<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Routing extends Repeater_Model
{
	protected $type;
	protected $metal;
	protected $operation;
	protected $description;
	protected $time;

	/*
	 * Getters
	 */
	
	public function get_type()
	{
		return $this->get_prop( 'type' );
	}
	
	public function get_metal()
	{
		return $this->get_prop( 'metal' );
	}

	public function get_operation()
	{
		if ( null === $this->operation ) {
			$this->operation = $this->get_details( 'operation' );
		}
		return $this->operation;
	}

	public function get_description()
	{
		if ( null === $this->description ) {
			$this->description = $this->get_details( 'description' );
		}
		return $this->description;
	}

	public function get_time()
	{
		if ( null === $this->time ) {
			$this->time =  $this->get_details( 'cycle_time' );
		}
		return $this->time;
	}
	
	public function get_time_unit()
	{
		if ( null === $this->time_unit ) {
			$this->time_unit =  $this->get_details( 'cycle_unit' );
		}
		return $this->time_unit;
	}
	
	private function get_details( $property )
	{
		$getter = "get_{$property}";
		return $this->get_site_Operation() ? $this->get_site_Operation()->{$getter}() : false;
	}

	private function get_site_Operation()
	{
		return PC_CPQ()->Settings()->find_operation( $this->get_type(), $this->get_metal() );
	}
}
