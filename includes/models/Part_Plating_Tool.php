<?php

namespace PC_CPQ\Models;

use \PC_CPQ\Helpers\Constants;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Plating_Tool
{
	protected $Part;
	protected $type;
	protected $raw_data;
	protected $actual_count;
	protected $piece_limit;
	protected $actual_weight;
	protected $pieces_per_load;
	protected $actual_piece_count;
	protected $size_limit;
	protected $weight_limit;
	protected $ft2_load;
	protected $piece_count;

	/**
	 * Initializes variables.
	 * @return void
	 */
	public function __construct( $type, $raw_data, $Part )
	{
		$this->set_type( $type );
		$this->set_raw_data( $raw_data );
		$this->set_Part( $Part );

		// fill properties
		$this->get_props();
		return $this;
	}

	/*
	 * Getters
	 */

	public function get_Part()
	{
		return $this->Part;
	}

	public function get_type()
	{
		return $this->type;
	}

	public function get_size_limit()
	{
		return floatval( $this->get_prop( 'size_limit' ) );
	}

	public function get_weight_limit()
	{
		return floatval( $this->get_prop( 'weight_limit' ) );
	}

	public function get_ft2_load()
	{
		return floatval( $this->get_prop( 'ft2_load' ) );
	}

	public function get_piece_count()
	{
		return floatval( $this->get_prop( 'piece_count' ) );
	}

	/*
	 * Getters (computed)
	 */

	public function get_actual_count()
	{
		if ( null === $this->actual_count ) {
			$this->actual_count = ( $this->get_size_limit() / $this->get_Part()->get_volume() );
		}
		return $this->actual_count;
	}

	public function get_piece_limit()
	{
		if ( null === $this->piece_limit ) {
			$this->piece_limit = ( $this->get_weight_limit() / $this->get_Part()->get_weight() );
		}
		return $this->piece_limit;
	}

	public function get_actual_weight()
	{
		if ( null === $this->actual_weight ) {
			$this->actual_weight = ( $this->get_size_limit() / $this->get_Part()->get_volume() ) * $this->get_Part()->get_weight();
		}
		return $this->actual_weight;
	}

	public function get_pieces_per_load() // error??
	{
		if ( null === $this->pieces_per_load ) {
			$this->pieces_per_load = ( $this->get_ft2_load() / $this->get_Part()->get_area() );
		}
		return $this->pieces_per_load;
	}

	public function get_actual_piece_count()
	{
		if ( null === $this->actual_piece_count ) {
			switch ( $this->get_type() ) {
				case 'barrel':
				case 'Barrel':
					$this->actual_piece_count = $this->get_pieces_per_load() <= $this->get_piece_limit() ? $this->get_pieces_per_load() : $this->get_piece_limit();
					break;
				case 'rack':
				case 'Rack':
					$this->actual_piece_count = $this->calculate_rack_actual_piece_count();
					break;
				default:
					$this->actual_piece_count = 1;
			}
		}
		return $this->actual_piece_count;
	}

	protected function get_meta( $prop )
	{
		if ( isset( $this->raw_data[$prop] ) ) {
			return $this->raw_data[$prop];
		}
		return false;
	}

	protected function get_raw_data( $prop = null )
	{
		return ( $prop && isset( $this->raw_data[$prop] ) ) ? $this->raw_data[$prop] : $this->raw_data;
	}

	/*
	 * Setters
	 */

	private function set_Part( $Part )
	{
		$this->Part = $Part;
		return $this->Part;
	}

	private function set_type( $type )
	{
		$this->type = $type;
		return $this->type;
	}

	private function set_raw_data( $raw_data )
	{
		$this->raw_data = $raw_data;
		return $this->raw_data;
	}

	/*
	 * Helpers
	 */
	
	public function get_input_value()
	{
		return $this->get_type();
	}

	protected function get_prop( $prop )
	{
		if ( ! $this->has_prop( $prop ) )
			return false;

		if ( null === $this->{$prop} || empty( $this->{$prop} ) ) {
			$this->{$prop} = $this->get_meta( $prop );
		}
		return $this->{$prop};
	}

	protected function get_props()
	{
		foreach ( get_object_vars( $this ) as $prop => $value ) {
			$getter = "get_{$prop}";
			if ( is_callable( array( $this, $getter ) ) ) {
				$this->{$getter}();
			}
		}
	}

	public function has_prop( $prop )
	{
		return property_exists( $this, $prop );
	}

	private function calculate_rack_actual_piece_count()
	{
		switch ( $this->get_Part()->get_tool() ) {
			case '10 CU.FT. BASKET':
			case '10 CU.FT. Basket':
				$actual_piece_count = ( $this->get_size_limit() / $this->get_Part()->get_volume() );
				break;
			case 'CHEM FILM':
			case 'Chem Film':
				$actual_piece_count = ( $this->get_size_limit() / ( $this->get_Part()->get_d_y() * $this->get_Part()->get_d_z() ) * 0.35 );
				break;
			case 'EN TANK SPECIAL':
			case 'EN Tank Special':
				$actual_piece_count = ( $this->get_size_limit() / $this->get_Part()->get_volume() );
				break;
			case 'Ideal Rack':
			case 'Ideal Rack Double Sided':
				$line = Constants::get_row( $this->get_Part()->get_plating_line(), Constants::$lines );
				$size_limit = $line['rack_ld_max_in2'];
				$fheight = $this->get_Part()->get_d_y() < 1.999998 ? ( ( 3 * $this->get_Part()->get_d_y() ) / 8 ) + 0.25 + ( $this->get_Part()->get_d_z() / 4 ) : 1 + ( $this->get_Part()->get_d_z() / 4 );
				$fwidth = $this->get_Part()->get_d_x() < 1.999998 ? ( ( 3 * $this->get_Part()->get_d_x() ) / 8 ) + 0.25 + ( $this->get_Part()->get_d_z() / 4 ) : 1 + ( $this->get_Part()->get_d_z() / 4 );
				$actual_piece_count = ( $size_limit / ( ( $this->get_Part()->get_d_y() + $fheight + $fheight ) * ( $this->get_Part()->get_d_x() + $fwidth + $fwidth ) ) );
				if ( $this->get_Part()->get_tool() == 'Ideal Rack Double Sided' ) {
					$actual_piece_count = $actual_piece_count * 2;
				}
				break;
			default:
				$actual_piece_count = $this->get_piece_count();
		}

		return $actual_piece_count;
	}
	
	public function to_array( $exclude = array() )
	{
		return get_object_vars( $this );
	}

}
