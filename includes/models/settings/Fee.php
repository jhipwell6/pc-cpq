<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Fee extends Repeater_Model
{
	protected $name;
	protected $amount;
	protected $unit;
	protected $enabled_by_default;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_amount()
	{
		return $this->get_prop( 'amount' );
	}
	
	public function get_unit()
	{
		return $this->get_prop( 'unit' );
	}
	
	public function get_enabled_by_default()
	{
		return $this->get_prop( 'enabled_by_default' );
	}
	
	public function is_enabled_by_default()
	{
		return (bool) $this->get_enabled_by_default();
	}
}