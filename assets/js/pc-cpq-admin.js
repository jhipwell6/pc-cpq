/* ---------------------------------------------------------------------
Global Js
Target Browsers: All
------------------------------------------------------------------------ */

var PC_CPQ_AdminHelpers = (function(PC_CPQ_AdminHelpers, $) {

	PC_CPQ_AdminHelpers.mm_to_in = ( mm ) => {
		return ( mm / parseFloat( 25.4 ) );
	};

	PC_CPQ_AdminHelpers.mm2_to_ft2 = ( mm2 ) => {
		return ( mm2 / parseFloat( 92903.04 ) );
	};

	PC_CPQ_AdminHelpers.mm3_to_in3 = ( mm3 ) => {
		return ( mm3 / parseFloat( 16387.064001 ) );
	};

	return PC_CPQ_AdminHelpers;

}(PC_CPQ_AdminHelpers || {}, jQuery));

var PC_CPQ_Admin = (function(PC_CPQ_Admin, $) {

    /**
     * Doc Ready
     */
    $(function() {
        PC_CPQ_Admin.Lead.init();
        PC_CPQ_Admin.LeadProcesses.init();
        PC_CPQ_Admin.LeadParts.init();
	});
	
	PC_CPQ_Admin.Lead = {
		
		apiUrl: 'https://stp-api.snowberrymedia.com/measure.php',

		init() {
			if ( pagenow == 'lead' ) {
				this.setStatus();
				if ( typeof acf != 'undefined' ) {
					acf.addAction( 'load_field/key=field_621d0f3b9bdec', $.proxy( this.initPartFileField, this ) );
					acf.addAction( 'append_field/key=field_621d0f3b9bdec', $.proxy( this.initPartFileField, this ) );
				}				
			}
			this.bind();
		},

		bind() {
			$( document ).on( 'change', '.js-upload-part-file', $.proxy( this.getModelData, this ) );
			$( document ).on( 'spc:part_added', $.proxy( this.onFileUploaded, this ) );
			$( document ).on( 'change', '[data-name="operation"] select', $.proxy( this.loadOperation, this ) );
			$( document ).on( 'change', '[data-name="load_template"] select', $.proxy( this.loadEmailTemplate, this ) );
			$( document ).on( 'change', '[data-name="add_recipient"] select', $.proxy( this.loadRecipient, this ) );
			$( document ).on( 'change keyup', '[data-name="time"] input', $.proxy( this.calculateOperationTotalTime, this ) );
			acf.addAction( 'remove', $.proxy( this.calculateOperationTotalTime, this ) );
		},
		
		setStatus() {
			$.post( PC_CPQ_AdminConfig.ajaxurl, {
				action: 'update_lead_status',
				ID: PC_CPQ_AdminConfig.ID
			});
		},
		
		setQuoteDate( e ) {
			console.log( 'quote_sent', e );
		},

		initPartFileField( field ) {
			const fileField = '<input type="file" class="js-upload-part-file" />';
			field.$el.find('.js-upload-part-file-wrapper').html( fileField );
		},
		
		getModelData: async function( e ) {
			const input = $( e.target );
			const index = input.closest('.acf-row').attr('data-id').replace(/row-/, '');
			const files = input.prop('files');
			const formData = new FormData();

			formData.append( 'file', files[0] );
			
			const response = await fetch( this.apiUrl, {
				method: 'POST',
				headers: {
					'X-API-KEY': '9f4c8e1a7b3d6c2f0e5a4b8c1d9e7f6a2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7'
				},
				body: formData
			});

			const json = await response.json();

			if ( json.isSuccess ) {
				for ( const data of json.filesInfo ) {
					this.saveFileData( files[0].name, data, index );
				}
			}
		},
		
		saveFileData( fileName, data, index ) {
			data.fileName = fileName;
			this.onFileUploaded( data, index );
		},

		onFileUploaded( data, index ) {
			let part = this.initPart( data );
			const rowID = isNaN( index ) ? index : 'row-' + index;
			
			$('.acf-field-6192c98d293ba').find('tr[data-id="' + rowID + '"] .acf-field').each( ( i, $elem ) => {
				let fieldName = $( $elem ).data('name');	
				if ( part.hasOwnProperty( fieldName ) ) {
					$( $elem ).find('input').val( part[ fieldName ] );
				}
			});
		},

		initPart( data ) {
			let part = {};
	
			// file name
			part.file_name = data.fileName;
			
			// dimensions
			part.d_x = PC_CPQ_AdminHelpers.mm_to_in( Math.abs( data.maxX - data.minX ) );
			part.d_y = PC_CPQ_AdminHelpers.mm_to_in( Math.abs( data.maxY - data.minY ) );
			part.d_z = PC_CPQ_AdminHelpers.mm_to_in( Math.abs( data.maxZ - data.minZ ) );

			// area
			part.area = PC_CPQ_AdminHelpers.mm2_to_ft2( data.area );

			// volume
			part.volume = PC_CPQ_AdminHelpers.mm3_to_in3( data.volume );

			return part;
		},
		
		loadOperation( e ) {
			const select = $( e.target ),
				  fieldContainer = select.closest('.acf-field'),
				  operationName = select.val(),
				  operationData = PC_CPQ_AdminConfig.operations.find( operation => operation.operation == operationName );
			fieldContainer.siblings('.acf-field[data-name="description"]').find('.acf-input input').val( operationData.description );
			fieldContainer.siblings('.acf-field[data-name="time"]').find('.acf-input input').val( operationData.cycle_time + ' ' + operationData.cycle_unit );
			
			this.calculateOperationTotalTime( select );
		},
		
		loadEmailTemplate( e ) {
			const select = $( e.target ),
				  templateName = select.val(),
				  templateData = PC_CPQ_AdminConfig.templates.find( template => template.name == templateName ),
				  message = templateName == 'Select a template' ? '' : templateData.template;
			
			$('[data-name="no_quote_email_message"]').find('.acf-input textarea').val( message );
			const messageField = acf.getField('field_630e1111d5f7a');
			var tinyID = messageField.$el.find("textarea").attr("id");
			var tinyInstance = tinyMCE.editors[ tinyID ];
			tinyInstance.setContent( message );
		},
		
		loadRecipient( e ) {
			const select = $( e.target ),
				  email = select.val(),
				  recipientInput = $('[data-name="recipient"]').find('.acf-input input'),
				  currentRecipients = recipientInput.val();
				  
			let recipientsArray = currentRecipients ? currentRecipients.split(',') : [];
			if ( ! recipientsArray.includes( email ) ) {
				recipientsArray.push( email );
			}
			const recipients = recipientsArray.join(',');
		  
			$('[data-name="recipient"]').find('.acf-input input').val( recipients );
			
			select.val('Select a recipient');
		},
		
		calculateOperationTotalTime( input ) {
			if ( input.target ) {
				input = $( input.target );
			}
			const timeInputs = ( input.hasClass('acf-row') ) ? input.closest('.acf-fields').find('[data-name="routing"] .acf-row').not( input ).find('[data-name="time"] input') : input.closest('.acf-fields').find('[data-name="routing"] [data-name="time"] input');
			const times = $.map( timeInputs, ( time ) => this.standardizeTimes( $( time ).val().trim() ) ).filter( ( time ) => time );
			let total = times.reduce( ( sum, a ) => sum + a, 0 );
			input.closest('.acf-fields').find('[data-name="total_operation_time"] input').val( total / 60 / 60 );
		},
		
		standardizeTimes( time ) {
			let t = parseFloat( time.replace(/\s[S|M|H]/g, '') );
			switch ( true ) {
				case time.includes('M'):
					t = t * 60;
					break;
				case time.includes('H'):
					t = t * 60 * 60;
					break;
			}
			return t;
		}
	};
	
	PC_CPQ_Admin.LeadProcesses = {
		
		shouldSync: false,
		processes: [],
		partDataFieldKey: 'field_6192c98d293ba', 
		processesFieldKey: 'field_6192cf3a293cb', 
		
		init() {
			if ( pagenow == 'lead' ) {
				if ( typeof acf != 'undefined' ) {
					acf.addAction( 'load_field/key=' + this.partDataFieldKey, $.proxy( this.initProcessField, this ) );
				}				
			}
			this.bind();
		},
		
		bind() {
			$( document ).on( 'change', '.js-sync-processes', $.proxy( this.toggleSync, this ) );
			$( document ).on( 'change', '.acf-field-6192cf3a293cb input', $.proxy( this.syncProcesses, this ) );

//			acf.addAction( 'append_field/key=field_6192cf5d293cc', $.proxy( this.addProcessStep, this ) );
//			acf.addAction( 'remove_field/key=field_6192cf5d293cc', $.proxy( this.removeProcessStep, this ) );
			acf.addAction( 'append', $.proxy( this.addProcessStep, this ) );
			acf.addAction( 'remove', $.proxy( this.removeProcessStep, this ) );
		},
		
		initProcessField( field ) {
			const syncButton = '<input class="js-sync-processes" type="checkbox" /> Sync Processes';
			field.$el.find('>.acf-label label').after( syncButton );
		},
		
		toggleSync( e ) {
			this.shouldSync = ! this.shouldSync;
			this.maybeInitProcesses();
		},
		
		maybeInitProcesses() {
			if ( ! this.getProcesses().length ) {
				const processField = acf.getFields({
					name: 'processes',
					limit: 1
				});
				if ( processField.length ) {
					processField[0].$el.find('.acf-row').not('.acf-clone').each( ( i, row ) => {
						this.addProcessStep( $( row ) );
					});
				}
			}
		},
		
		syncProcesses( e ) {
			if ( ! this.shouldSync )
				return;
			
			const processFields = acf.getFields({
				name: 'processes'
			});
			
			console.log( 'sync', this.getProcesses() );
			
//			const input = $( e.target );
//			const field = input.closest('.acf-field').attr('data-name');
			processFields.forEach( ( field ) => {
				let subFields = acf.getFields({
					parent: field.$el
				});
				let k = 0;
				subFields.forEach( ( field, i ) => {
					let value = this.getProcesses()[ k ].get( field.get('name') );
//					console.log( field.get('name'), value );
					field.val( value );
					if ( ( i + 1 ) % 6 == 0 ) {
						k++;
					}
				})
			});
		},
		
		addProcessStep( $el ) {
			if ( ! this.shouldSync )
				return;
			
			if ( ! this.isProcessesField( $el ) )
				return;
			
			let data = {};
			const subFields = acf.getFields({
				parent: $el
			});
			subFields.forEach( ( field ) => {
				data[ field.get('name') ] = field.val();
			});
			
			let newProcessStep = new PC_CPQ_Admin.processModel( { data: data } );
			this.getProcesses().push( newProcessStep );
			
			$el.attr( 'data-cid', newProcessStep.cid );
		},
		
		updateProcessStep( ) {
			
		},
		
		removeProcessStep( $el ) {
			if ( ! this.shouldSync )
				return;
			
			if ( ! this.isProcessesField( $el ) )
				return;
			
			const newProcesses = this.getProcesses().filter( ( process ) => {
				return process.cid != $el.data('cid');
			});
			
			this.setProcesses( newProcesses );
		},
		
		isProcessesField( $el ) {
			return $el.data('key') == this.processesFieldKey || $el.closest('[data-key="' + this.processesFieldKey + '"]').length > 0 ? true : false;
		},
		
		getProcesses() {
			return this.processes;
		},
		
		setProcesses( value ) {
			this.processes = value;
			return this.processes;
		}		
	};
	
	PC_CPQ_Admin.processModel = acf.Model.extend( {
		data: {
			metal: '',
			specification: '',
			min_thickness: '',
			max_thickness: '',
			unit: '',
			time: '',
		}
	} );
	
	PC_CPQ_Admin.LeadParts = {
		
		partDataFieldKey: 'field_6192c98d293ba',
		
		init() {
			if ( pagenow == 'lead' ) {
				if ( typeof acf != 'undefined' ) {
					acf.addAction( 'load_field/key=' + this.partDataFieldKey, $.proxy( this.lookupParts, this ) );
				}				
			}
		},
		
		lookupParts( field ) {
			let parts = [];
			$.each( field.$el.find('>.acf-input>.acf-repeater>.acf-table>tbody>.acf-row:not(.acf-clone)'), function( i, row ) {
				let part = {
					drawing_number: $( row ).find('.acf-field[data-name="drawing_number"] input').val(),
					revision_number: $( row ).find('.acf-field[data-name="revision_number"] input').val(),
					part_number: $( row ).find('.acf-field[data-name="part_number"] input').val(),
				}
				parts.push( part );
			});
			$.get( PC_CPQ_AdminConfig.ajaxurl, {
				action: 'lookup_parts',
				parts: parts,
				ID: PC_CPQ_AdminConfig.ID
			}).done( function( response ) {
				$.each( field.$el.find('>.acf-input>.acf-repeater>.acf-table>tbody>.acf-row:not(.acf-clone)'), function( i, row ) {
					if ( typeof response[ i ] === 'object' && response[ i ].hasOwnProperty('id') ) {
						let text = 'Duplicate part found. <a href="' + response[i].edit_url + '" targegt="_blank">' + response[i].title + '</a>';
						$( row ).find('[data-key="field_62d6dfa8a2273"] .acf-input').html( text );
					}
				});
			});
		}
	}

	return PC_CPQ_Admin;

}(PC_CPQ_Admin || {}, jQuery));