<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Operation extends Repeater_Model
{
	protected $operation;
	protected $description;
	protected $setup_time;
	protected $setup_unit;
	protected $cycle_time;
	protected $cycle_unit;
	protected $efficiency;
	protected $type;
	protected $base_metal;
	protected $material;
	protected $metal;

	public function get_operation()
	{
		return $this->get_prop( 'operation' );
	}
	
	public function get_description()
	{
		return $this->get_prop( 'description' );
	}
	
	public function get_setup_time()
	{
		return $this->get_prop( 'setup_time' );
	}
	
	public function get_setup_unit()
	{
		return $this->get_prop( 'setup_unit' );
	}
	
	public function get_cycle_time()
	{
		return $this->get_prop( 'cycle_time' );
	}
	
	public function get_cycle_unit()
	{
		return $this->get_prop( 'cycle_unit' );
	}
	
	public function get_efficiency()
	{
		return $this->get_prop( 'efficiency' );
	}
	
	public function get_type()
	{
		return $this->get_prop( 'type' );
	}
	
	public function get_base_metal()
	{
		return (array) $this->get_prop( 'base_metal' );
	}
	
	public function get_base_metal_list()
	{
		if ( is_string( $this->get_base_metal() ) ) {
			return $this->get_base_metal();
		}
		if ( is_array( $this->get_base_metal() ) && ! empty( $this->get_base_metal() ) ) {
			return implode( ', ', $this->get_base_metal() );
		}
		return '';
	}
	
	public function get_material()
	{
		return $this->get_prop( 'material' );
	}
	
	public function get_truncated_description()
	{
		if ( null === $this->truncated_description ) {
			$this->truncated_description = wp_trim_words( $this->get_description(), '15' );
		}
		return $this->truncated_description;
	}
	
	public function get_metal()
	{
		if ( null === $this->metal ) {
			$this->metal = false;
			switch ( $this->get_type() ) {
				case 'Prep':
					$this->metal = $this->get_base_metal();
					break;
				case 'Plating':
				case 'Post':
					$this->metal = $this->get_material();
					break;
			}
		}
		return $this->metal;
	}
}