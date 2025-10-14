<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;
use \PC_CPQ\Helpers\Constants;
use \PC_CPQ\Helpers\Geometry;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Process extends Repeater_Model
{
	protected $time;
	protected $metal;
	protected $specification;
	protected $min_thickness;
	protected $max_thickness;
	protected $unit;
	// computed
	private $average_thickness;
	private $metal_deposit_rate;
	private $metal_density;
	private $metal_cost;
	private $min_lot_charge;
	private $description;
	private $unit_type;
	private $unit_visible;

	/*
	 * Getters
	 */

	public function get_time()
	{
		if ( null === $this->time ) {
			$this->time = $this->get_metal_deposit_rate() > 0 ? ceil( $this->get_average_thickness() / $this->get_metal_deposit_rate() ) : 0;
		}
		return $this->time;
	}

	public function get_metal()
	{
		return $this->get_prop( 'metal' );
	}

	public function get_specification()
	{
		return $this->get_prop( 'specification' );
	}

	public function get_min_thickness()
	{
		return $this->get_prop( 'min_thickness' );
	}

	public function get_max_thickness()
	{
		return $this->get_prop( 'max_thickness' );
	}

	public function get_unit()
	{
		return $this->get_prop( 'unit' );
	}

	/*
	 * Getters (computed)
	 */

	public function get_average_thickness()
	{
		if ( null === $this->average_thickness ) {
			$values = array();
			if ( $this->get_min_thickness() != '' && $this->get_min_thickness() > 0 ) {
				$values[] = $this->get_min_thickness();
			}
			if ( $this->get_max_thickness() != '' && $this->get_max_thickness() > 0 ) {
				$values[] = $this->get_max_thickness();
			}
			$average_thickness = ! empty( $values ) ? array_sum( $values ) / count( $values ) : 0;
			$this->average_thickness = $this->get_unit() == 'Metric' ? Geometry::mm_to_microinch( $average_thickness ) : $average_thickness;
		}
		return $this->average_thickness;
	}
	
	public function get_metal_deposit_rate()
	{
		if ( null === $this->metal_deposit_rate ) {
			$this->metal_deposit_rate = floatval( Constants::get_plating_metal_value( $this->get_metal(), 'deposit_rate' ) );
		}
		return $this->metal_deposit_rate;
	}

	public function get_metal_density()
	{
		if ( null === $this->metal_density ) {
			$this->metal_density = floatval( Constants::get_plating_metal_value( $this->get_metal(), 'density' ) );
		}
		return $this->metal_density;
	}

	public function get_metal_cost()
	{
		if ( null === $this->metal_cost ) {
			$this->metal_cost = floatval( Constants::get_plating_metal_value( $this->get_metal(), 'cost' ) );
		}
		return $this->metal_cost;
	}

	public function is_precious_metal()
	{
		return Constants::get_plating_metal_value( $this->get_metal(), 'precious_metal' );
	}
	
	public function get_min_lot_charge()
	{
		if ( null === $this->min_lot_charge ) {
			$this->min_lot_charge = Constants::get_plating_metal_value( $this->get_metal(), 'min_lot_charge' );
		}
		return $this->min_lot_charge;
	}
	
	public function get_unit_type()
	{
		if ( null === $this->unit_type ) {
			$this->unit_type = Constants::get_plating_metal_value( $this->get_metal(), 'unit_type' );
		}
		return $this->unit_type;
	}
	
	public function get_unit_visible()
	{
		if ( null === $this->unit_visible ) {
			$this->unit_visible = Constants::get_plating_metal_value( $this->get_metal(), 'unit_visible' );
		}
		return $this->unit_visible;
	}
	
	public function get_description()
	{
		if ( null === $this->description ) {
			$this->description = $this->get_metal();
			if ( $this->get_unit_visible() ) {
				switch ( $this->get_unit_type() ) {
					case 'Time':
						$unit = 'hrs';
						break;
					default:
						$unit = $this->get_unit() == 'Metric' ? 'um' : 'uin';
				}
				$this->description .= sprintf( " %s-%s %s", $this->get_min_thickness(), $this->get_max_thickness(), $unit );
			}
			if ( $this->get_specification() ) {
				$this->description .= sprintf( " per %s", $this->get_specification() );
			}
			$this->description .= sprintf( "<br />" );
		}
		return $this->description;
	}
}
