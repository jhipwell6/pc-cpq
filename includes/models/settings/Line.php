<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Line extends Repeater_Model
{
	protected $name;
	protected $plate_cells;
	protected $max_pulls_per_hour;
	protected $barrel_size_limit;
	protected $rack_size_limit;
	protected $rack_factor;
	protected $weight_limit;
	protected $rack_ld_max_in2;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_plate_cells()
	{
		return $this->get_prop( 'plate_cells' );
	}
	
	public function get_max_pulls_per_hour()
	{
		return $this->get_prop( 'max_pulls_per_hour' );
	}
	
	public function get_barrel_size_limit()
	{
		return $this->get_prop( 'barrel_size_limit' );
	}
	
	public function get_rack_size_limit()
	{
		return $this->get_prop( 'rack_size_limit' );
	}
	
	public function get_rack_factor()
	{
		return $this->get_prop( 'rack_factor' );
	}
	
	public function get_weight_limit()
	{
		return $this->get_prop( 'weight_limit' );
	}
	
	public function get_rack_ld_max_in2()
	{
		return $this->get_prop( 'rack_ld_max_in2' );
	}
}