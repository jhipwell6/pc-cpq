import {PC_CPQ_Helpers} from './pc-cpq-helpers.js';
import {PC_CPQ_TourConfig} from './pc-cpq-tourconfig.js';
import {convert} from 'https://cdn.jsdelivr.net/npm/convert@4';

var PC_CPQ_Manage = ( function ( PC_CPQ_Manage, $, Pace ) {

	PC_CPQ_Manage.Form = {
		init() {
			this.bind();
		},

		bind() {
			$( document ).on( 'change', 'select', $.proxy( this.onInputChange, this ) );
			$( document ).on( 'input', 'input, textarea', $.proxy( this.onInputChange, this ) );
		},

		save( action, postVar, rawData, callback = null ) {
			const data = new FormData();
			data.append( 'action', action );
			data.append( postVar, rawData );

			Pace.track( ( arg ) => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					if ( response.status !== 200 ) {
						PC_CPQ_Manage.Form.showSaveError();
					} else {
						PC_CPQ_Manage.Form.hideSaveReminder();
						PC_CPQ_Manage.Form.showSaveSuccess();
					}
					return response.json()
				} ).then( ( result ) => {
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		fetch( rawData, callback = null ) {
			const data = new FormData();
			Object.entries( rawData ).forEach( ( [ key, value ] ) => {
				data.append( key, value );
			} );

			Pace.track( () => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					return response.json();
				} ).then( ( result ) => {
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		quote( rawData, callback = null ) {
			const data = new FormData();
			Object.entries( rawData ).forEach( ( [ key, value ] ) => {
				data.append( key, value );
			} );

			this.hideSendQuoteSuccess();
			this.hideSendQuoteError();

			Pace.track( () => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					if ( response.status !== 200 ) {
						this.showSendQuoteError();
					} else {
						this.showSendQuoteSuccess();
					}
					return response.json()
				} ).then( ( result ) => {
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		message( rawData, callback = null ) {
			const data = new FormData();
			Object.entries( rawData ).forEach( ( [ key, value ] ) => {
				data.append( key, value );
			} );

			this.hideSendMessageSuccess();
			this.hideSendMessageError();

			Pace.track( () => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					if ( response.status !== 200 ) {
						this.showSendMessageError();
					} else {
						this.showSendMessageSuccess();
					}
					return response.json()
				} ).then( ( result ) => {
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		import( rawData, type, callback = null ) {
			const data = new FormData();
			Object.entries( rawData ).forEach( ( [ key, value ] ) => {
				data.append( key, value );
			} );

			this.hideImportSuccess( type );
			this.hideImportError( type );

			Pace.track( () => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					if ( response.status !== 200 ) {
						this.showImportError( type );
					} else {
						this.showImportSuccess( type );
					}
					return response.json()
				} ).then( ( result ) => {
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		export( rawData, type, callback = null ) {
			const data = new FormData();
			Object.entries( rawData ).forEach( ( [ key, value ] ) => {
				data.append( key, value );
			} );

			Pace.track( () => {
				fetch( PC_CPQ_ManageConfig.ajaxurl, {
					method: 'POST',
					body: data
				} ).then( ( response ) => {
					return response.blob()
				} ).then( ( result ) => {
					this.saveFile( result, type );
					if ( typeof callback == 'function' ) {
						callback( result );
					}
				} );
			} );
		},

		saveFile( blob, fileName ) {
			const a = document.createElement( "a" );
			document.body.appendChild( a );
			a.style = "display: none";
			const url = window.URL.createObjectURL( blob );
			a.href = url;
			a.download = fileName;
			a.click();
			window.URL.revokeObjectURL( url );
			a.remove();
		},

		onInputChange( e, data = null ) {
			if ( $( e.target ).hasClass( 'js-non-reactive' ) || $( e.target ).closest( '#prepare-quote-modal' ).length ) {
				return false;
			}

			if ( data && data.spc_manual_change ) {
				return false;
			}

			$( document ).trigger( 'spc-change' );
			this.hideSaveSuccess();
			this.hideSaveError();
			this.showSaveReminder();
		},

		showSaveReminder() {
			$( '.js-save-reminder' ).removeClass( 'd-none' );
		},

		hideSaveReminder() {
			$( '.js-save-reminder' ).addClass( 'd-none' );
		},

		showSaveSuccess() {
			$( '.js-save-success' ).removeClass( 'd-none' );
		},

		hideSaveSuccess() {
			$( '.js-save-success' ).addClass( 'd-none' );
		},

		showSaveError() {
			$( '.js-save-error' ).removeClass( 'd-none' );
		},

		hideSaveError() {
			$( '.js-save-error' ).addClass( 'd-none' );
		},

		showImportSuccess( type ) {
			$( '.modal[data-type="' + type + '"] .js-import-success' ).removeClass( 'd-none' );
		},

		hideImportSuccess( type ) {
			$( '.modal[data-type="' + type + '"] .js-import-success' ).addClass( 'd-none' );
		},

		showImportError( type ) {
			$( '.modal[data-type="' + type + '"] .js-import-error' ).removeClass( 'd-none' );
		},

		hideImportError( type ) {
			$( '.modal[data-type="' + type + '"] .js-import-error' ).addClass( 'd-none' );
		},

		showSendQuoteSuccess() {
			$( '.js-send-quote-success' ).removeClass( 'd-none' );
		},

		hideSendQuoteSuccess() {
			$( '.js-send-quote-success' ).addClass( 'd-none' );
		},

		showSendQuoteError() {
			$( '.js-send-quote-error' ).removeClass( 'd-none' );
		},

		hideSendQuoteError() {
			$( '.js-send-quote-error' ).addClass( 'd-none' );
		},

		showSendMessageSuccess() {
			$( '.js-send-message-success' ).removeClass( 'd-none' );
		},

		hideSendMessageSuccess() {
			$( '.js-send-message-success' ).addClass( 'd-none' );
		},

		showSendMessageError() {
			$( '.js-send-message-error' ).removeClass( 'd-none' );
		},

		hideSendMessageError() {
			$( '.js-send-message-error' ).addClass( 'd-none' );
		},

		showCopyProcessSuccess() {
			$( '.js-copy-process-success' ).removeClass( 'd-none' );
		},

		hideCopyProcessSuccess() {
			$( '.js-copy-process-success' ).addClass( 'd-none' );
		},

		toggleCopyProcessSuccess() {
			this.showCopyProcessSuccess();
			setTimeout( () => {
				this.hideCopyProcessSuccess();
			}, 2000 );
		},

		showPasteProcessSuccess() {
			$( '.js-paste-process-success' ).removeClass( 'd-none' );
		},

		hidePasteProcessSuccess() {
			$( '.js-paste-process-success' ).addClass( 'd-none' );
		},

		togglePasteProcessSuccess() {
			this.showPasteProcessSuccess();
			setTimeout( () => {
				this.hidePasteProcessSuccess();
			}, 2000 );
		},

		showCopyQuantitiesSuccess() {
			$( '.js-copy-quantities-success' ).removeClass( 'd-none' );
		},

		hideCopyQuantitiesSuccess() {
			$( '.js-copy-quantities-success' ).addClass( 'd-none' );
		},

		toggleCopyQuantitiesSuccess() {
			this.showCopyQuantitiesSuccess();
			setTimeout( () => {
				this.hideCopyQuantitiesSuccess();
			}, 2000 );
		},

		showPasteQuantitiesSuccess() {
			$( '.js-paste-quantities-success' ).removeClass( 'd-none' );
		},

		hidePasteQuantitiesSuccess() {
			$( '.js-paste-quantities-success' ).addClass( 'd-none' );
		},

		togglePasteQuantitiesSuccess() {
			this.showPasteQuantitiesSuccess();
			setTimeout( () => {
				this.hidePasteQuantitiesSuccess();
			}, 2000 );
		},

		showCopyPricingSuccess() {
			$( '.js-copy-pricing-success' ).removeClass( 'd-none' );
		},

		hideCopyPricingSuccess() {
			$( '.js-copy-pricing-success' ).addClass( 'd-none' );
		},

		toggleCopyPricingSuccess() {
			this.showCopyPricingSuccess();
			setTimeout( () => {
				this.hideCopyPricingSuccess();
			}, 2000 );
		},

		showPastePricingSuccess() {
			$( '.js-paste-pricing-success' ).removeClass( 'd-none' );
		},

		hidePastePricingSuccess() {
			$( '.js-paste-pricing-success' ).addClass( 'd-none' );
		},

		togglePastePricingSuccess() {
			this.showPastePricingSuccess();
			setTimeout( () => {
				this.hidePastePricingSuccess();
			}, 2000 );
		}
	};

	PC_CPQ_Manage.Common = {

		unitSystem: 'imperial',

		init() {
			this.bind();
			this.initFileUploader();
			this.initSelect2();
			this.initDatePicker();
			this.initTooltips();
			this.convertUnits();
		},

		bind() {
			$( document ).on( 'hide.bs.modal', '.modal', $.proxy( this.renderRow, this ) );
			$( document ).on( 'change', '[name="unit_system"]', $.proxy( this.toggleUnits, this ) );
			$( document ).on( 'keyup', '[data-convertable-input="1"]', $.proxy( this.updateUnitAttributes, this ) );
		},

		initTooltips() {
			$( '[data-toggle="tooltip"],.js-tooltip a,.js-self-tooltip' ).tooltip();
		},

		initFileUploader() {
			bsCustomFileInput.init();
		},

		initSelect2() {
			$( 'select.custom-select' ).select2( {
				theme: 'bootstrap4',
				placeholder: 'Select',
				allowClear: true
			} );

			$( 'select[multiple]' ).select2( {
				theme: 'bootstrap4'
			} );
		},

		initDatePicker() {
			$( '#edit-follow-up-date' ).datetimepicker( {
				format: 'LT'
			} );
		},

		initWpEditor( id ) {
			if ( typeof tinymce != 'undefined' ) {
				tinymce.execCommand( 'mceRemoveEditor', false, id );
				tinymce.execCommand( 'mceAddEditor', false, id );
			}
		},

		renderRow( e ) {
			const $modal = $( e.target ),
					type = $modal.data( 'type' ),
					index = $modal.data( 'index' ),
					data = this._getRowData( $modal, index, type ),
					$row = $( 'tr[data-type="' + type + '"][data-index="' + index + '"]' );

			Object.entries( data ).forEach( ( [ key, value ] ) => {
				$row.find( '[data-model="' + key + '"]' ).text( value );
			} );
		},

		_getRowData( row, index, type ) {
			let data = { };
			switch ( type ) {
				case 'part':
					data = {
						fileName: row.find( 'input[name="raw_parts/' + index + '/file_name"]' ).val(),
						drawingNumber: row.find( 'input[name="raw_parts/' + index + '/drawing_number"]' ).val(),
						revisionNumber: row.find( 'input[name="raw_parts/' + index + '/revision_number"]' ).val(),
						partNumber: row.find( 'input[name="raw_parts/' + index + '/part_number"]' ).val()
					};
					break;
				case 'contact':
					data = {
						name: row.find( 'input[name="raw_contacts/' + index + '/name"]' ).val(),
						phone: row.find( 'input[name="raw_contacts/' + index + '/phone"]' ).val(),
						email: row.find( 'input[name="raw_contacts/' + index + '/email"]' ).val()
					};
					break;
				case 'shipping':
					data = {
						address: row.find( 'input[name="raw_shipping/' + index + '/shipping_street_address"]' ).val(),
						state: row.find( 'select[name="raw_shipping/' + index + '/shipping_state"]' ).val()
					};
					break;
				case 'email_template':
					data = {
						name: row.find( 'input[name="raw_email_templates/' + index + '/name"]' ).val()
					};
					break;
				case 'metal':
					data = {
						name: row.find( 'input[name="raw_metals/' + index + '/name"]' ).val(),
						density: row.find( 'input[name="raw_metals/' + index + '/density"]' ).val(),
						prepCycle: row.find( 'input[name="raw_metals/' + index + '/prep_cycle"]' ).val()
					};
					break;
				case 'plating_metal':
					data = {
						name: row.find( 'input[name="raw_plating_metals/' + index + '/name"]' ).val(),
						density: row.find( 'input[name="raw_plating_metals/' + index + '/density"]' ).val(),
						cost: row.find( 'input[name="raw_plating_metals/' + index + '/cost"]' ).val(),
						depositRate: row.find( 'input[name="raw_plating_metals/' + index + '/deposit_rate"]' ).val(),
						unitType: row.find( 'select[name="raw_plating_metals/' + index + '/unit_type"]' ).val(),
						unitVisible: row.find( 'input[name="raw_plating_metals/' + index + '/unit_visible"]' ).is( ':checked' ) ? 'Yes' : '-',
						minLotCharge: row.find( 'input[name="raw_plating_metals/' + index + '/min_lot_charge"]' ).val(),
						preciousMetal: row.find( 'input[name="raw_plating_metals/' + index + '/precious_metal"]' ).is( ':checked' ) ? 'Yes' : '-',
						hide: row.find( 'input[name="raw_plating_metals/' + index + '/hide"]' ).is( ':checked' ) ? 'Yes' : '-'
					};
					break;
				case 'line':
					data = {
						name: row.find( 'input[name="raw_lines/' + index + '/name"]' ).val(),
						plateCells: row.find( 'input[name="raw_lines/' + index + '/plate_cells"]' ).val(),
						maxPullsPerHour: row.find( 'input[name="raw_lines/' + index + '/max_pulls_per_hour"]' ).val(),
						barrelSizeLimit: row.find( 'input[name="raw_lines/' + index + '/barrel_size_limit"]' ).val(),
						rackSizeLimit: row.find( 'input[name="raw_lines/' + index + '/rack_size_limit"]' ).val(),
						rackFactor: row.find( 'input[name="raw_lines/' + index + '/rack_factor"]' ).val(),
						weightLimit: row.find( 'input[name="raw_lines/' + index + '/weight_limit"]' ).val(),
						rackLdMaxIn2: row.find( 'input[name="raw_lines/' + index + '/rack_ld_max_in2"]' ).val()
					};
					break;
				case 'barrel':
					data = {
						name: row.find( 'input[name="raw_barrels/' + index + '/name"]' ).val(),
						sizeLimit: row.find( 'input[name="raw_barrels/' + index + '/size_limit"]' ).val(),
						ft2Load: row.find( 'input[name="raw_barrels/' + index + '/ft2_load"]' ).val(),
						weightLimit: row.find( 'input[name="raw_barrels/' + index + '/weight_limit"]' ).val()
					};
					break;
				case 'rack':
					data = {
						name: row.find( 'input[name="raw_racks/' + index + '/name"]' ).val(),
						sizeLimit: row.find( 'input[name="raw_racks/' + index + '/size_limit"]' ).val(),
						weightLimit: row.find( 'input[name="raw_racks/' + index + '/weight_limit"]' ).val(),
						pieceCount: row.find( 'input[name="raw_racks/' + index + '/piece_count"]' ).val()
					};
					break;
				case 'operation':
					var type = row.find( 'select[name="raw_operations/' + index + '/type"]' ).val();
					var baseMetal = row.find( 'select[name="raw_operations/' + index + '/base_metal"]' ).val();
					var material = row.find( 'select[name="raw_operations/' + index + '/material"]' ).val();
					data = {
						operation: row.find( 'input[name="raw_operations/' + index + '/operation"]' ).val(),
						description: row.find( 'input[name="raw_operations/' + index + '/description"]' ).val(),
						setupTime: row.find( 'input[name="raw_operations/' + index + '/setup_time"]' ).val(),
						setupUnit: row.find( 'input[name="raw_operations/' + index + '/setup_unit"]' ).val(),
						cycleTime: row.find( 'input[name="raw_operations/' + index + '/cycle_time"]' ).val(),
						cycleUnit: row.find( 'input[name="raw_operations/' + index + '/cycle_unit"]' ).val(),
						efficiency: row.find( 'input[name="raw_operations/' + index + '/efficiency"]' ).val(),
						type: type,
						metalMaterial: type == 'Prep' ? baseMetal : material
					};
					break;
			}
			return data;
		},

		convertUnits() {
			$( '[data-convertable-text]' ).each( ( i, el ) => {
				let fromUnit = $( el ).attr( 'data-unit-imperial' );
				let toUnit = $( el ).attr( 'data-unit-metric' );
				let fromValue = parseFloat( $( el ).attr( 'data-value-imperial' ) );
				let toValue = convert( fromValue, fromUnit ).to( toUnit );
				$( el ).attr( 'data-value-metric', toValue );
			} );

			this.convertableInputs().forEach( ( input ) => {
				$( input.selector ).each( ( i, el ) => {
					this.setUnitValues( $( el ), input.imperialUnit, input.metricUnit );
				} );
			} );
		},

		updateUnitAttributes( e ) {
			const $el = $( e.target );
			const imperialUnit = $el.attr( 'data-unit-imperial' );
			const metricUnit = $el.attr( 'data-unit-metric' );
			this.setUnitValues( $el, imperialUnit, metricUnit );
		},

		setUnitValues( $el, imperialUnit, metricUnit ) {
			$el.attr( 'data-convertable-input', 1 )
					.attr( 'data-unit-imperial', imperialUnit )
					.attr( 'data-unit-metric', metricUnit );

			let imperialValue = parseFloat( $el.val() );
			if ( imperialValue ) {
				let metricValue = convert( imperialValue, imperialUnit ).to( metricUnit );
				$el.attr( 'data-value-imperial', imperialValue )
						.attr( 'data-value-metric', metricValue );
			} else {
				$el.attr( 'data-value-imperial', '' )
						.attr( 'data-value-metric', '' );
			}
		},

		toggleUnits() {
			this.toggleUnitSystem();
			this.toggleUnitsDOM();
		},

		toggleUnitsDOM() {
			$( '[data-convertable-text]' ).each( ( i, el ) => {
				let value = this.unitSystem == 'imperial' ? $( el ).attr( 'data-value-imperial' ) : $( el ).attr( 'data-value-metric' );
				let unit = this.unitSystem == 'imperial' ? $( el ).attr( 'data-unit-imperial' ) : $( el ).attr( 'data-unit-metric' );
				$( el ).text( parseFloat( value ).toFixed( 4 ) + ' ' + unit );
			} );

			this.convertableInputs().forEach( ( input ) => {
				$( input.selector ).each( ( i, el ) => {
					let value = this.unitSystem == 'imperial' ? $( el ).attr( 'data-value-imperial' ) : $( el ).attr( 'data-value-metric' );
					let unit = this.unitSystem == 'imperial' ? $( el ).attr( 'data-unit-imperial' ) : $( el ).attr( 'data-unit-metric' );
					$( el ).val( value ).next( '.input-group-append' ).find( '.input-group-text' ).text( unit );
				} );
			} );
		},

		toggleUnitSystem() {
			if ( this.unitSystem == 'imperial' ) {
				this.unitSystem = 'metric';
			} else {
				this.unitSystem = 'imperial';
			}
		},

		forceUnitSystem( system ) {
			$( '[name="unit_system"][value="' + system + '"]' ).click();
			this.toggleUnitsDOM();
		},

		convertableInputs() {
			return [
				{
					selector: '[name$="/area_computed"]',
					imperialUnit: 'ft2',
					metricUnit: 'mm2'
				},
				{
					selector: '[name$="/area_override"]',
					imperialUnit: 'ft2',
					metricUnit: 'mm2'
				},
				{
					selector: '[name$="/volume_computed"]',
					imperialUnit: 'in3',
					metricUnit: 'mm3'
				},
				{
					selector: '[name$="/volume_override"]',
					imperialUnit: 'in3',
					metricUnit: 'mm3'
				},
				{
					selector: '[name$="/d_x_computed"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				},
				{
					selector: '[name$="/d_x_override"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				},
				{
					selector: '[name$="/d_y_computed"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				},
				{
					selector: '[name$="/d_y_override"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				},
				{
					selector: '[name$="/d_z_computed"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				},
				{
					selector: '[name$="/d_z_override"]',
					imperialUnit: 'in',
					metricUnit: 'mm'
				}
			];
		},
	};

	PC_CPQ_Manage.Lead = {

		leadID: $( 'input[name="lead_id"]' ).val(),

		canSendQuote: true,

		process: null,
		quantities: null,
		pasteTarget: null,

		init() {
			this.bind();
			this.checkCanSendQuote();
			this.updateAllOperationData();
			this.initPlatingToolInputs();
			this.customerLookup();
//			this.renderStepFiles();
		},

		bind() {
			$( document ).on( 'spc-change', $.proxy( this.onInputChange, this ) )
			$( document ).on( 'submit', '.js-send-quote-form', $.proxy( this.sendQuote, this ) );
			$( document ).on( 'submit', '.js-send-message-form', $.proxy( this.sendMessage, this ) );
			$( document ).on( 'click', '.js-preview-quote', $.proxy( this.previewQuote, this ) );
			$( document ).on( 'submit', '.js-edit-lead-form', $.proxy( this.editLead, this ) );
			$( document ).on( 'click', '.js-delete-lead', $.proxy( this.deleteLead, this ) );
			$( document ).on( 'click', '.js-add-part', $.proxy( this.addPart, this ) );
			$( document ).on( 'click', '.js-clone-part', $.proxy( this.clonePart, this ) );
			$( document ).on( 'click', '.js-copy-part-process', $.proxy( this.copyPartProcess, this ) );
			$( document ).on( 'click', '.js-paste-part-process', $.proxy( this.pastePartProcess, this ) );
			$( document ).on( 'click', '.js-copy-part-quantities', $.proxy( this.copyPartQuantities, this ) );
			$( document ).on( 'click', '.js-paste-part-quantities', $.proxy( this.pastePartQuantities, this ) );
			$( document ).on( 'click', '.js-copy-part-pricing', $.proxy( this.copyPartPricing, this ) );
			$( document ).on( 'click', '.js-paste-part-pricing', $.proxy( this.pastePartPricing, this ) );

			$( document ).on( 'click', '.js-paste-part-all', $.proxy( this.pastePartAll, this ) );

			$( document ).on( 'click', '.js-delete-part', $.proxy( this.deletePart, this ) );
			$( document ).on( 'click', '.js-add-part-quantity', $.proxy( this.addPartQuantity, this ) );
			$( document ).on( 'click', '.js-delete-part-quantity', $.proxy( this.deletePartQuantity, this ) );
			$( document ).on( 'click', '.js-add-part-process', $.proxy( this.addPartProcess, this ) );
			$( document ).on( 'click', '.js-add-part-operation', $.proxy( this.addPartOperation, this ) );
			$( document ).on( 'click', '.js-delete-part-process', $.proxy( this.deletePartProcess, this ) );
			$( document ).on( 'click', '.js-delete-part-operation', $.proxy( this.deletePartOperation, this ) );
			$( document ).on( 'change', '.js-import-part-file', $.proxy( this.getModelData, this ) );
			$( document ).on( 'change', 'select[name$="/operation"]', $.proxy( this.updateOperationData, this ) );
			$( document ).on( 'change', '.js-load-message-template', $.proxy( this.loadMessageTemplate, this ) );
			$( document ).on( 'change', 'select[name$="/plating_method"]', $.proxy( this.togglePlatingToolInput, this ) );

			$( document ).on( 'click', '.js-send-to-nutshell', $.proxy( this.sendToNutshell, this ) );

			$( document ).on( 'click', '.js-save-customer', $.proxy( this.saveCustomer, this ) );
		},

		onInputChange( e ) {
			this.setCanSendQuote( false );
		},

		setCanSendQuote( val ) {
			this.canSendQuote = val;
			this.checkCanSendQuote();
			return this.canSendQuote;
		},

		checkCanSendQuote() {
			if ( this.canSendQuote ) {
				$( '.js-prepare-quote' ).prop( 'disabled', false );
			} else {
				$( '.js-prepare-quote' ).prop( 'disabled', true );
			}
		},

		sendQuote( e ) {
			e.preventDefault();
			if ( confirm( 'Are you sure you want to send this quote?' ) == true ) {
				const rawData = $( e.target ).serialize();
				const data = {
					action: 'send_quote',
					lead_id: this.leadID,
					quote: rawData
				};
				PC_CPQ_Manage.Form.quote( data, ( ( response ) => {
					$( '#quote-details' ).replaceWith( response.data.html );
				} ) );
			}
		},

		sendMessage( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			const data = {
				action: 'send_message',
				lead_id: this.leadID,
				message: rawData
			};
			PC_CPQ_Manage.Form.message( data );
		},

		previewQuote( e ) {
			const rawData = $( e.target ).closest( 'form' ).serialize();
			const data = {
				action: 'preview_quote',
				lead_id: this.leadID,
				preview: rawData
			};
			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '.js-preview-quote-iframe' ).attr( 'src', response.data.url );
				$( '#preview-quote-modal' ).modal( 'show' );
			} ) );
		},

		editLead( e ) {
			e.preventDefault();
			PC_CPQ_Manage.Common.forceUnitSystem( 'imperial' );
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_lead', 'edit_lead_form', rawData, ( ( response ) => {
				$( '#edit-lead' ).replaceWith( response.data.html );
				this.leadID = response.data.leadID;
				this.setCanSendQuote( true );
				PC_CPQ_Manage.Common.initWpEditor( 'message' );
				PC_CPQ_Manage.Common.initWpEditor( 'quote_notes' );
				PC_CPQ_Manage.Common.initTooltips();
				PC_CPQ_Manage.Common.initSelect2();
				PC_CPQ_Manage.Common.convertUnits();
			} ) );
		},

		deleteLead( e ) {
			if ( confirm( 'Are you sure you want to delete this lead?' ) == true ) {
				const ID = $( e.currentTarget ).data( 'id' );
				const data = {
					action: 'delete_lead',
					lead_id: ID
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( 'tr[data-type="lead"][data-id="' + ID + '"]' ).remove();
				} ) );
			}
		},

		addPart() {
			const liveData = this.getLiveParts();
			const data = {
				action: 'add_part',
				lead_id: this.leadID,
				live_parts: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#lead-parts' ).html( response.data.html );
				$( '[data-target="#part-modal-' + response.data.partsCount + '"]' ).click();
				PC_CPQ_Manage.Common.initFileUploader();
				PC_CPQ_Manage.Common.initTooltips();
				PC_CPQ_Manage.Common.convertUnits();
			} ) );
		},

		clonePart( e ) {
			const liveData = this.getLiveParts();
			const data = {
				action: 'clone_part',
				lead_id: this.leadID,
				clone_part: $( e.currentTarget ).data( 'index' ),
				live_parts: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#lead-parts' ).html( response.data.html );
				$( '[data-target="#part-modal-' + response.data.partsCount + '"]' ).click();
				PC_CPQ_Manage.Common.initFileUploader();
				PC_CPQ_Manage.Common.initTooltips();
				PC_CPQ_Manage.Common.convertUnits();
			} ) );
		},

		copyPartProcess( e ) {
			const index = $( e.currentTarget ).data( 'index' );
			const processData = this.getLivePartProcess( index );

			this.setCopiedProcess( processData );
			this.setPasteTarget( 'process' );
			PC_CPQ_Manage.Form.toggleCopyProcessSuccess();
			$( '.js-paste-part-process' ).removeClass( 'disabled' );
			$( '.js-paste-part-all' ).removeClass( 'd-none' );
		},

		pastePartProcess( e ) {
			if ( this.getCopiedProcess() ) {
				let processAdded = [ ];
				const index = $( e.currentTarget ).data( 'index' );
				let params = new URLSearchParams( this.getCopiedProcess() );
				let processData = { };
				for ( let [k, v] of params )
					processData[k] = v;
				Object.entries( processData ).forEach( ( [key, value] ) => {
					let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + index + '/' );
					let processStepMatch = key.match( /raw_parts\/(\d+)\/processes\/(\d+)/ );
					let originalIndex = parseInt( processStepMatch[1] );
					let processStep = parseInt( processStepMatch[2] );
					if ( ! $( '[name^="' + newKey + '"]' ).length && ! processAdded.includes( processStep ) ) {
						$( '#part_' + index + '_processes' ).find( '.js-add-part-process' ).click();
						$( document ).on( 'pc_cpq_process_step_added', ( ( e ) => {
							this.delayedPasteProcess( index, processStep, originalIndex, processData );
						} ) );
						processAdded.push( processStep );
					} else {
						$( '[name^="' + newKey + '"]' ).val( value );
					}
					this.updateProcessRows( index );
				} );
				PC_CPQ_Manage.Form.togglePasteProcessSuccess();
			}
		},

		delayedPasteProcess( partNumber, processNumber, orginialPartNumber, data ) {
			let process = Object.entries( data ).filter( ( [key, value] ) => {
				let keyArr = key.split( '/' );
				return keyArr[1] == orginialPartNumber && keyArr[3] == processNumber;
			} );
			process.forEach( ( [key, value] ) => {
				let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + partNumber + '/' );
				$( '[name^="' + newKey + '"]' ).val( value );
			} );
			this.updateProcessRows( partNumber );
		},

		updateProcessRows( partNumber ) {
			$( 'tr[data-type="process"][data-part-index="' + partNumber + '"]' ).each( ( i, el ) => {
				let index = $( el ).data( 'index' );
				let partIndex = $( el ).data( 'part-index' );
				let value = $( el ).next().find( '[name="raw_parts/' + partIndex + '/processes/' + index + '/metal"]' ).val();
				$( el ).find( '[data-model="metal"]' ).text( value );
			} );
		},

		getCopiedProcess( forceUpdate = false ) {
			if ( null === this.process || forceUpdate ) {
				this.process = JSON.parse( localStorage.getItem( 'pc-cpq-PartProcess' ) || "[]" );
			}
			return this.process;
		},

		setCopiedProcess( process ) {
			this.process = process;
			localStorage.setItem( 'pc-cpq-PartProcess', JSON.stringify( process ) );
			return this.process;
		},

		getLiveParts() {
			return $( '[name^="raw_parts"]' ).serialize();
		},

		getLivePartProcess( index ) {
			return $( '[name^="raw_parts/' + index + '/processes/"]' ).serialize();
		},

		copyPartQuantities( e ) {
			const index = $( e.currentTarget ).data( 'index' );
			const quantitiesData = this.getLivePartQuantities( index );

			this.setCopiedQuantities( quantitiesData );
			this.setPasteTarget( 'quantities' );
			PC_CPQ_Manage.Form.toggleCopyQuantitiesSuccess();
			$( '.js-paste-part-quantities' ).removeClass( 'disabled' );
			$( '.js-paste-part-all' ).removeClass( 'd-none' );
		},

		pastePartQuantities( e ) {
			if ( this.getCopiedQuantities() ) {
				let quantitiesAdded = [ ];
				const index = $( e.currentTarget ).data( 'index' );
				let params = new URLSearchParams( this.getCopiedQuantities() );
				let quantitiesData = { };
				for ( let [k, v] of params )
					quantitiesData[k] = v;
				Object.entries( quantitiesData ).forEach( ( [key, value] ) => {
					let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + index + '/' );
					let quantitiesBreakPointMatch = key.match( /raw_parts\/(\d+)\/quantities\/(\d+)/ );
					let originalIndex = parseInt( quantitiesBreakPointMatch[1] );
					let quantitiesBreakPoint = parseInt( quantitiesBreakPointMatch[2] );
					if ( ! $( '[name^="' + newKey + '"]' ).length && ! quantitiesAdded.includes( quantitiesBreakPoint ) ) {
						$( '#part_' + index + '_quantities' ).find( '.js-add-part-quantity' ).click();
						$( document ).on( 'pc_cpq_quantities_added', ( ( e ) => {
							this.delayedPasteQuantities( index, quantitiesBreakPoint, originalIndex, quantitiesData );
						} ) );
						quantitiesAdded.push( quantitiesBreakPoint );
					} else {
						$( '[name^="' + newKey + '"]' ).val( value );
				}
				} );
				PC_CPQ_Manage.Form.togglePasteQuantitiesSuccess();
			}
		},

		delayedPasteQuantities( partNumber, quantitiesNumber, orginialPartNumber, data ) {
			let quantities = Object.entries( data ).filter( ( [key, value] ) => {
				let keyArr = key.split( '/' );
				return keyArr[1] == orginialPartNumber && keyArr[3] == quantitiesNumber;
			} );
			quantities.forEach( ( [key, value] ) => {
				let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + partNumber + '/' );
				$( '[name^="' + newKey + '"]' ).val( value );
			} );
		},

		getLivePartQuantities( index ) {
			return $( '[name^="raw_parts/' + index + '/quantities/"]' ).serialize();
		},

		getCopiedQuantities( forceUpdate = false ) {
			if ( null === this.quantities || forceUpdate ) {
				this.quantities = JSON.parse( localStorage.getItem( 'pc-cpq-PartQuantities' ) || "[]" );
			}
			return this.quantities;
		},

		setCopiedQuantities( quantities ) {
			this.quantities = quantities;
			localStorage.setItem( 'pc-cpq-PartQuantities', JSON.stringify( quantities ) );
			return this.quantities;
		},

		copyPartPricing( e ) {
			const index = $( e.currentTarget ).data( 'index' );
			const pricingData = this.getLivePartPricing( index );

			this.setCopiedPricing( pricingData );
			this.setPasteTarget( 'pricing' );
			PC_CPQ_Manage.Form.toggleCopyPricingSuccess();
			$( '.js-paste-part-pricing' ).removeClass( 'disabled' );
			$( '.js-paste-part-all' ).removeClass( 'd-none' );
		},

		pastePartPricing( e ) {
			if ( this.getCopiedPricing() ) {
				let pricingAdded = [ ];
				const index = $( e.currentTarget ).data( 'index' );
				let params = new URLSearchParams( this.getCopiedPricing() );
				let pricingData = { };
				for ( let [k, v] of params )
					pricingData[k] = v;
				Object.entries( pricingData ).forEach( ( [key, value] ) => {
					let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + index + '/' );
					let pricingBreakPointMatch = key.match( /raw_parts\/(\d+)\/pricing\/(\d+)/ );
					let originalIndex = parseInt( pricingBreakPointMatch[1] );
					let pricingBreakPoint = parseInt( pricingBreakPointMatch[2] );
					if ( ! $( '[name^="' + newKey + '"]' ).length && ! pricingAdded.includes( pricingBreakPoint ) ) {
						$( '#part_' + index + '_pricing' ).find( '.js-add-part-pricing' ).click();
						$( document ).on( 'pc_cpq_pricing_added', ( ( e ) => {
							this.delayedPastePricing( index, pricingBreakPoint, originalIndex, pricingData );
						} ) );
						pricingAdded.push( pricingBreakPoint );
					} else {
						$( '[name^="' + newKey + '"]' ).val( value );
				}
				} );
				PC_CPQ_Manage.Form.togglePastePricingSuccess();
			}
		},

		delayedPastePricing( partNumber, pricingNumber, orginialPartNumber, data ) {
			let pricing = Object.entries( data ).filter( ( [key, value] ) => {
				let keyArr = key.split( '/' );
				return keyArr[1] == orginialPartNumber && keyArr[3] == pricingNumber;
			} );
			pricing.forEach( ( [key, value] ) => {
				let newKey = key.replace( /raw_parts\/\d+\//, 'raw_parts/' + partNumber + '/' );
				$( '[name^="' + newKey + '"]' ).val( value );
			} );
		},

		getLivePartPricing( index ) {
			return $( '[name^="raw_parts/' + index + '/pricing/"]' ).serialize();
		},

		getCopiedPricing( forceUpdate = false ) {
			if ( null === this.pricing || forceUpdate ) {
				this.pricing = JSON.parse( localStorage.getItem( 'pc-cpq-PartPricing' ) || "[]" );
			}
			return this.pricing;
		},

		setCopiedPricing( pricing ) {
			this.pricing = pricing;
			localStorage.setItem( 'pc-cpq-PartPricing', JSON.stringify( pricing ) );
			return this.pricing;
		},

		pastePartAll() {
			let data, btnSelector;
			switch ( this.getPasteTarget() ) {
				case 'process':
					data = this.getCopiedProcess();
					btnSelector = '.js-paste-part-process';
					break;
				case 'quantities':
					data = this.getCopiedQuantities();
					btnSelector = '.js-paste-part-quantities';
					break;
				case 'pricing':
					data = this.getCopiedPricing();
					btnSelector = '.js-paste-part-pricing';
					break;
			}

			$( btnSelector ).each( ( i, btn ) => {
				$( btn ).click();
			} );

			$( '.js-paste-part-all' ).addClass( 'd-none' );
		},

		getPasteTarget() {
			return this.pasteTarget;
		},

		setPasteTarget( target ) {
			this.pasteTarget = target;
			return this.pasteTarget;
		},

		deletePart( e ) {
			if ( confirm( 'Are you sure you want to delete this part?' ) == true ) {
				const liveData = this.getLiveParts();
				const data = {
					action: 'delete_part',
					lead_id: this.leadID,
					index: $( e.currentTarget ).data( 'index' ),
					live_parts: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#lead-parts' ).html( response.data.html );
				} ) );
			}
		},

		addPartQuantity( e ) {
			const index = $( e.currentTarget ).closest( '[data-type="part"]' ).attr( 'data-index' );
			const liveData = this.getLivePartQuantities( index );
			const data = {
				action: 'add_part_quantity',
				lead_id: this.leadID,
				part_id: index,
				live_part_quantities: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#part_' + response.data.i + '_quantities' ).html( response.data.html );
				$( document ).trigger( 'pc_cpq_quantities_added' );
			} ) );
		},

		getLivePartQuantities( index ) {
			return $( '[name^="raw_parts/' + index + '/quantities"]' ).serialize();
		},

		deletePartQuantity( e ) {
			if ( confirm( 'Are you sure you want to delete this quantity?' ) == true ) {
				const partId = $( e.currentTarget ).attr( 'data-part-index' );
				const liveData = this.getLivePartQuantities( partId );
				const data = {
					action: 'delete_part_quantity',
					lead_id: this.leadID,
					part_id: partId,
					index: $( e.currentTarget ).data( 'index' ),
					live_part_quantities: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#part_' + response.data.i + '_quantities' ).html( response.data.html );
				} ) );
			}
		},

		addPartProcess( e ) {
			const index = $( e.currentTarget ).closest( '[data-type="part"]' ).attr( 'data-index' );
			const liveData = this.getLivePartProcesses( index );
			const data = {
				action: 'add_part_process',
				lead_id: this.leadID,
				part_id: index,
				live_part_processes: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#part_' + response.data.i + '_processes' ).html( response.data.html );
				$( document ).trigger( 'pc_cpq_process_step_added' );
			} ) );
		},

		getLivePartProcesses( index ) {
			return $( '[name^="raw_parts/' + index + '/processes"]' ).serialize();
		},

		deletePartProcess( e ) {
			if ( confirm( 'Are you sure you want to delete this process?' ) == true ) {
				const partId = $( e.currentTarget ).attr( 'data-part-index' );
				const liveData = this.getLivePartProcesses( partId );
				const data = {
					action: 'delete_part_process',
					lead_id: this.leadID,
					part_id: partId,
					index: $( e.currentTarget ).attr( 'data-index' ),
					live_part_processes: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#part_' + response.data.i + '_processes' ).html( response.data.html );
				} ) );
			}
		},

		addPartOperation( e ) {
			const index = $( e.currentTarget ).closest( '[data-type="part"]' ).attr( 'data-index' );
			const liveData = this.getLivePartOperations( index );
			const data = {
				action: 'add_part_operation',
				lead_id: this.leadID,
				part_id: index,
				live_part_operations: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#part_' + response.data.i + '_plating' ).html( response.data.html );
				this.updateAllOperationData();
			} ) );
		},

		getLivePartOperations( index ) {
			return $( '[name^="raw_parts/' + index + '/routing"]' ).serialize();
		},

		deletePartOperation( e ) {
			if ( confirm( 'Are you sure you want to delete this operation?' ) == true ) {
				const data = {
					action: 'delete_part_operation',
					lead_id: this.leadID,
					part_id: $( e.currentTarget ).attr( 'data-part-index' ),
					index: $( e.currentTarget ).attr( 'data-index' )
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#part_' + response.data.i + '_plating' ).html( response.data.html );
				} ) );
			}
		},

		updatePartData( rawData ) {
			const $modal = $( '.modal[data-type="part"][data-index="' + rawData.index + '"]' );
			const data = this._prepFileData( rawData );
			Object.entries( data ).forEach( ( [ key, value ] ) => {
				$modal.find( '[name="raw_parts/' + rawData.index + '/' + key + '"]' ).val( value );
			} );
			PC_CPQ_Manage.Common.convertUnits();
		},

		updateAllOperationData() {
			$( 'select[name$="/operation"]' ).trigger( 'change', [ { spc_manual_change: true } ] );
		},

		updateOperationData( e ) {
			const $select = $( e.currentTarget );
			const $parent = $select.closest( '.js-part-operation' );
			const partIndex = $parent.attr( 'data-part-index' );
			const index = $parent.attr( 'data-index' );
			const operation = this.getOperationByName( $select.val() );

			$( '[data-model="operation_description"]', $parent ).html( operation.description );
			$( '[data-model="operation_time"]', $parent ).html( operation.cycle_time + ' ' + operation.cycle_unit );

			PC_CPQ_Helpers.debounce( $.proxy( this.updateOperationTotalTime( partIndex ), this ) );
		},

		updateOperationTotalTime( partIndex ) {
			const timeInputs = $( '.js-part-operation[data-part-index="' + partIndex + '"]' ).find( '[data-model="operation_time"]' );
			const totalTime = this.calculateOperationTime( timeInputs );
			$( '[data-model="part_total_operation_time"][data-index="' + partIndex + '"]' ).html( totalTime + ' hrs.' );
		},

		loadMessageTemplate( e ) {
			const $select = $( e.currentTarget );
			const value = $select.val();
			if ( value !== 'null' ) {
				const message = this.getMessageByName( value );
				tinyMCE.get( 'message' ).setContent( message.template );
				$select.val( 'null' );
			}
		},

		getMessageByName( name ) {
			return PC_CPQ_ManageConfig.templates.find( ( template ) => template.name == name );
		},

		getOperationByName( name ) {
			return PC_CPQ_ManageConfig.operations.find( ( op ) => op.operation == name );
		},

		calculateOperationTime( timeInputs ) {
			const times = $.map( timeInputs, ( time ) => PC_CPQ_Helpers.standardizeTimes( $( time ).text().trim() ) ).filter( ( time ) => time );
			let total = times.reduce( ( sum, a ) => sum + a, 0 );
			return total / 60 / 60;
		},

		initPlatingToolInputs() {
			$( 'select[name$="/plating_method"]' ).each( ( i, select ) => {
				$( select ).trigger( 'change', [ { spc_manual_change: true } ] );
			} )
		},

		togglePlatingToolInput( e ) {
			const $select = $( e.target );
			const tool = $select.val();
			if ( tool == '' ) {
				return false;
			}

			$select.closest( '.tab-pane' ).find( 'select[name*="/plating_tool_"]' ).closest( '.form-group' ).addClass( 'd-none' );
			if ( tool == 'Barrel' ) {
				$select.closest( '.tab-pane' ).find( 'select[name$="/plating_tool_barrel"]' ).closest( '.form-group' ).removeClass( 'd-none' );
			}

			if ( tool == 'Rack' ) {
				$select.closest( '.tab-pane' ).find( 'select[name$="/plating_tool_rack"]' ).closest( '.form-group' ).removeClass( 'd-none' );
			}
		},

		sendToNutshell() {
			const data = {
				action: 'send_to_nutshell',
				lead_id: this.leadID
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#nutshell-input' ).replaceWith( response.data.html );
			} ) );
		},

		_prepFileData( rawData ) {
			return {
				file_name: rawData.fileName,
				area_computed: convert( rawData.area, 'mm2' ).to( 'ft2' ),
				volume_computed: convert( rawData.volume, 'mm3' ).to( 'in3' ),
				d_x_computed: convert( Math.abs( rawData.maxX - rawData.minX ), 'mm' ).to( 'in' ),
				d_y_computed: convert( Math.abs( rawData.maxY - rawData.minY ), 'mm' ).to( 'in' ),
				d_z_computed: convert( Math.abs( rawData.maxZ - rawData.minZ ), 'mm' ).to( 'in' )
			}
		},

		getModelData: async function ( e ) {
			const $input = $( e.currentTarget );
			const index = $input.data( 'index' );
			const file = $input.prop( 'files' )[0];
//			const apiUrl = 'https://archive.sharrettsplating.com/cgi-bin/stpmeasure.cgi';
			const apiUrl = 'https://stp-api.snowberrymedia.com/measure.php';
			const formData = new FormData();
			formData.append( 'file', file );
			const response = await fetch( apiUrl, {
				method: 'POST',
				headers: {
					'X-API-KEY': '9f4c8e1a7b3d6c2f0e5a4b8c1d9e7f6a2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7'
				},
				body: formData
			} );
			let text = await response.text();
			if ( text.includes( '[{ "isSuccess"' ) ) { // fix json bug
				text = text.replace( /\[\{/, '[{}],' );
			}
			const json = JSON.parse( text );

			if ( json.isSuccess ) {
				for ( const data of json.filesInfo ) {
					this.saveFileData( file.name, data, index );
				}
			} else {
				this.updatePartData( { fileName: file.name, index: index } );
			}
		},

		saveFileData( fileName, data, index ) {
			data.fileName = fileName;
			data.index = index;
			this.updatePartData( data );
		},

		renderStepFiles: async function () {
			const occt = await occtimportjs(); // Load WASM once

			$( '.step-viewer[data-url]' ).each( async function () {
				const $canvas = $( this );
				const canvas = $canvas[0];
				const url = $canvas.data( 'url' );

				const res = await fetch( url );
				const buffer = await res.arrayBuffer();
				const data = new Uint8Array( buffer );

				const shape = occt.ReadStepFile( data, null );
				if ( ! shape || ! shape.meshes ) {
					console.error( 'STEP parsing failed or mesh is missing.' );
					return;
				}

				const scene = new THREE.Scene();
				const camera = new THREE.PerspectiveCamera( 45, canvas.width / canvas.height, 0.1, 1000 );
				const renderer = new THREE.WebGLRenderer( { canvas, antialias: true } );
				renderer.setSize( canvas.width, canvas.height );
				renderer.setClearColor( 0x000000, 1 );

				const group = new THREE.Group();

				for ( const meshData of shape.meshes ) {
					if ( ! meshData?.positions || ! meshData?.indices )
						continue;
					const geometry = new THREE.BufferGeometry();
					geometry.setAttribute(
							'position',
							new THREE.BufferAttribute(
									meshData.positions instanceof Float32Array ? meshData.positions : new Float32Array( meshData.positions ),
									3
									)
							);
					geometry.setIndex(
							new THREE.BufferAttribute(
									meshData.indices instanceof Uint32Array ? meshData.indices : new Uint32Array( meshData.indices ),
									1
									)
							);
					geometry.computeVertexNormals();

					const material = new THREE.MeshNormalMaterial( { side: THREE.DoubleSide } );
					const mesh = new THREE.Mesh( geometry, material );
					group.add( mesh );
				}

				scene.add( group );

				// Center the model and adjust the camera
				const box = new THREE.Box3().setFromObject( group );
				const size = box.getSize( new THREE.Vector3() ).length();
				const center = box.getCenter( new THREE.Vector3() );

				group.position.sub( center );
				camera.position.set( 0, 0, size * 1.5 );
				camera.lookAt( 0, 0, 0 );

				scene.add( new THREE.AmbientLight( 0xffffff, 1 ) );

				( function animate() {
					requestAnimationFrame( animate );
					group.rotation.y += 0.01; // ✅ rotate the mesh group, not the scene
					renderer.render( scene, camera );
				} )();
			} );
		},

		customerLookup() {
			var $el = $( '.js-customer-select' );
			if ( ! $el.length )
				return;

			$el.select2( {
				ajax: {
					url: PC_CPQ_ManageConfig.ajaxurl,
					dataType: 'json',
					delay: 250,
					data: function ( params ) {
						return {
							action: 'search_customers',
							q: params.term || '',
							page: params.page || 1
						};
					},
					processResults: function ( data, params ) {
						params.page = params.page || 1;
						return {
							results: data.results || [ ],
							pagination: { more: data.pagination && data.pagination.more }
						};
					},
					cache: true
				},
				placeholder: 'Search customers…',
				allowClear: true,
				minimumInputLength: 1,
				width: '100%'
			} );
		},

		saveCustomer() {
			const foundCustomer = $( '.js-customer-select' ).val();
			const createCustomer = $( '#create_company' ).val();

			const data = {
				action: 'save_customer',
				lead_id: this.leadID,
				foundCustomer: foundCustomer,
				createCustomer: createCustomer
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#customer-modal' ).modal( 'hide' )
						.on( 'hidden.bs.modal', () => {
							$( '#edit-lead' ).replaceWith( response.data.html );
							PC_CPQ_Manage.Common.initWpEditor( 'message' );
							PC_CPQ_Manage.Common.initWpEditor( 'quote_notes' );
							PC_CPQ_Manage.Common.initTooltips();
							PC_CPQ_Manage.Common.initSelect2();
							PC_CPQ_Manage.Common.convertUnits();
						} );
			} ) );
		}
	};

	PC_CPQ_Manage.Customer = {

		customerID: $( 'input[name="customer_id"]' ).val(),

		init() {
			this.bind();
		},

		bind() {
			$( document ).on( 'submit', '.js-edit-customer-form', $.proxy( this.editCustomer, this ) );
			$( document ).on( 'click', '.js-delete-customer', $.proxy( this.deleteCustomer, this ) );
			$( document ).on( 'click', '.js-add-contact', $.proxy( this.addContact, this ) );
			$( document ).on( 'click', '.js-delete-contact', $.proxy( this.deleteContact, this ) );
			$( document ).on( 'click', '.js-add-shipping', $.proxy( this.addShipping, this ) );
			$( document ).on( 'click', '.js-delete-shipping', $.proxy( this.deleteShipping, this ) );
		},

		editCustomer( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_customer', 'edit_customer_form', rawData, ( ( response ) => {
				$( '#edit-customer' ).html( response.data.html );
			} ) );
		},

		deleteCustomer( e ) {
			if ( confirm( 'Are you sure you want to delete this customer?' ) == true ) {
				const ID = $( e.currentTarget ).data( 'id' );
				const data = {
					action: 'delete_customer',
					lead_id: ID
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( 'tr[data-type="customer"][data-id="' + ID + '"]' ).remove();
				} ) );
			}
		},

		addContact() {
			const liveData = this.getLiveContacts();
			const data = {
				action: 'add_contact',
				customer_id: this.customerID,
				live_contacts: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#customer-contacts' ).html( response.data.html );
				$( '[data-target="#contact-modal-' + response.data.contactsCount + '"]' ).click();
			} ) );
		},

		deleteContact( e ) {
			if ( confirm( 'Are you sure you want to delete this contact?' ) == true ) {
				const liveData = this.getLiveContacts();
				const data = {
					action: 'delete_contact',
					customer_id: this.customerID,
					index: $( e.currentTarget ).data( 'index' ),
					live_contacts: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#customer-contacts' ).html( response.data.html );
				} ) );
			}
		},

		getLiveContacts() {
			return $( '[name^="raw_contacts"]' ).serialize();
		},

		addShipping() {
			const liveData = this.getLiveShipping();
			const data = {
				action: 'add_shipping',
				customer_id: this.customerID,
				live_shipping: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#customer-shipping' ).html( response.data.html );
				$( '[data-target="#shipping-modal-' + response.data.shippingCount + '"]' ).click();
			} ) );
		},

		deleteShipping( e ) {
			if ( confirm( 'Are you sure you want to delete this shipping address?' ) == true ) {
				const liveData = this.getLiveShipping();
				const data = {
					action: 'delete_shipping',
					customer_id: this.customerID,
					index: $( e.currentTarget ).data( 'index' ),
					live_shipping: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#customer-shipping' ).html( response.data.html );
				} ) );
			}
		},

		getLiveShipping() {
			return $( '[name^="raw_shipping"]' ).serialize();
		}
	};

	PC_CPQ_Manage.Settings = {

		init() {
			this.bind();
			this.initMetalMaterialInputs();
			this.initSortable();
		},

		bind() {
			$( document ).on( 'submit', '.js-edit-settings-parts-form', $.proxy( this.editSettingsParts, this ) );
			$( document ).on( 'submit', '.js-edit-settings-quotes-form', $.proxy( this.editSettingsQuotes, this ) );
			$( document ).on( 'submit', '.js-edit-settings-plating-form', $.proxy( this.editSettingsPlating, this ) );
			$( document ).on( 'submit', '.js-edit-settings-processes-form', $.proxy( this.editSettingsProcesses, this ) );
			$( document ).on( 'submit', '.js-edit-settings-templates-form', $.proxy( this.editSettingsTemplates, this ) );
			$( document ).on( 'click', '.js-add-email-template', $.proxy( this.addEmailTemplate, this ) );
			$( document ).on( 'click', '.js-delete-email-template', $.proxy( this.deleteEmailTemplate, this ) );
			$( document ).on( 'click', '.js-add-metal', $.proxy( this.addMetal, this ) );
			$( document ).on( 'click', '.js-delete-metal', $.proxy( this.deleteMetal, this ) );
			$( document ).on( 'click', '.js-add-plating-metal', $.proxy( this.addPlatingMetal, this ) );
			$( document ).on( 'click', '.js-delete-plating-metal', $.proxy( this.deletePlatingMetal, this ) );
			$( document ).on( 'click', '.js-add-line', $.proxy( this.addLine, this ) );
			$( document ).on( 'click', '.js-delete-line', $.proxy( this.deleteLine, this ) );
			$( document ).on( 'click', '.js-add-barrel', $.proxy( this.addBarrel, this ) );
			$( document ).on( 'click', '.js-delete-barrel', $.proxy( this.deleteBarrel, this ) );
			$( document ).on( 'click', '.js-add-rack', $.proxy( this.addRack, this ) );
			$( document ).on( 'click', '.js-delete-rack', $.proxy( this.deleteRack, this ) );
			$( document ).on( 'click', '.js-add-operation', $.proxy( this.addOperation, this ) );
			$( document ).on( 'click', '.js-delete-operation', $.proxy( this.deleteOperation, this ) );
			$( document ).on( 'submit', '.js-import-settings-file-form', $.proxy( this.importSettings, this ) );
			$( document ).on( 'click', '.js-export-settings', $.proxy( this.exportSettings, this ) );
			$( document ).on( 'change', 'select[name^="raw_operations/"][name$="/type"]', $.proxy( this.toggleMetalMaterialInput, this ) );
		},

		importSettings( e ) {
			e.preventDefault();
			const $input = $( e.target ).find( '.js-import-settings-file' );
			const type = $input.data( 'type' );
			const fileName = 'pq_' + type + '_import_file';
			const data = {
				action: 'import_settings',
				type: type,
				file_name: fileName,
				[fileName]: $input.prop( 'files' )[0]
			};

			PC_CPQ_Manage.Form.import( data, type, ( ( response ) => {
				if ( response.success ) {
					window.location.reload();
				}
			} ) );
		},

		exportSettings( e ) {
			e.preventDefault();
			const $input = $( e.currentTarget );
			const type = $input.data( 'type' );
			const data = {
				action: 'export_settings',
				type: type,
			};

			PC_CPQ_Manage.Form.export( data, type );
		},

		editSettingsParts( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_settings_parts', 'edit_settings_parts_form', rawData, ( ( response ) => {
				$( '#edit-settings-parts' ).html( response.data.html );
			} ) );
		},

		editSettingsQuotes( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_settings_quotes', 'edit_settings_quotes_form', rawData, ( ( response ) => {
				$( '#edit-settings-quotes' ).html( response.data.html );
			} ) );
		},

		editSettingsPlating( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_settings_plating', 'edit_settings_plating_form', rawData, ( ( response ) => {
				$( '#edit-settings-plating' ).html( response.data.html );
			} ) );
		},

		editSettingsProcesses( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_settings_processes', 'edit_settings_processes_form', rawData, ( ( response ) => {
				$( '#edit-settings-processes' ).html( response.data.html );
				PC_CPQ_Manage.Common.initSelect2();
			} ) );
		},

		editSettingsTemplates( e ) {
			e.preventDefault();
			const rawData = $( e.target ).serialize();
			PC_CPQ_Manage.Form.save( 'edit_settings_templates', 'edit_settings_templates_form', rawData, ( ( response ) => {
				$( '#edit-settings-templates' ).html( response.data.html );
				PC_CPQ_Manage.Common.initWpEditor( 'quote_header' );
				PC_CPQ_Manage.Common.initWpEditor( 'quote_footer' );
				PC_CPQ_Manage.Common.initWpEditor( 'quote_terms' );
				this.refreshEmailTemplateWpEditors();
			} ) );
		},

		addEmailTemplate() {
			const liveData = this.getLiveEmailTemplates();
			const data = {
				action: 'add_email_template',
				live_email_templates: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#email-templates' ).html( response.data.html );
				$( '[data-target="#email-template-modal-' + response.data.emailTemplatesCount + '"]' ).click();
				this.refreshEmailTemplateWpEditors();
			} ) );
		},

		deleteEmailTemplate( e ) {
			if ( confirm( 'Are you sure you want to delete this email template?' ) == true ) {
				const liveData = this.getLiveEmailTemplates();
				const data = {
					action: 'delete_email_template',
					index: $( e.currentTarget ).data( 'index' ),
					live_email_templates: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#email-templates' ).html( response.data.html );
					this.refreshEmailTemplateWpEditors();
				} ) );
			}
		},

		getLiveEmailTemplates() {
			return $( '[name^="raw_email_templates"]' ).serialize();
		},

		refreshEmailTemplateWpEditors() {
			$( '.email-template-modals .modal' ).each( ( i, modal ) => {
				PC_CPQ_Manage.Common.initWpEditor( 'template_' + $( modal ).data( 'index' ) );
			} );
		},

		addMetal() {
			const liveData = this.getLiveMetals();
			const data = {
				action: 'add_metal',
				live_metals: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#metals' ).html( response.data.html );
				$( '[data-target="#metal-modal-' + response.data.metalsCount + '"]' ).click();
			} ) );
		},

		deleteMetal( e ) {
			if ( confirm( 'Are you sure you want to delete this metal?' ) == true ) {
				const liveData = this.getLiveMetals();
				const data = {
					action: 'delete_metal',
					index: $( e.currentTarget ).data( 'index' ),
					live_metals: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#metals' ).html( response.data.html );
				} ) );
			}
		},

		getLiveMetals() {
			return $( '[name^="raw_metals"]' ).serialize();
		},

		addPlatingMetal() {
			const liveData = this.getLivePlatingMetals();
			const data = {
				action: 'add_plating_metal',
				live_plating_metals: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#plating-metals' ).html( response.data.html );
				$( '[data-target="#plating-metal-modal-' + response.data.platingMetalsCount + '"]' ).click();
			} ) );
		},

		deletePlatingMetal( e ) {
			if ( confirm( 'Are you sure you want to delete this plating metal?' ) == true ) {
				const liveData = this.getLivePlatingMetals();
				const data = {
					action: 'delete_plating_metal',
					index: $( e.currentTarget ).data( 'index' ),
					live_plating_metals: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#plating-metals' ).html( response.data.html );
				} ) );
			}
		},

		getLivePlatingMetals() {
			return $( '[name^="raw_plating_metals"]' ).serialize();
		},

		addLine() {
			const liveData = this.getLiveLines();
			const data = {
				action: 'add_line',
				live_lines: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#lines' ).html( response.data.html );
				$( '[data-target="#line-modal-' + response.data.linesCount + '"]' ).click();
			} ) );
		},

		deleteLine( e ) {
			if ( confirm( 'Are you sure you want to delete this line?' ) == true ) {
				const liveData = this.getLiveLines();
				const data = {
					action: 'delete_line',
					index: $( e.currentTarget ).data( 'index' ),
					live_lines: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#lines' ).html( response.data.html );
				} ) );
			}
		},

		getLiveLines() {
			return $( '[name^="raw_lines"]' ).serialize();
		},

		addBarrel() {
			const liveData = this.getLiveBarrels();
			const data = {
				action: 'add_barrel',
				live_barrels: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#barrels' ).html( response.data.html );
				$( '[data-target="#barrel-modal-' + response.data.barrelsCount + '"]' ).click();
			} ) );
		},

		deleteBarrel( e ) {
			if ( confirm( 'Are you sure you want to delete this barrel?' ) == true ) {
				const liveData = this.getLiveBarrels();
				const data = {
					action: 'delete_barrel',
					index: $( e.currentTarget ).data( 'index' ),
					live_barrels: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#barrels' ).html( response.data.html );
				} ) );
			}
		},

		getLiveBarrels() {
			return $( '[name^="raw_barrels"]' ).serialize();
		},

		addRack() {
			const liveData = this.getLiveRacks();
			const data = {
				action: 'add_rack',
				live_racks: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#racks' ).html( response.data.html );
				$( '[data-target="#rack-modal-' + response.data.racksCount + '"]' ).click();
			} ) );
		},

		deleteRack( e ) {
			if ( confirm( 'Are you sure you want to delete this rack?' ) == true ) {
				const liveData = this.getLiveRacks();
				const data = {
					action: 'delete_rack',
					index: $( e.currentTarget ).data( 'index' ),
					live_racks: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#racks' ).html( response.data.html );
				} ) );
			}
		},

		getLiveRacks() {
			return $( '[name^="raw_racks"]' ).serialize();
		},

		addOperation() {
			const liveData = this.getLiveOperations();
			const data = {
				action: 'add_operation',
				live_operations: liveData
			};

			PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
				$( '#operations' ).html( response.data.html );
				$( '[data-target="#operation-modal-' + response.data.operationsCount + '"]' ).click();
				this.refreshOperationWpEditors();
			} ) );
		},

		deleteOperation( e ) {
			if ( confirm( 'Are you sure you want to delete this operation?' ) == true ) {
				const liveData = this.getLiveOperations();
				const data = {
					action: 'delete_operation',
					index: $( e.currentTarget ).data( 'index' ),
					live_operations: liveData
				};

				PC_CPQ_Manage.Form.fetch( data, ( ( response ) => {
					$( '#operations' ).html( response.data.html );
					this.refreshOperationWpEditors();
				} ) );
			}
		},

		getLiveOperations() {
			return $( '[name^="raw_operations"]' ).serialize();
		},

		refreshOperationWpEditors() {
			$( '.operation-modals .modal' ).each( ( i, modal ) => {
				PC_CPQ_Manage.Common.initWpEditor( 'description_' + $( modal ).data( 'index' ) );
			} );
		},

		initWpEditor( id ) {
			if ( typeof tinymce != 'undefined' ) {
				tinymce.execCommand( 'mceRemoveEditor', false, id );
				tinymce.execCommand( 'mceAddEditor', false, id );
			}
		},

		initMetalMaterialInputs() {
			$( 'select[name^="raw_operations/"][name$="/type"]' ).each( ( i, select ) => {
				$( select ).trigger( 'change', [ { spc_manual_change: true } ] );
			} )
		},

		toggleMetalMaterialInput( e ) {
			const $select = $( e.target );
			const type = $select.val();
			if ( type == '' || type == 'null' ) {
				return false;
			}

			$select.closest( '.card-body' ).find( 'select[name*="/base_metal"],select[name*="/material"]' ).closest( '.form-group' ).addClass( 'd-none' );
			if ( type == 'Prep' ) {
				$select.closest( '.card-body' ).find( 'select[name$="/base_metal"]' ).closest( '.form-group' ).removeClass( 'd-none' );
			}

			if ( type == 'Plating' ) {
				$select.closest( '.card-body' ).find( 'select[name$="/material"]' ).closest( '.form-group' ).removeClass( 'd-none' );
			}
		},

		initSortable() {
			if ( ! $( '#post-operations' ).length )
				return;

			const postOps = new Sortable( $( '#post-operations .table > tbody' )[0], {
				handle: '.js-sortable-handle',
				draggable: 'tr',
				dataIdAttr: 'data-index',
				onSort( e ) {
					var order = [ ];
					$( postOps.el ).find( 'tr' ).each( ( i, tr ) => {
						order.push( {
							operation: $( '[data-model="operation"]', tr ).text()
						} );
						let newIndex = i + 1;
						$( tr ).attr( 'data-index', i )
								.find( 'td:first-child' ).text( newIndex + '.' );
					} );
					$( '#post_ops_order' ).val( JSON.stringify( order ) );
				}
			} );
		}
	};

	PC_CPQ_Manage.Tour = {

		id: 'pc-cpq-tour',
		instance: null,

		init() {
			this.instance = new Shepherd.Tour( PC_CPQ_TourConfig );
			this.bind();

			if ( this.shouldStart() ) {
				this.start();
			}
		},

		bind() {
			$( document ).on( 'click', '.js-restart-tour', $.proxy( this.start, this ) );
			this.instance.on( 'cancel', $.proxy( this.dismiss, this ) );
			this.instance.on( 'complete', $.proxy( this.dismiss, this ) );
		},

		start( e ) {
			if ( e ) {
				e.preventDefault();
			}
			this.instance.start();
		},

		shouldStart() {
			return $( '.js-restart-tour' ).length && ! localStorage.getItem( this.id );
		},

		dismiss() {
			if ( ! localStorage.getItem( this.id ) ) {
				localStorage.setItem( this.id, 'yes' );
			}
		}

	};

	const onDocReady = [
		() => {
			PC_CPQ_Manage.Form.init();
		},
		() => {
			PC_CPQ_Manage.Common.init();
		},
		() => {
			PC_CPQ_Manage.Lead.init();
		},
		() => {
			PC_CPQ_Manage.Customer.init();
		},
		() => {
			PC_CPQ_Manage.Settings.init();
		},
		() => {
			PC_CPQ_Manage.Tour.init();
		}
	];

	// Iterate through callbacks and move each callback separately to event queue
	$( function () {
		onDocReady.forEach( callback => {
			setTimeout( callback, 0 );
		} );
	} );

	return PC_CPQ_Manage;
}( PC_CPQ_Manage || { }, jQuery, Pace ) );