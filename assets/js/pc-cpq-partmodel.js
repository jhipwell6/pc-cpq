export function createPartModel( PC_CPQ_Helpers ) {
	return {
		ID: '', // computed
		fileName: '', // computed
		area: '', // computed
		volume: '', // computed
		dX: '', // computed
		dY: '', // computed
		dZ: '', // computed
		minX: '', // computed
		minY: '', // computed
		minZ: '', // computed
		maxX: '', // computed
		maxY: '', // computed
		maxZ: '', // computed
		baseMetal: '', // user-defined
		processes: [ ], // user-defined
		drawingNumber: '', // user-defined
		revisionNumber: '', // user-defined
		partNumber: '', // user-defined
		show: false,
		showCopy: false,
		showPaste: false,
		showCopyMsg: false,
		showPasteMsg: false,
		fields: [
			{
				name: 'baseMetal',
				label: 'Base Metal',
				type: 'select',
				placeholder: 'Select a metal',
				col: '12',
				options: PC_CPQ_Config.parts.metals
			},
			{
				name: 'drawingNumber',
				label: 'Drawing Number',
				type: 'text',
				col: '4'
			},
			{
				name: 'revisionNumber',
				label: 'Revision Number',
				type: 'text',
				col: '4'
			},
			{
				name: 'partNumber',
				label: 'Part Number',
				type: 'text',
				col: '4'
			},
			{
				name: 'processes',
				label: 'Process(es)',
				type: 'array',
				col: '12',
				fields: [
					{
						name: 'metal',
						label: 'Metal Plating',
						type: 'select',
						placeholder: 'Select a metal',
						options: PC_CPQ_Config.parts.plating_metals,
						col: '5',
						parent: 'processes'
					},
					{
						name: 'specification',
						label: 'Specification',
						type: 'text',
						placeholder: 'e.g. ASTM B700',
						col: '7',
						parent: 'processes',
					},
					{
						name: 'minThickness',
						label: 'Min Thickness (&mu;in)',
						type: 'text',
						col: '3',
						parent: 'processes',
					},
					{
						name: 'maxThickness',
						label: 'Max Thickness (&mu;in)',
						type: 'text',
						col: '3',
						parent: 'processes',
					},
					{
						name: 'unit',
						label: 'Unit',
						type: 'select',
						placeholder: 'Select a unit',
						options: [
							'Standard',
							'Metric'
						],
						col: '3',
						parent: 'processes'
					}
				]
			},
		],
		requiredProperties: [
			'ID',
			'fileName',
			'area',
			'volume',
			'dX',
			'dY',
			'dZ',
			'baseMetal',
			'processes',
			'drawingNumber',
			'revisionNumber',
			'partNumber'
		],
		requiredFields: [
			'ID',
			'fileName',
			'baseMetal',
			'processes',
			'drawingNumber',
			'revisionNumber',
			'partNumber'
		],

		init() {
			this.setPartID();
			this.addItem( 'processes' );
			this.setDimensions();
		},

		setPartID() {
			this.ID = PC_CPQ_Helpers.generateID();
		},

		setDimensions() {
			this.dX = Math.abs( this.maxX - this.minX );
			this.dY = Math.abs( this.maxY - this.minY );
			this.dZ = Math.abs( this.maxZ - this.minZ );
		},

		resetMessages() {
			this.showCopyMsg = false;
			this.showPasteMsg = false;
		},

		addItem( field ) {
			if ( ! this.hasOwnProperty( field ))
				return false;

			let newItems = [ ];
			let item = { };
			switch ( field ) {
				case 'processes':
					let current = JSON.parse( JSON.stringify( this[ field ] ) );
					item = [ {
							metal: '',
							specification: '',
							minThickness: '',
							maxThickness: '',
							unit: 'Standard',
						} ];
					newItems = current.concat( item );
					break;
			}

			if ( newItems.length ) {
				this[ field ] = newItems;
			}
		},

		removeItem( field, index ) {
			if ( ! this.hasOwnProperty( field ))
				return false;

			this[ field ].splice( index, 1 );
		},

		hasRequiredData() {
			let valid = true;
			this.requiredFields.every( ( prop ) => {
				if ( this[ prop ] == '' ) {
					valid = false;
					return false;
				}
				return true;
			} );
			return valid;
		},

		toggleShow() {
			this.show = ! this.show;
		},

		toggleCopyButton() {
			this.showCopy = ! this.showCopy;
		},

		togglePasteButton() {
			this.showPaste = ! this.showPaste;
		},

		toggleCopiedMessage() {
			this.showCopyMsg = ! this.showCopyMsg;
		},

		togglePastedMessage() {
			this.showPasteMsg = ! this.showPasteMsg;
		},

		toggleInputs( field, value, index ) {
			if ( ! this.hasOwnProperty( field ))
				return false;

			let newItems = [ ];
			let current = JSON.parse( JSON.stringify( this[ field ] ) );
			switch ( value ) {
				case 'MASKING':
				case 'VIBRATORY FINISH':
					newItems = current.map( ( subfield, i ) => {
						if ( i == index ) {
							subfield.minThickness = 'disabled';
							subfield.maxThickness = 'disabled';
						}
						return subfield;
					} );
					break;
				case 'BAKING':
					newItems = current.map( ( subfield, i ) => {
						if ( i == index ) {
							subfield.minThickness = 'placeholder:time in mins';
							subfield.maxThickness = 'disabled';
						}
						return subfield;
					} );
					break;
				case 'IMPREG':
					newItems = current.map( ( subfield, i ) => {
						if ( i == index ) {
							subfield.minThickness = 'placeholder:# of cycles (e.g. 2)';
							subfield.maxThickness = 'disabled';
						}
						return subfield;
					} );
					break;
				default:
					newItems = current.map( ( subfield, i ) => {
						if ( i == index ) {
							subfield.minThickness = this[field][i]['minThickness'];
							subfield.maxThickness = this[field][i]['maxThickness'];
						}
						return subfield;
					} );
					break;
			}

			if ( newItems.length ) {
				this[ field ] = newItems;
			}
		},

		renderFields() {
			const show = this.show ? ' show' : '';
			const showCopy = this.showCopy ? 'd-block' : 'd-none';
			const showCopyMsg = this.showCopyMsg ? 'd-block' : 'd-none';
			const showPaste = this.showPaste ? 'd-block' : 'd-none';
			const showPasteMsg = this.showPasteMsg ? 'd-block' : 'd-none';
			let buttons = PC_CPQ_Helpers.template( 'copy-message', { ID: this.ID, displayClass: showCopyMsg } );
			buttons += PC_CPQ_Helpers.template( 'paste-message', { ID: this.ID, displayClass: showPasteMsg } );
			buttons += PC_CPQ_Helpers.template( 'copy-button', { ID: this.ID, displayClass: showCopy } );
			buttons += PC_CPQ_Helpers.template( 'paste-button', { ID: this.ID, displayClass: showPaste } );
			buttons += PC_CPQ_Helpers.template( 'collapse-button', { ID: this.ID } );

			let fieldOutput = '';
			this.fields.forEach( ( field ) => {
				const fieldData = {
					col: field.hasOwnProperty( 'col' ) ? field.col : '6',
					labelClass: field.type == 'array' ? ' class="label-lg"' : '',
					label: field.label,
					input: this.renderInput( field )
				}

				fieldOutput += PC_CPQ_Helpers.template( 'part-field', fieldData );
			} );

			const templateData = {
				ID: this.ID,
				fileName: this.fileName,
				show: show,
				buttons: buttons,
				fields: fieldOutput
			}

			return PC_CPQ_Helpers.template( 'part', templateData );
		},

		renderInput( field ) {
			const fieldId = this.ID + '_' + field.name;
			const fieldName = field.name;
			const fieldValue = this.hasOwnProperty( field.name ) ? this[ field.name ] : '';
			let output = '';

			switch ( field.type ) {
				// standard text inputs
				case 'text':
				case 'email':
				case 'number':
					output += this.renderTextInput( field, fieldId, fieldName, fieldValue );
					break;

					// select inputs
				case 'select':
					output += this.renderSelectInput( field, fieldId, fieldName, fieldValue );
					break;

					// repeating inputs
				case 'array':
					if ( this.hasOwnProperty( field.name ) && Array.isArray( this[ field.name ] ) ) {
						let suboutput = '';
						this[ field.name ].forEach( ( f, index ) => {
							let subfields = field.fields.map( ( subfield ) => {
								let col = subfield.hasOwnProperty( 'col' ) ? subfield.col : '6';
								return '<div class="col-' + col + '">' +
										'<label>' + subfield.label + ' *</label>' +
										this.renderSubInput( subfield, index ) +
										'</div>';
							} );

							suboutput += '<div class="row align-items-end">';
							suboutput += subfields.join( '' );
							suboutput += this.renderItemActions( field, index );
							suboutput += '</div>';

							output = suboutput;
						} )
					}
					break;
			}

			return output;
		},

		renderSubInput( field, index ) {
			const fieldName = field.parent + '_' + index + '_' + field.name;
			const fieldId = this.ID + '_' + fieldName;
			const fieldValue = this.hasOwnProperty( field.parent ) ? this[ field.parent ][ index ][ field.name ] : '';
			let output = '';

			switch ( field.type ) {
				// standard text inputs
				case 'text':
				case 'email':
				case 'number':
					output = this.renderTextInput( field, fieldId, fieldName, fieldValue, index );
					break;

					// select inputs
				case 'select':
					output = this.renderSelectInput( field, fieldId, fieldName, fieldValue, index );
					break;
			}

			return output;
		},

		renderTextInput( field, fieldId, fieldName, fieldValue, index = false ) {
			let value = fieldValue;
			let placeholder = '';

			if ( fieldValue.includes( 'disabled' ) ) {
				value = '';
			}
			if ( fieldValue.includes( 'placeholder' ) ) {
				value = '';
				placeholder = fieldValue.replace( /placeholder\:/gi, '' );
			}
			if ( field.hasOwnProperty( 'placeholder' ) ) {
				placeholder = field.placeholder;
			}

			let attData = {
				type: field.type,
				name: this.ID + '_' + fieldName,
				value: value,
				class: 'form-control mb-3',
				id: fieldId,
				placeholder: placeholder,
				'data-name': fieldName,
				'data-part': this.ID
			};

			// set parent if it exists
			if ( field.hasOwnProperty( 'parent' ) ) {
				attData['data-parent'] = field.parent;
			}

			// set disabled if true
			if ( fieldValue == 'disabled' ) {
				attData['disabled'] = true;
			}

			if ( index !== false ) {
				attData['data-index'] = index;
			}
			const atts = PC_CPQ_Helpers.objectToAtts( attData );

			return PC_CPQ_Helpers.template( 'text-input', { atts: atts } );
		},

		renderSelectInput( field, fieldId, fieldName, fieldValue, index = false ) {
			// set options
			let options = field.options.map( ( option ) => {
				const optionData = {
					value: option,
					label: option,
					selected: fieldValue == option ? ' selected' : ''
				}
				return PC_CPQ_Helpers.template( 'select-input-option', optionData );
			} );

			// set placeholder option if it exists
			if ( field.hasOwnProperty( 'placeholder' ) ) {
				const placeholder = PC_CPQ_Helpers.template( 'select-input-option', {
					value: field.placeholder,
					label: field.placeholder
				} );
				options.unshift( placeholder );
			}

			// init attributes
			let attData = {
				name: this.ID + '_' + fieldName,
				value: fieldValue == 'disabled' ? '' : fieldValue,
				class: 'form-control mb-3',
				id: fieldId,
				'data-name': fieldName,
				'data-part': this.ID
			};

			// set parent if it exists
			if ( field.hasOwnProperty( 'parent' ) ) {
				attData['data-parent'] = field.parent;
			}

			// set disabled if true
			if ( fieldValue == 'disabled' ) {
				attData['disabled'] = true;
			}

			if ( index !== false ) {
				attData['data-index'] = index;
			}

			// convert atts to a string
			const atts = PC_CPQ_Helpers.objectToAtts( attData );

			// init select data
			const selectData = {
				atts: atts,
				options: options.join( '' )
			}

			return PC_CPQ_Helpers.template( 'select-input', selectData );
		},

		renderItemActions( field, index ) {
			const addAtts = PC_CPQ_Helpers.objectToAtts( {
				type: 'button',
				class: 'btn btn-outline-dark text-monospace mb-3',
				'data-part': this.ID,
				'data-field': field.name,
				'data-index': index,
				'data-action': 'add'
			} );
			const addButtonData = {
				atts: addAtts,
				label: '+'
			}
			let buttons = PC_CPQ_Helpers.template( 'action-button', addButtonData );

			if ( index > 0 ) {
				const removeAtts = PC_CPQ_Helpers.objectToAtts( {
					type: 'button',
					class: 'btn btn-outline-dark text-monospace mb-3',
					'data-part': this.ID,
					'data-field': field.name,
					'data-index': index,
					'data-action': 'remove'
				} );
				const removeButtonData = {
					atts: removeAtts,
					label: '-'
				}
				buttons += PC_CPQ_Helpers.template( 'action-button', removeButtonData );
			}

			return PC_CPQ_Helpers.template( 'action-buttons', { buttons: buttons } );
		},

		setProp( key, value ) {
			if ( this.hasOwnProperty( key ) ) {
				this[ key ] = value;
			} else if ( key.includes( '_' ) ) {
				const path = key.split( '_' );
				this[ path[0] ][ path[1] ][ path[2] ] = value;
			}
		},

		snapshot() {
			return PC_CPQ_Helpers.clone( this );
		}
	};
}