<?php

namespace PC_CPQ\Helpers;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Constants
{
	static public $starting_quote_number;
	static public $hourly_rate;
	static public $quote_expires_after;
	static public $follow_up_after;
	static public $metals = array();
	static public $plating_metals = array();
	static public $available_plating_metals = array();
	static public $lines = array();
	static public $barrels = array();
	static public $racks = array();
	static public $recipes = array();
	static public $operations = array();
	static public $email_templates = array();

	static public function set_constants()
	{
		if ( ! function_exists( 'get_field' ) ) {
			return null;
		}
		
		self::$starting_quote_number = get_field( 'starting_quote_number', 'option' );
		self::$hourly_rate = (string) get_field( 'hourly_rate', 'option' );
		self::$quote_expires_after = get_field( 'quote_expires_after', 'option' );
		self::$follow_up_after = get_field( 'follow_up_after', 'option' );
		self::$metals = get_field( 'metals', 'option' );
		self::$plating_metals = get_field( 'plating_metals', 'option' );
		self::$lines = get_field( 'lines', 'option' );
		self::$barrels = get_field( 'barrels', 'option' );
		self::$racks = get_field( 'racks', 'option' );
		self::$recipes = get_field( 'recipes', 'option' );
		self::$operations = get_field( 'operations', 'option' );
		self::$email_templates = get_field( 'email_templates', 'option' );

		self::$available_plating_metals = self::filter_rows( array( 'hide' => 0 ), self::$plating_metals );
	}

	static public function get_metal_value( $metal, $property )
	{
		return self::get_value( $metal, $property, self::$metals );
	}

	static public function get_plating_metal_value( $plating_metal, $property )
	{
		return self::get_value( $plating_metal, $property, self::$plating_metals );
	}

	static public function get_line_value( $line, $property )
	{
		return self::get_value( $line, $property, self::$lines );
	}

	static public function get_barrel_value( $barrel, $property )
	{
		return self::get_value( $barrel, $property, self::$barrels );
	}

	static public function get_rack_value( $rack, $property )
	{
		return self::get_value( $rack, $property, self::$racks );
	}

	static public function get_recipe_value( $recipe, $property )
	{
		return self::get_value( $recipe, $property, self::$recipes );
	}

	static protected function get_value( $val, $property, $arr )
	{
		$value = null;

		$rows = wp_list_filter( $arr, array( 'name' => $val ) );
		if ( ! empty( $rows ) ) {
			$row = reset( $rows );
			$value = $row[$property];
		}

		return $value;
	}

	static public function get_row( $val, $arr )
	{
		$row = null;

		$rows = wp_list_filter( $arr, array( 'name' => $val ) );
		if ( ! empty( $rows ) ) {
			$row = reset( $rows );
		}

		return $row;
	}

	static public function get_col( $col, $arr )
	{
		return wp_list_pluck( $arr, $col );
	}

	static public function filter_rows( $args, $arr, $operator = 'AND' )
	{
		return array_values( wp_list_filter( $arr, $args, $operator ) );
	}

}

Constants::set_constants();
