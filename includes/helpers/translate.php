<?php

namespace PC_CPQ\Helpers;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Translate
{
	
	static public function field_keys( $data )
	{
		$key_map = array(
			'field_6192cf5d293cc' => 'metal',
			'field_61955b1f0e88f' => 'min_thickness',
			'field_6192cf68293cd' => 'max_thickness',
			'field_6192cf73293ce' => 'time',
			'field_621d05885eb9e' => 'specification',
			'field_621fba2f415f4' => 'unit',
		);

		$arr = array();

		foreach ( $data as $key => $value ) {
			$arr[$key_map[$key]] = $value;
		}

		return $arr;
	}

	static public function dimension_vars( $part )
	{
		$dimension_map = array(
			'radius' => 'r',
			'radius_2' => 'R',
			'length' => 'l',
			'width' => 'w',
			'height' => 'h',
			'side' => 'a',
			'side_2' => 'b',
			'side_3' => 'c'
		);

		$dimensions = array();

		foreach ( $part as $key => $value ) {
			if ( array_key_exists( $key, $dimension_map ) )
				$dimensions[$dimension_map[$key]] = $value;
		}

		return $dimensions;
	}

}
