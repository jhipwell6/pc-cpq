export var PC_CPQ_Helpers = (function(PC_CPQ_Helpers, $) {
	
	/**
     * Set a height for product lists
     * @type {Object}
     */
	PC_CPQ_Helpers.getKeyByValue = ( object, value ) => {
		return Object.keys( object ).find( key => object[key] === value );
	}
	
	PC_CPQ_Helpers.generateID = () => {
		return Date.now();
	}
	
	PC_CPQ_Helpers.objectToAtts = ( obj, sep = ' ' ) => {
		let atts = [];
		Object.entries( obj ).forEach( ( [ key, value ] ) => {
			atts.push( key + '="' + value + '"' );
		});
		
		return atts.join( sep );
	}
	
	PC_CPQ_Helpers.valueByPath = ( obj, path ) => {
		if ( ! path ) {
			return false;
		}
		return path
			.replace(/\[(\w+)\]/g, '.$1')
			.replace(/^\./, '')
			.split(/\./g)
			.reduce( ( ref, key ) => key in ref && ref[key] != null ? ref[key] : ref, obj )
	},
	
	PC_CPQ_Helpers.clone = ( data ) => {
		return JSON.parse( JSON.stringify( data ) );
	},
	
	PC_CPQ_Helpers.minimize = ( obj, requiredProperties ) => {
		Object.entries( obj ).forEach( ( [ key, value ] ) => {
			if ( ! requiredProperties.includes( key ) ) {
				delete obj[ key ];
			}
		});
		
		return obj;
	},
	
	PC_CPQ_Helpers.template = ( id, data ) => {
		let template = $.trim( $( '#' + id ).html() );
		Object.entries( data ).forEach( ( [ key, value ] ) => {
			let regex = new RegExp( '{{' + key + '}}', 'ig' );
			template = template.replace( regex, value );
		});
		return template;
	},
	
	PC_CPQ_Helpers.standardizeTimes = ( time ) => {
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
	},
	
	PC_CPQ_Helpers.debounce = ( func, timeout = 300 ) => {
		let timer;
		return (...args) => {
			clearTimeout( timer );
			timer = setTimeout( () => { func.apply( this, args ); }, timeout );
		};
	}
	
	return PC_CPQ_Helpers;
}(PC_CPQ_Helpers || {}, jQuery));