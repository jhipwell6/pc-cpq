( async function () {
	const version = PC_CPQ_Config.scriptVersion;
	const [ { PC_CPQ_Helpers }, { createPartModel } ] = await Promise.all( [
		import( `./pc-cpq-helpers.js?v=${version}` ),
		import( `./pc-cpq-partmodel.js?v=${version}` )
	] );
	const PC_CPQ_PartModel_Base = createPartModel( PC_CPQ_Helpers );

	var PC_CPQ = ( function ( PC_CPQ, $ ) {

		/**
		 * Set a height for product lists
		 * @type {Object}
		 */
		PC_CPQ.Specs = {

			init() {
				this.bind();
			},

			bind() {
				if ( typeof gform != 'undefined' ) {
					gform.addFilter( 'gform_file_upload_markup', $.proxy( this.processFiles, this ) );
				}
			},

			processFiles( html, file, up, strings, imagesUrl, response ) {
				const formId = up?.settings?.multipart_params?.form_id;
				const fieldId = up?.settings?.multipart_params?.field_id;
				const tempFilename = response?.data?.temp_filename;

				if ( formId && fieldId ) {
					$( `#gform_multifile_upload_${formId}_${fieldId}` ).addClass( 'file-added' );
				}

				if ( ! this.isStepFile( file.name ) ) {
					this.saveFileData( file.name, { } );
					return html;
				}

				if ( ! formId || ! fieldId || ! tempFilename ) {
					this.saveFileData( file.name, { } );
					return html;
				}

				this.getModelData( {
					fileName: file.name,
					formId,
					fieldId,
					tempFilename
				} );

				return html;
			},

			isStepFile( fileName ) {
				return /\.(stp|step)$/i.test( fileName || '' );
			},

			getModelData: async function ( fileData ) {
				try {
					const formData = new FormData();
					formData.append( 'action', 'pc_cpq_process_step_upload' );
					formData.append( 'nonce', PC_CPQ_Config.stpProcessingNonce );
					formData.append( 'form_id', fileData.formId );
					formData.append( 'field_id', fileData.fieldId );
					formData.append( 'temp_filename', fileData.tempFilename );
					formData.append( 'uploaded_filename', fileData.fileName );

					const response = await fetch( PC_CPQ_Config.ajaxurl, {
						method: 'POST',
						body: formData
					} );
					const json = await response.json();

					if ( json?.success && json?.data?.measurement ) {
						this.saveFileData( fileData.fileName, json.data.measurement );
						return;
					}
				} catch ( error ) {
					// Preserve the existing UX by falling back to a file-only part.
				}

				this.saveFileData( fileData.fileName, { } );
			},

			saveFileData( fileName, data ) {
				data.fileName = fileName;
				$( document ).trigger( 'spc:part_added', data );
			}
		};

		PC_CPQ.Parts = {

			formFieldID: '#parts-model',
			dataFieldID: '#input_1_31',
			form: '',
			parts: [ ],
			copiedData: [ ],
			useSameProcesses: false,

			init() {
				this.bind();
			},

			bind() {
				$( document ).on( 'spc:part_added', $.proxy( this.onPartAdded, this ) );
				$( document ).on( 'propertychange input', this.formFieldID + ' input', this.debounce( $.proxy( this.updatePart, this ) ) );
				$( document ).on( 'change', this.formFieldID + ' select', this.debounce( $.proxy( this.updatePart, this ) ) );
				$( document ).on( 'click', this.formFieldID + ' [data-action="add"]', $.proxy( this.addPartItem, this ) );
				$( document ).on( 'click', this.formFieldID + ' [data-action="remove"]', $.proxy( this.removePartItem, this ) );
				$( document ).on( 'show.bs.collapse', '.collapse', $.proxy( this.togglePartShow, this ) );
				$( document ).on( 'hide.bs.collapse', '.collapse', $.proxy( this.togglePartShow, this ) );
				$( document ).on( 'gform_page_loaded', $.proxy( this.onPageLoaded, this ) );

				// conditional logic
				$( document ).on( 'change', '[data-parent="processes"][data-name$="metal"]', this.debounce( $.proxy( this.onProcessChange, this ) ) );

				// copy/paste feature
				$( document ).on( 'click', '[data-action="use-same-process"]', $.proxy( this.setUseSameProcesses, this ) );
				$( document ).on( 'change', '[data-action="use-same-process"]', $.proxy( this.maybeSyncProcesses, this ) );
				$( document ).on( 'click', this.formFieldID + ' [data-action="copy"]', $.proxy( this.onCopy, this ) );
				$( document ).on( 'click', this.formFieldID + ' [data-action="paste"]', $.proxy( this.onPaste, this ) );

				// change unit
				$( document ).on( 'change', 'select[data-name$="unit"]', $.proxy( this.updateUnitLabel, this ) );
			},

			refresh( updateView = true ) {
				// maybe render the view
				if ( updateView ) {
					this.form = this.defaultForm();
					this.parts.forEach( ( part ) => {
						this.form += part.renderFields();
					} );
					this.render();
				}

				// store the data
				this.storeData();
			},

			render() {
				$( this.formFieldID ).html( this.form );
			},

			onPartAdded( e, part ) {
				let newPart = $.extend( true, { }, PC_CPQ_PartModel_Base, part );
				newPart.init();
				this.addPart( newPart );
			},

			addPart( part ) {
				this.parts.push( part );
				if ( this.parts.length > 1 ) {
					this.resetCopyPasteButtons();
				}

				// refresh the app
				this.refresh();
			},

			removePart() {
				// remove part by name

				// refresh the app
				this.refresh();
			},

			updatePart( e ) {
				const $field = $( e.target ),
						ID = $field.attr( 'data-part' ),
						part = this.getPartByID( ID );

				if ( part ) {
					part.setProp( $field.attr( 'data-name' ), $field.val() );
				}

				this.maybeSyncProcesses( part );

				// refresh the app (not the view)
				this.refresh( false );
			},

			addPartItem( e ) {
				const $button = $( e.target ),
						ID = $button.attr( 'data-part' ),
						field = $button.attr( 'data-field' ),
						part = this.getPartByID( ID );

				part.addItem( field );

				// refresh the app
				this.refresh();
			},

			removePartItem( e ) {
				const $button = $( e.target ),
						ID = $button.attr( 'data-part' ),
						field = $button.attr( 'data-field' ),
						index = $button.attr( 'data-index' ),
						part = this.getPartByID( ID );

				part.removeItem( field, index );

				// refresh the app
				this.refresh();
			},

			togglePartShow( e ) {
				const $collapse = $( e.target ),
						ID = $collapse.attr( 'data-part' ),
						part = this.getPartByID( ID );

				part.toggleShow();

				// refresh the app (not the view)
				this.refresh( false );
			},

			onProcessChange( e ) {
				const $select = $( e.target ),
						ID = $select.attr( 'data-part' ),
						field = $select.attr( 'data-parent' ),
						index = $select.attr( 'data-index' ),
						value = $select.val(),
						part = this.getPartByID( ID );

				part.toggleInputs( field, value, index );

				// refresh the app
				this.refresh();
			},

			updateUnitLabel( e ) {
				const select = $( e.target );
				const unit = select.val();

				if ( unit === 'Standard' ) {
					select.closest( '.row' ).find( 'input[data-name$="minThickness"]' ).prev().text( 'Min Thickness (μin) *' );
					select.closest( '.row' ).find( 'input[data-name$="maxThickness"]' ).prev().text( 'Max Thickness (μin) *' );
				} else {
					select.closest( '.row' ).find( 'input[data-name$="minThickness"]' ).prev().text( 'Min Thickness (μm) *' );
					select.closest( '.row' ).find( 'input[data-name$="maxThickness"]' ).prev().text( 'Max Thickness (μm) *' );
				}
			},

			resetCopyPasteButtons() {
				// show the copy button for all parts
				// hide the paste button for all parts
				this.parts.forEach( ( part ) => {
					part.showCopy = true;
					part.showPaste = false;
				} );

				// refresh the app
				this.refresh();
			},

			togglePartsCopy( p = null ) {
				// toggle the copy button for all parts
				this.parts.forEach( ( part ) => {
					if ( p != null && p.ID == part.ID ) {
						part.showCopyMsg = true;
						this.resetMsgsOnDelay();
					}
					part.toggleCopyButton();
				} );

				// refresh the app
				this.refresh();
			},

			togglePartsPaste( p = null ) {
				// toggle the paste button for all parts
				this.parts.forEach( ( part ) => {
					if ( p != null && p.ID == part.ID ) {
						part.showPasteMsg = true;
						this.resetMsgsOnDelay();
					}
					part.togglePasteButton();
				} );

				// refresh the app
				this.refresh();
			},

			onCopy( e ) {
				const $button = $( e.target ),
						ID = $button.attr( 'data-part' ),
						part = this.getPartByID( ID );

				// copy the data
				this.copyProcesses( part );

				// toggle the buttons
				this.togglePartsCopy( part );
				this.togglePartsPaste();
			},

			onPaste( e ) {
				const $button = $( e.target ),
						ID = $button.attr( 'data-part' ),
						part = this.getPartByID( ID );

				// paste the data
				this.pasteProcesses( part );

				// toggle the buttons
				this.togglePartsCopy();
				this.togglePartsPaste( part );
			},

			setUseSameProcesses( e ) {
				this.useSameProcesses = ! this.useSameProcesses;
			},

			maybeSyncProcesses( part ) {
				if ( ! part.hasOwnProperty( 'ID' ) ) {
					part = this.parts.length ? this.parts[0] : false;
				}
				if ( this.useSameProcesses && part ) {
					this.copyProcesses( part );
					this.parts.forEach( ( p ) => {
						this.pasteProcesses( p, false );
					} );

					// refresh the app
					this.refresh();
				}
			},

			copyProcesses( part ) {
				this.copiedData = PC_CPQ_Helpers.clone( part.processes );
			},

			pasteProcesses( part, clear = true ) {
				part.processes = this.copiedData;
				if ( clear ) {
					this.copiedData = [ ];
			}
			},

			resetMsgsOnDelay() {
				setTimeout( () => {
					this.parts.forEach( ( part ) => {
						part.resetMessages();
					} );

					this.refresh();
				}, 1000 );
			},

			onPageLoaded( e, formId, currentPage ) {
				if ( currentPage == '2' ) {
					this.refresh();
				}

				if ( currentPage == '3' ) {
					this.validatePartData();
				}
			},

			validatePartData() {
				const invalid = this.parts.some( ( part ) => {
					return ! part.hasRequiredData();
				} );

				if ( invalid ) {
					$( "#gform_target_page_number_1" ).val( "2" );
					$( "#gform_1" ).trigger( "submit", [ true ] );
					alert( 'Please configure all parts.  All fields are required.' );
				}
			},

			getPartByID( ID ) {
				let parts = this.parts.filter( ( part ) => part.ID == ID );
				return parts.length ? parts[0] : false;
			},

			defaultForm() {
				const checked = this.useSameProcesses ? ' checked' : '';
				return '<div class="fieldset-key">1 in = 1000000 microinches</div><div class="form-check mb-2">\
					<input type="checkbox" class="form-check-input" data-action="use-same-process" value="" id="use-same-process"' + checked + '>\
					<label class="form-check-label" for="use-same-process"> Use the same process for all parts</label>\
				</div>';
			},

			prepDataForStoring() {
				let parts = PC_CPQ_Helpers.clone( this.parts );
				if ( parts.length ) {
					parts.forEach( ( part, i, minimizedParts ) => {
						minimizedParts[i] = PC_CPQ_Helpers.minimize( part, part.requiredProperties );
					} );
				}

				return parts;
			},

			storeData() {
				const data = this.prepDataForStoring( this.parts );
				$( this.dataFieldID ).val( JSON.stringify( data ) );
			},

			debounce( func, timeout = 300 ) {
				let timer;
				return ( ...args ) => {
					clearTimeout( timer );
					timer = setTimeout( () => {
						func.apply( this, args );
					}, timeout );
				};
			}
		};

		PC_CPQ.Phone = {

			selector: [
				'.gform_wrapper .ginput_container_phone input',
				'.gform_wrapper .gfield--type-phone input',
				'.gform_wrapper input[type="tel"]',
				'.gform_wrapper input[id^="input_"][autocomplete="tel"]'
			].join( ', ' ),
			instances: new WeakMap(),
			observer: null,
			debug: true,
			libraryLoadPromise: null,

			init() {
				this.log( 'init:start', {
					intlTelInputAvailable: typeof window.intlTelInput === 'function',
					selector: this.selector
				} );
				this.bind();
				this.initInputs();
				this.observeDom();
			},

			bind() {
				this.log( 'bind:events' );
				$( document ).on( 'gform_post_render', $.proxy( this.initInputs, this ) );
				$( document ).on( 'gform_page_loaded', $.proxy( this.initInputs, this ) );
				$( document ).on( 'submit', '.gform_wrapper form', $.proxy( this.prepareSubmit, this ) );
			},

			observeDom() {
				if ( this.observer || typeof MutationObserver === 'undefined' ) {
					this.log( 'observer:skip', {
						hasObserver: !! this.observer,
						mutationObserverAvailable: typeof MutationObserver !== 'undefined'
					} );
					return;
				}

				this.observer = new MutationObserver( () => {
					this.log( 'observer:mutation' );
					this.initInputs();
				} );

				document.querySelectorAll( '.gform_wrapper' ).forEach( ( wrapper ) => {
					this.observer.observe( wrapper, { childList: true, subtree: true } );
					this.log( 'observer:attached', {
						wrapperClassName: wrapper.className
					} );
				} );
			},

			initInputs() {
				this.log( 'initInputs:start', {
					intlTelInputAvailable: typeof window.intlTelInput === 'function'
				} );

				if ( typeof window.intlTelInput !== 'function' ) {
					this.log( 'initInputs:missing-library', {
						scriptUrl: PC_CPQ_Config?.intlTelInput?.scriptUrl || ''
					} );
					this.ensureLibrary().then( () => {
						this.log( 'initInputs:library-ready-after-load', {
							intlTelInputAvailable: typeof window.intlTelInput === 'function'
						} );
						this.initInputs();
					} ).catch( ( error ) => {
						this.log( 'initInputs:library-load-failed', {
							message: error?.message || 'Unknown error'
						} );
					} );
					return;
				}

				const inputs = Array.from( document.querySelectorAll( this.selector ) );
				this.log( 'initInputs:matched-inputs', {
					count: inputs.length,
					inputs: inputs.map( ( input ) => ( {
						id: input.id || '',
						name: input.name || '',
						type: input.type || '',
						autocomplete: input.autocomplete || '',
						className: input.className || ''
					} ) )
				} );

				inputs.forEach( ( input ) => {
					const shouldAttach = this.shouldAttachToInput( input );
					this.log( 'initInputs:candidate', {
						id: input.id || '',
						name: input.name || '',
						type: input.type || '',
						autocomplete: input.autocomplete || '',
						className: input.className || '',
						shouldAttach
					} );

					if ( shouldAttach ) {
						this.initInput( input );
					}
				} );
			},

			shouldAttachToInput( input ) {
				if ( ! input || ! input.name || input.type === 'hidden' ) {
					this.log( 'shouldAttach:false-basic', {
						hasInput: !! input,
						name: input?.name || '',
						type: input?.type || ''
					} );
					return false;
				}

				const wrapper = input.closest( '.gfield, .ginput_container, li' );
				const wrapperClassName = wrapper?.className || '';
				const inputId = input.id || '';
				const inputName = input.name || '';
				const autocomplete = input.autocomplete || '';

				const result = /phone/i.test( wrapperClassName )
					|| /phone/i.test( inputId )
					|| /phone/i.test( inputName )
					|| autocomplete === 'tel';

				this.log( 'shouldAttach:result', {
					id: inputId,
					name: inputName,
					autocomplete,
					wrapperClassName,
					result
				} );

				return result;
			},

			initInput( input ) {
				if ( ! input || this.instances.has( input ) || ! input.name ) {
					this.log( 'initInput:skip', {
						hasInput: !! input,
						alreadyInitialized: input ? this.instances.has( input ) : false,
						name: input?.name || ''
					} );
					return;
				}

				this.log( 'initInput:attach', {
					id: input.id || '',
					name: input.name || '',
					type: input.type || '',
					value: input.value || ''
				} );

				const iti = window.intlTelInput( input, {
					initialCountry: 'us',
					countrySearch: true,
					formatAsYouType: true,
					numberDisplayFormat: 'NATIONAL',
					placeholderNumberPolicy: 'AGGRESSIVE',
					placeholderNumberType: 'MOBILE',
					separateDialCode: true,
					strictMode: true
				} );

				this.instances.set( input, iti );
				input.dataset.pcCpqIntlTelReady = '1';
				this.log( 'initInput:attached', {
					id: input.id || '',
					name: input.name || '',
					hasDatasetFlag: input.dataset.pcCpqIntlTelReady === '1'
				} );

				if ( input.value ) {
					iti.setNumber( input.value );
					this.log( 'initInput:setNumber', {
						id: input.id || '',
						name: input.name || '',
						value: input.value || ''
					} );
				}

				input.addEventListener( 'countrychange', () => {
					this.log( 'countrychange', {
						id: input.id || '',
						name: input.name || '',
						value: input.value || '',
						placeholder: input.placeholder || ''
					} );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );

				window.setTimeout( () => {
					this.log( 'initInput:post-init-state', {
						id: input.id || '',
						name: input.name || '',
						value: input.value || '',
						placeholder: input.placeholder || '',
						selectedCountry: typeof iti.getSelectedCountry === 'function' ? iti.getSelectedCountry() : null
					} );
				}, 0 );
			},

			prepareSubmit( e ) {
				this.log( 'prepareSubmit:start', {
					formId: e.currentTarget?.id || ''
				} );
				e.currentTarget.querySelectorAll( this.selector ).forEach( ( input ) => {
					const iti = this.instances.get( input );

					if ( ! iti || ! input.value ) {
						this.log( 'prepareSubmit:skip', {
							id: input.id || '',
							name: input.name || '',
							hasInstance: !! iti,
							value: input.value || ''
						} );
						return;
					}

					try {
						const formatted = iti.getNumber();
						if ( formatted ) {
							input.value = formatted;
						}
						this.log( 'prepareSubmit:formatted', {
							id: input.id || '',
							name: input.name || '',
							formatted: formatted || ''
						} );
					} catch ( error ) {
						this.log( 'prepareSubmit:error', {
							id: input.id || '',
							name: input.name || '',
							message: error?.message || 'Unknown error'
						} );
						// Leave the user's current input intact if utils are not ready yet.
					}
				} );
			},

			ensureLibrary() {
				if ( typeof window.intlTelInput === 'function' ) {
					return Promise.resolve( window.intlTelInput );
				}

				if ( this.libraryLoadPromise ) {
					this.log( 'ensureLibrary:reuse-promise' );
					return this.libraryLoadPromise;
				}

				const scriptUrl = PC_CPQ_Config?.intlTelInput?.scriptUrl;
				if ( ! scriptUrl ) {
					return Promise.reject( new Error( 'Missing intl-tel-input script URL.' ) );
				}

				this.log( 'ensureLibrary:start', { scriptUrl } );

				this.libraryLoadPromise = new Promise( ( resolve, reject ) => {
					const existingScript = document.querySelector( `script[data-pc-cpq-intl-tel-input="1"]` );
					if ( existingScript ) {
						this.log( 'ensureLibrary:existing-script-found' );
						existingScript.addEventListener( 'load', () => {
							if ( typeof window.intlTelInput === 'function' ) {
								resolve( window.intlTelInput );
								return;
							}

							reject( new Error( 'intl-tel-input script loaded but window.intlTelInput is unavailable.' ) );
						}, { once: true } );
						existingScript.addEventListener( 'error', () => {
							reject( new Error( 'Existing intl-tel-input script failed to load.' ) );
						}, { once: true } );
						return;
					}

					const script = document.createElement( 'script' );
					script.src = scriptUrl;
					script.async = true;
					script.dataset.pcCpqIntlTelInput = '1';
					script.addEventListener( 'load', () => {
						this.log( 'ensureLibrary:loaded', {
							intlTelInputAvailable: typeof window.intlTelInput === 'function'
						} );

						if ( typeof window.intlTelInput === 'function' ) {
							resolve( window.intlTelInput );
							return;
						}

						reject( new Error( 'intl-tel-input loaded but did not expose window.intlTelInput.' ) );
					}, { once: true } );
					script.addEventListener( 'error', () => {
						reject( new Error( 'Failed to load intl-tel-input script.' ) );
					}, { once: true } );
					document.head.appendChild( script );
				} ).catch( ( error ) => {
					this.libraryLoadPromise = null;
					throw error;
				} );

				return this.libraryLoadPromise;
			},

			log( event, data = {} ) {
				if ( ! this.debug || typeof console === 'undefined' ) {
					return;
				}

				console.log( '[PC_CPQ Phone]', event, data );
			}
		};

		const onDocReady = [
			() => {
				PC_CPQ.Specs.init();
			},
			() => {
				PC_CPQ.Parts.init();
			},
			() => {
				PC_CPQ.Phone.init();
			}
		];

		// Iterate through callbacks and move each callback separately to event queue
		$( function () {
			onDocReady.forEach( callback => {
				setTimeout( callback, 0 );
			} );
		} );

		return PC_CPQ;
	}( PC_CPQ || { }, jQuery ) );
} )();
