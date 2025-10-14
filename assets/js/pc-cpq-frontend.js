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

			apiUrl: 'https://www.sharrettsplating.com/cgi-bin/stpmeasure.cgi',

			init() {
				this.bind();
			},

			bind() {
				if ( typeof gform != 'undefined' ) {
					gform.addFilter( 'gform_file_upload_status_markup', $.proxy( this.processFiles, this ) );
				}
			},

			processFiles( html, file, up, strings, imagesUrl, response ) {
				this.getModelData( file.getNative() );
				$( '#gform_multifile_upload_1_11' ).addClass( 'file-added' );

				return html;
			},

			getModelData: async function ( file ) {
				const formData = new FormData();
				formData.append( 'file', file );
				const response = await fetch( this.apiUrl, {
					method: 'POST',
					body: formData
				} );
				let text = await response.text();
				if ( text.includes( '[{ "isSuccess"' ) ) { // fix json bug
					text = text.replace( /\[\{/, '[{}],' );
				}
				const json = JSON.parse( text );

				if ( json.isSuccess ) {
					for ( const data of json.filesInfo ) {
						this.saveFileData( file.name, data );
					}
				} else {
					$( document ).trigger( 'spc:part_added', { fileName: file.name } );
				}
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

		const onDocReady = [
			() => {
				PC_CPQ.Specs.init();
			},
			() => {
				PC_CPQ.Parts.init();
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