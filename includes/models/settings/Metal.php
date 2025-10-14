<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Metal extends Repeater_Model
{
	protected $name;
	protected $density;
	protected $prep_cycle;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_density()
	{
		return $this->get_prop( 'density' );
	}
	
	public function get_prep_cycle()
	{
		return $this->get_prop( 'prep_cycle' );
	}
}