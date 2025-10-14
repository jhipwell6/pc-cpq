<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Barrel extends Repeater_Model
{
	protected $name;
	protected $size_limit;
	protected $ft2_load;
	protected $weight_limit;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_size_limit()
	{
		return $this->get_prop( 'size_limit' );
	}
	
	public function get_ft2_load()
	{
		return $this->get_prop( 'ft2_load' );
	}
	
	public function get_weight_limit()
	{
		return $this->get_prop( 'weight_limit' );
	}
}