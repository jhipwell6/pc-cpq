<?php

namespace PC_CPQ\Helpers;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Geometry
{
	/**
	 * @method calculate_weight
	 * @params volume (float), density (float)
	 * @formula volume * density
	 */
	static public function calculate_weight( $volume, $density )
	{
		$weight = floatval( $volume ) * floatval( $density );
		return $weight;
	}

	/**
	 * @method calculate_surface_area
	 * @params shape (str), inputs (arr)
	 * @formula multiple
	 * 	Cone
	 * 	Cube
	 * 	Cylinder
	 * 	Frustrum
	 * 	Half Torus
	 * 	Rectangular Prism
	 * 	Sphere
	 * 	Torus
	 * 	Triangular Prism
	 * 	Tube/Ring
	 * 	***Gear-Head Solid Rivet***    todo
	 * 	Rectangular Can
	 * 	***Cap Pin***                  todo
	 * 	***Hexagonal Prism***          todo
	 */
	static public function calculate_surface_area( $shape, $inputs = array() )
	{
		if ( isset( $inputs ) ) {
			extract( $inputs );
		} else {
			return false;
		}

		$surface_area = 0;

		switch ( $shape ) {

			case 'cone': // s = π × r² + π × r × h
				$surface_area = pi() * pow( $r, 2 ) + pi() * $r * $h;
				break;

			case 'cube': // s = 6 × a × a
				$surface_area = 6 * $a * $a;
				break;

			case 'cylinder': // s = ( 2 × π × r² ) + 2 × π × r × h
				$surface_area = ( 2 * pi() * pow( $r, 2 ) ) + ( 2 * pi() * $r * $h );
				break;

			case 'frustrum': // s = π × ( R + r ) × √( ( R - r )² + h² )
				$surface_area = pi() * ( $R + $r ) * sqrt( pow( ( $R - $r ), 2 ) + pow( $h, 2 ) );
				break;

			case 'half_torus': // s = ( ( 2 × π × R ) × ( 2 × π × r ) ) / 2
				$surface_area = ( ( 2 * pi() * $R ) * ( 2 * pi() * $r ) ) / 2;
				break;

			case 'rectangular_prism': // s = 2 × ( ( w × l ) + ( h × l ) + ( h × w ) )
				$surface_area = 2 * ( ( $w * $l ) + ( $h * $l ) + ( $h * $w ) );
				break;

			case 'rectangular_can': // s = ( 2 × w × l ) + ( 4 × h × l ) + ( 4 × h × w )
				$surface_area = ( 2 * $w * $l ) + ( 4 * $h * $l ) + ( 4 * $h * $w );
				break;

			case 'sphere': // s = 4 × π × r²
				$surface_area = 4 * pi() * pow( $r, 2 );
				break;

			case 'torus': // s = ( 2 × π × R ) × ( 2 × π × r )
				$surface_area = ( 2 * pi() * $R ) * ( 2 * pi() * $r );
				break;

			case 'triangular_prism':  // s = ( a × b × c ) + ( ( a + b + c ) × h )
				$surface_area = ( $a * $b * $h ) + ( ( $a + $b + $c ) * $h );
				break;

			case 'tube': // s = ( 2 × π × ( R² - r² ) ) + ( 2 × π × h × ( R + r ) )
			case 'ring':
				$surface_area = ( 2 * pi() * ( pow( $R, 2 ) - pow( $r, 2 ) ) ) + ( 2 * pi() * $h * ( $R + $r ) );
				break;
		}

		return $surface_area;
	}

	/**
	 * @method calculate_volume
	 * @params shape (str), inputs (arr)
	 * @formula multiple
	 */
	static public function calculate_volume( $shape, $inputs = array() )
	{
		if ( isset( $inputs ) ) {
			extract( $inputs );
		} else {
			return false;
		}

		$volume = 0;

		switch ( $shape ) {

			case 'cone': // v = (1/3) × π × r² × h
				$volume = (1 / 3) * pi() * pow( $r, 2 ) * $h;
				break;

			case 'cube': // v = a³
				$volume = pow( $a, 3 );
				break;

			case 'cylinder': // v = π × r² × h
				$volume = pi() * pow( $r, 2 ) * $h;
				break;

			case 'frustrum': // v = (1/3) × π × h × (R² + r² + (R × r))
				$volume = (1 / 3) * pi() * $h * ( pow( $R, 2 ) + pow( $r, 2 ) + ( $R * $r ) );
				break;

			case 'half_torus': // v = π² × R × r²
				$volume = pi() * $R * pow( $r, 2 );
				break;

			case 'rectangular_prism': // v = l × w × h
			case 'rectangular_can':
				$volume = $l * $w * $h;
				break;

			case 'sphere': // v = (4/3) × π × r³
				$volume = (4 / 3) * pi() * pow( $r, 3 );
				break;

			case 'torus': // v = 2 × π² × R × r²
				$volume = 2 * pi() * $R * pow( $r, 2 );
				break;

			case 'triangular_prism': // v = (1/4) × h × √( (a + b + c) × (-a + b + c) × (a - b + c) × (a + b - c) )
				$volume = (1 / 4) * $h * sqrt( ( $a + $b + $c ) * ( -$a + $b + $c ) * ( $a - $b + $c ) * ( $a + $b - $c ) );
				break;

			case 'tube': // v = ( π × h ) × ( R² - r² )
			case 'ring':
				$volume = ( pi() * $h ) * ( pow( $R, 2 ) - pow( $r, 2 ) );
				break;
		}

		return $volume;
	}

	static public function mm_to_in( $mm )
	{
		return ( floatval( $mm ) / 25.4 );
	}

	static public function mm3_to_in3( $mm3 )
	{
		return ( floatval( $mm3 ) / 16387.064001 );
	}

	static public function mm2_to_ft2( $mm2 )
	{
		return ( floatval( $mm2 ) / 92903.04 );
	}

	static public function mm_to_microinch( $mm )
	{
		$inches = self::mm_to_in( $mm );
		return ( floatval( $inches ) * 1000 );
	}

}
