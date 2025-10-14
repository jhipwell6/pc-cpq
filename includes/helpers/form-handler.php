<?php

namespace PC_CPQ\Helpers;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Form_Handler
{

	static public function pre_validate_form( $nonce_name, $action, $form_data )
	{
		if ( self::filter_input( 'action' ) !== $action ) {
			$error = new \WP_Error( 'invalid_action', 'Something went wrong. Invalid form action.' );
			wp_send_json_error( $error, 500 );
			die();
		}

		if ( ! self::is_nonce_verified( $nonce_name, $action, $form_data ) ) {
			$error = new \WP_Error( 'invalid_nonce', 'Unauthorized. Invalid nonce.' );
			wp_send_json_error( $error, 403 );
			die();
		}
	}

	static public function get_form_data( $post_var, $skip_sanitization = false )
	{
		$form_data = self::parse( self::filter_input( $post_var ) );
		if ( is_null( $form_data ) ) {
			$form_data = self::filter_input( $post_var );
		}
		if ( ! $skip_sanitization ) {
			$sanitized_data = self::sanitize_form_data( $form_data );
			return self::merge_array_data( $sanitized_data );
		}
		return self::merge_array_data( $form_data );
	}

	static public function get_file_data( $post_var )
	{
		return isset( $_FILES[$post_var] ) ? $_FILES[$post_var] : null;
	}

	static public function merge_array_data( $data )
	{
		$new_data = array();
		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				data_set( $new_data, $key, $value, '/' );
			}
		}
		return $new_data;
	}

	static public function validate_form_data( $form_data )
	{
		if ( empty( $form_data ) ) {
			$error = new \WP_Error( 'invalid_form', 'Something went wrong. Invalid form data.' );
			wp_send_json_error( $error, 500 );
			die();
		}
	}

	static public function is_nonce_verified( $nonce_name, $action, $form_data )
	{
		$nonce_field = $form_data[$nonce_name];
		return isset( $nonce_field ) && wp_verify_nonce( $nonce_field, $action );
	}

	static public function filter_input( $key )
	{
		return filter_input( INPUT_POST, $key, FILTER_UNSAFE_RAW );
	}

	static public function sanitize_form_data( $form_data )
	{
		return array_map( 'sanitize_text_field', $form_data );
	}

	static public function sanitize_input( $input )
	{
		return sanitize_text_field( $input );
	}

	static public function parse( string $str, $urlEncoding = true ): array
	{
		$result = [];

		if ( $str === '' ) {
			return $result;
		}

		if ( $urlEncoding === true ) {
			$decoder = function ( $value ) {
				return rawurldecode( str_replace( '+', ' ', (string) $value ) );
			};
		} elseif ( $urlEncoding === PHP_QUERY_RFC3986 ) {
			$decoder = 'rawurldecode';
		} elseif ( $urlEncoding === PHP_QUERY_RFC1738 ) {
			$decoder = 'urldecode';
		} else {
			$decoder = function ( $str ) {
				return $str;
			};
		}

		foreach ( explode( '&', $str ) as $kvp ) {
			$parts = explode( '=', $kvp, 2 );
			$key = $decoder( $parts[0] );
			$value = isset( $parts[1] ) ? $decoder( $parts[1] ) : null;
			if ( ! array_key_exists( $key, $result ) ) {
				$result[$key] = $value;
			} else {
				if ( ! is_array( $result[$key] ) ) {
					$result[$key] = [ $result[$key] ];
				}
				$result[$key][] = $value;
			}
		}

		return $result;
	}

}
