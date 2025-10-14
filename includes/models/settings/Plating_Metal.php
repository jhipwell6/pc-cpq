<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Plating_Metal extends Repeater_Model
{
	protected $name;
	protected $density;
	protected $cost;
	protected $deposit_rate;
	protected $unit_type;
	protected $unit_visible;
	protected $min_lot_charge;
	protected $precious_metal;
	protected $hide;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_density()
	{
		return $this->get_prop( 'density' );
	}
	
	public function get_cost()
	{
		return $this->get_prop( 'cost' );
	}
	
	public function get_deposit_rate()
	{
		return $this->get_prop( 'deposit_rate' );
	}
	
	public function get_unit_type()
	{
		return $this->get_prop( 'unit_type' );
	}
	
	public function get_unit_visible()
	{
		return $this->get_prop( 'unit_visible' );
	}
	
	public function get_min_lot_charge()
	{
		return $this->get_prop( 'min_lot_charge' );
	}
	
	public function get_precious_metal()
	{
		return (bool) $this->get_prop( 'precious_metal' );
	}
	
	public function is_precious_metal()
	{
		return $this->get_precious_metal();
	}
	
	public function get_hide()
	{
		return (bool) $this->get_prop( 'hide' );
	}
	
	public function is_hidden()
	{
		return $this->get_hide();
	}
}