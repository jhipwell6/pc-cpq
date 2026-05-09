// templateEngine.js
// A tiny ES6 template engine with helpers, conditionals, loops, and reactive binding

const cache = { };
const helpers = { };

// Register or remove helper functions
export function addHelper( name, fn ) {
	helpers[name] = fn;
}

export function removeHelper( name ) {
	delete helpers[name];
}

// Register default helpers
( function registerDefaultHelpers() {
	addHelper( 'uppercase', s => String( s || '' ).toUpperCase() );
	addHelper( 'lowercase', s => String( s || '' ).toLowerCase() );
	addHelper( 'capitalize', s => {
		s = String( s || '' );
		return s.charAt( 0 ).toUpperCase() + s.slice( 1 );
	} );
	addHelper( 'truncate', ( s, len = 100, end = '…' ) => {
		s = String( s || '' );
		return s.length > len ? s.slice( 0, len ) + end : s;
	} );
	addHelper( 'default', ( value, fallback ) =>
		value != null && value !== '' ? value : fallback
	);
	addHelper( 'formatDate', ( date, locale = 'en-US', options = {} ) => {
		const dt = date instanceof Date ? date : new Date( date );
		return new Intl.DateTimeFormat( locale, options ).format( dt );
	} );
	addHelper( 'formatCurrency', ( value, locale = 'en-US', options = {
	style: 'currency',
			currency: 'USD',
	} ) => {
		return new Intl.NumberFormat( locale, options ).format( value );
	} );
	addHelper( 'join', ( arr, sep = ', ' ) =>
		Array.isArray( arr ) ? arr.join( sep ) : ''
	);
	addHelper( 'json', obj => JSON.stringify( obj, null, 2 ) );
} )();

// Escape HTML special characters
function escapeHTML( str ) {
	const div = document.createElement( 'div' );
	div.textContent = str == null ? '' : str;
	return div.innerHTML;
}

// Compile a template string into a render function
function compile( tmpl ) {
	return data => {
		let result = tmpl;

		// Loops
		result = result.replace( /\{\{\s*#each\s+([\w.]+)\s*\}\}([\s\S]*?)\{\{\s*\/each\s*\}\}/g,
				( _, path, inner ) => {
			const arr = path.split( '.' ).reduce( ( o, k ) => ( o || { } )[k], data ) || [ ];
			const renderItem = compile( inner );
			return arr.map( item => renderItem( item ) ).join( '' );
		}
		);

		// Conditionals
		result = result.replace(
				/\{\{\s*#if\s+([\s\S]+?)\s*\}\}([\s\S]*?)(?:\{\{\s*else\s*\}\}([\s\S]*?))?\{\{\s*\/if\s*\}\}/g,
				( _, expr, truthy, falsy ) => {
			let ok = false;
			try {
				const fn = new Function( 'data', `with(data){ return (${expr}); }` );
				ok = fn( data );
				if ( Array.isArray( ok ) ) {
					ok = ok.length > 0;
				}
				ok = Boolean( ok );
			} catch ( e ) {
				console.warn( 'Invalid #if expression:', expr, e );
			}
			return ok ? truthy : ( falsy || '' );
		}
		);


		// Helpers
		result = result.replace( /\{\{\s*([\w.]+)\(([^)]*)\)\s*\}\}/g,
				( _, fnName, args ) => {
			const fn = helpers[fnName];
			if ( ! fn )
				return '';
			const vals = args.split( ',' ).map( a => {
				a = a.trim();
				// string literal
				if ( /^['"].*['"]$/.test( a ) )
					return a.slice( 1, - 1 );
				// numeric literal
				if ( /^-?\d+(?:\.\d+)?$/.test( a ) )
					return parseFloat( a );
				// data path
				return a.split( '.' ).reduce( ( o, k ) => ( o || { } )[k], data );
			} );
			return escapeHTML( fn.apply( data, vals ) );
		}
		);

		// Placeholders
		result = result.replace( /\{\{\s*([\w.]+)\s*\}\}/g,
				( _, path ) => {
			const value = path.split( '.' ).reduce( ( o, k ) => ( o || { } )[k], data );
			return escapeHTML( value );
		}
		);

		return result;
	};
}

// Render single
export function render( id, data = {} ) {
	if ( ! cache[id] ) {
		const el = document.getElementById( id );
		cache[id] = el ? compile( el.innerHTML.trim() ) : () => '';
	}
	return cache[id]( data );
}

// Render multiple
export function renderAll( id, dataArray ) {
	return dataArray.map( item => render( id, item ) ).join( '' );
}

// Reactive binding
export default {
	render,
	renderAll,
	addHelper,
	removeHelper,
	bind( id, state, container ) {
		const root = typeof container === 'string'
				? document.querySelector( container )
				: container;
		function update() {
			root.innerHTML = render( id, state );
		}
		const proxy = new Proxy( state, {
			set( target, prop, value ) {
				target[prop] = value;
				update();
				return true;
			}
		} );
		update();
		return proxy;
	}
};
