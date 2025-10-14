<?php

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed   $target
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
if ( ! function_exists( 'data_get' ) ) {

	function data_get( $target, $key, $default = null )
	{
		if ( is_null( $key ) )
			return $target;
		foreach ( explode( '.', $key ) as $segment ) {
			if ( is_array( $target ) ) {
				if ( ! array_key_exists( $segment, $target ) ) {
					return $default;
				}
				$target = $target[$segment];
			} elseif ( $target instanceof ArrayAccess ) {
				if ( ! isset( $target[$segment] ) ) {
					return $default;
				}
				$target = $target[$segment];
			} elseif ( is_object( $target ) ) {
				if ( ! isset( $target->{$segment} ) ) {
					return $default;
				}
				$target = $target->{$segment};
			} else {
				return $default;
			}
		}
		return $target;
	}

}

if ( ! function_exists( 'data_set' ) ) {

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function data_set( &$array, $key, $value, $separator = '.' )
	{
		if ( is_null( $key ) )
			return $array = $value;

		$keys = explode( $separator, $key );

		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $array[$key] ) || ! is_array( $array[$key] ) ) {
				$array[$key] = array();
			}

			$array = & $array[$key];
		}

		$array[array_shift( $keys )] = $value;

		return $array;
	}

}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable  $callback
 * @param  mixed  $default
 * @return mixed
 */
if ( ! function_exists( 'array_first' ) ) {

	function array_first( $array, $callback = null, $default = null )
	{
		if ( is_null( $callback ) ) {
			return count( $array ) > 0 ? reset( $array ) : null;
		}
		foreach ( $array as $key => $value ) {
			if ( call_user_func( $callback, $key, $value ) )
				return $value;
		}
		return value( $default );
	}

}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
if ( ! function_exists( 'value' ) ) {

	function value( $value )
	{
		return $value instanceof Closure ? $value() : $value;
	}

}

if ( ! function_exists( 'prepare_in' ) ) {

	function prepare_in( $values )
	{
		return implode( ',', array_map( function ( $value ) {
				global $wpdb;
				// Use the official prepare() function to sanitize the value.
				return $wpdb->prepare( '%s', $value );
			}, $values ) );
	}

}

if ( ! function_exists( 'get_meta_values' ) ) {

	function get_meta_values( $key = '', $args = array() )
	{
		if ( empty( $key ) )
			return;

		$defaults = array(
			'post_type' => 'post',
			'post_status' => array( 'publish' ),
		);
		$args = wp_parse_args( $args, $defaults );

		global $wpdb;
		$sql = "
			SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s 
			AND p.post_status IN (%s) 
			AND p.post_type = %s
		";
		$result = $wpdb->get_results( sprintf( $sql, $wpdb->prepare( "%s", $key ), prepare_in( $args['post_status'] ), $wpdb->prepare( "%s", $args['post_type'] ) ) );

		$output = array();
		if ( ! empty( $result ) ) {
			foreach ( $result as $row ) {
				$output[$row->post_id] = $row->meta_value;
			}
		}

		return $output;
	}

}

if ( ! function_exists( 'to_currency' ) ) {
	function to_currency( $value, $digits = 2 )
	{
		$formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
		$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, $digits );
		return $formatter->formatCurrency( $value, 'USD' );
	}
}