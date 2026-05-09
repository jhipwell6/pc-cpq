<?php

namespace PC_CPQ\Models;

use PC_CPQ\Core\Pricing\Pricing_Calculator;
if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Pricing
{
	private $Part;
	private $Lead;
	private $result;

	/**
	 * Initializes variables.
	 * @return void
	 */
	public function __construct( $quantity_break, $Part, $Lead )
	{
		$this->set_Part( $Part );
		$this->set_Lead( $Lead );
		$this->result = ( new Pricing_Calculator( null, $Lead->get_pricing_mode() ) )->calculate( $quantity_break, $Part, $Lead );
		return $this;
	}

	/*
	 * Getters
	 */

	public function get_Part()
	{
		return $this->Part;
	}

	public function get_Lead()
	{
		return $this->Lead;
	}

	public function get_result()
	{
		return $this->result;
	}

	public function get_quantity_min()
	{
		return $this->result->get_quantity_min();
	}

	public function get_quantity_max()
	{
		return $this->result->get_quantity_max();
	}

	public function get_quantity_range()
	{
		return $this->result->get_quantity_range();
	}

	public function get_normalized_quantity_min()
	{
		return $this->result->get_normalized_quantity_min();
	}

	public function get_price_adjuster()
	{
		return $this->result->get_price_adjuster();
	}

	public function get_actual_rate()
	{
		return $this->result->get_actual_rate();
	}

	public function get_utilization_rate()
	{
		return $this->result->get_utilization_rate();
	}

	public function get_total_time( $context = 'raw' )
	{
		return $this->result->get_total_time( $context );
	}

	public function get_cost_per_unit( $context = 'raw' )
	{
		return $this->result->get_cost_per_unit( $context );
	}

	public function get_price_per_unit( $context = 'raw' )
	{
		return $this->result->get_price_per_unit( $context );
	}

	public function get_final_price_per_unit( $context = 'raw' )
	{
		return $this->result->get_final_price_per_unit( $context );
	}

	public function get_base_rate( $context = 'raw' )
	{
		return $this->result->get_base_rate( $context );
	}

	public function get_base_cost_per_unit( $context = 'raw' )
	{
		return $this->result->get_base_cost_per_unit( $context );
	}

	public function get_base_price_per_unit( $context = 'raw' )
	{
		return $this->result->get_base_price_per_unit( $context );
	}

	public function get_base_final_price_per_unit( $context = 'raw' )
	{
		return $this->result->get_base_final_price_per_unit( $context );
	}

	/*
	 * Setters
	 */

	private function set_Part( $Part )
	{
		$this->Part = $Part;
		return $this->Part;
	}

	private function set_Lead( $Lead )
	{
		$this->Lead = $Lead;
		return $this->Lead;
	}

	public function to_array( $exclude = array() )
	{
		$data = $this->result->to_array();
		$data['Part'] = $this->Part;
		$data['Lead'] = $this->Lead;
		return array_diff_key( $data, array_flip( $exclude ) );
	}
}
