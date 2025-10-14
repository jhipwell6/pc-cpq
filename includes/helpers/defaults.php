<?php

namespace PC_CPQ\Helpers;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Defaults
{
	static public $margin;
	static public $eff;
	static public $people;
	static public $eau;
	static public $shift;
	static public $break_in;
	static public $metal_adder;

	static public function set_defaults()
	{
		if ( ! function_exists( 'get_field' ) ) {
			return null;
		}
		
		self::$margin = get_field( 'default_margin', 'option' );
		self::$eff = get_field( 'default_eff', 'option' );
		self::$people = get_field( 'default_people', 'option' );
		self::$eau = get_field( 'default_eau', 'option' );
		self::$shift = get_field( 'default_shift', 'option' );
		self::$break_in = get_field( 'default_break_in', 'option' );
		self::$metal_adder = get_field( 'default_metal_adder', 'option' );
	}

}

Defaults::set_defaults();
