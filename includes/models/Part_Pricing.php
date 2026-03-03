<?php

namespace PC_CPQ\Models;

use \NumberFormatter;
use \PC_CPQ\Helpers\Constants;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Pricing
{
	const YEARLY_HOURS = 2080;
	const WEEKLY_HOURS = 40;
	const INVESTMENT = 0;

	private $Part;
	private $Lead;
	private $quantity_min;
	private $quantity_max;
	private $quantity_range;
	private $price_adjuster;
	private $actual_rate;
	private $utilization_rate;
	private $total_time;
	// special pricing
	private $cost_per_unit;
	private $price_per_unit;
	private $final_price_per_unit;
	// commodity pricing
	private $base_rate;
	private $base_cost_per_unit;
	private $base_price_per_unit;
	private $base_final_price_per_unit;

	/**
	 * Initializes variables.
	 * @return void
	 */
	public function __construct( $quantity_break, $Part, $Lead )
	{
		$this->set_quantity_min( $quantity_break );
		$this->set_Part( $Part );
		$this->set_Lead( $Lead );

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

	public function get_Lead()
	{
		return $this->Lead;
	}

	public function get_quantity_min()
	{
		return $this->quantity_min;
	}

	public function get_quantity_max()
	{
		if ( null === $this->quantity_max ) {
			$key = 0;
			$quantities = $this->get_Part()->get_Quantities();
			if ( $quantities ) {
				foreach ( $quantities as $k => $Quantity ) {
					if ( $Quantity->get_break_point() == $this->get_quantity_min() ) {
						$key = $k;
						break;
					}
				}
			}
			$this->quantity_max = isset( $quantities[$key + 1] ) ? $quantities[$key + 1]->get_break_point() - 1 : 'up';
		}
		return $this->quantity_max;
	}

	public function get_quantity_range()
	{
		if ( null === $this->quantity_range ) {
			$this->quantity_range = sprintf( "%s - %s", $this->get_quantity_min(), $this->get_quantity_max() );
		}
		return $this->quantity_range;
	}

	public function get_price_adjuster()
	{
		if ( null === $this->price_adjuster ) {
			switch ( $this->get_Part()->get_Pricing()->get_price_unit() ) {
				case 'lb':
					$adjuster = 1 / $this->get_Part()->get_weight();
					break;
				case 'k':
					$adjuster = 1000;
					break;
				case 'c':
					$adjuster = 100;
					break;
//				case 'g':
//					$adjuster = 1 / ( $this->get_Part()->get_weight() * 453.592 );
//					break;
//				case 'kg':
//					$adjuster = 1 / ( $this->get_Part()->get_weight() / 2.204 );
//					break;
				default:
					$adjuster = 1;
			}
			$this->price_adjuster = $adjuster;
		}
		return $this->price_adjuster;
	}

	public function get_actual_rate()
	{
		if ( null === $this->actual_rate ) {
			$this->actual_rate = ( ( Constants::$hourly_rate * ( ( 1 - $this->get_Part()->get_Pricing()->get_eff() / 100 ) + ( 1 + $this->get_Part()->get_Pricing()->get_margin() / 100 ) * ( 1 + ( 1 - $this->get_utilization_rate() ) ) ) ) * $this->get_Part()->get_Pricing()->get_people() );
		}
		return $this->actual_rate;
	}

	public function get_utilization_rate()
	{
		if ( null === $this->utilization_rate ) {
			$shifts = $this->get_Part()->get_Pricing()->get_shift();
			$total_hours = $this->get_quantity_min() / $this->get_Part()->get_pieces_per_hour();
			if ( $total_hours > self::YEARLY_HOURS ) {
				$shifts = round( $total_hours / self::YEARLY_HOURS, 0 );
			}
			if ( $shifts < 1 ) {
				$shifts = 1;
			}
			$this->utilization_rate = ( $this->get_quantity_min() / $this->get_Part()->get_pieces_per_hour() ) / ( self::YEARLY_HOURS * $shifts );
		}
		return $this->utilization_rate;
	}

	public function get_total_time( $context = 'raw' )
	{
		if ( null === $this->total_time ) {
			$this->total_time = ( $this->get_quantity_min() / $this->get_Part()->get_pieces_per_hour() );
		}
		return $context != 'raw' ? sprintf( _n( "%s hr", "%s hrs", ceil( $this->total_time ) ), ceil( $this->total_time ) ) : $this->total_time;
	}

	public function get_cost_per_unit( $context = 'raw' )
	{
		if ( null === $this->cost_per_unit ) {
			$hours = $this->get_quantity_min() > $this->get_Part()->get_pieces_per_hour() ? floor( $this->get_quantity_min() / $this->get_Part()->get_pieces_per_hour() ) : 1;
			$hours = $hours > self::WEEKLY_HOURS ? self::WEEKLY_HOURS : $hours;
			$thruput = $this->get_Part()->get_pieces_per_hour() * $hours;
			$max_rate = ( $this->get_actual_rate() * 2 ) * ( 1 + ( 1 - $this->get_utilization_rate() ) );
			$rate_increment = ( $max_rate - $this->get_actual_rate() ) / ( self::WEEKLY_HOURS - 1 );

			$hr_rate = $this->get_actual_rate() + ( ( self::WEEKLY_HOURS - $hours ) * $rate_increment );
			$price_per_sale = $hr_rate * $hours;

			$this->cost_per_unit = $price_per_sale / $thruput;
		}
		return $context != 'raw' ? $this->to_currency( $this->cost_per_unit ) : $this->cost_per_unit;
	}

	public function get_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->price_per_unit ) {
			$this->price_per_unit = $this->get_cost_per_unit() + $this->get_Part()->get_material_cost() + ( ( $this->get_Part()->get_Pricing()->get_break_in() + self::INVESTMENT ) / $this->get_quantity_min() );
		}
		return $context != 'raw' ? $this->to_currency( $this->price_per_unit ) : $this->price_per_unit;
	}

	public function get_final_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->final_price_per_unit ) {
			$this->final_price_per_unit = $this->get_price_per_unit() * $this->get_price_adjuster();
		}
		return $context != 'raw' ? $this->to_currency( $this->final_price_per_unit ) : $this->final_price_per_unit;
	}

	public function get_base_rate( $context = 'raw' )
	{
		if ( null === $this->base_rate ) {
			$total_hours = ceil( $this->get_quantity_min() / $this->get_Part()->get_pieces_per_hour() );
			$this->base_rate = $this->get_actual_rate() * $total_hours;
		}
		return $context != 'raw' ? $this->to_currency( $this->base_rate ) : $this->base_rate;
	}

	public function get_base_cost_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_cost_per_unit ) {
			$this->base_cost_per_unit = $this->get_base_rate() / $this->get_quantity_min();
		}
		return $context != 'raw' ? $this->to_currency( $this->base_cost_per_unit ) : $this->base_cost_per_unit;
	}

	public function get_base_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_price_per_unit ) {
			$this->base_price_per_unit = $this->get_base_cost_per_unit() + $this->get_Part()->get_material_cost() + ( ( $this->get_Part()->get_Pricing()->get_break_in() + self::INVESTMENT ) / $this->get_quantity_min() );
		}
		return $context != 'raw' ? $this->to_currency( $this->base_price_per_unit ) : $this->base_price_per_unit;
	}

	public function get_base_final_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_final_price_per_unit ) {
			$this->base_final_price_per_unit = $this->get_base_price_per_unit() * $this->get_price_adjuster();
		}
		return $context != 'raw' ? $this->to_currency( $this->base_final_price_per_unit ) : $this->base_final_price_per_unit;
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

	private function set_quantity_min( $quantity_min )
	{
		$this->quantity_min = $quantity_min;
		return $this->quantity_min;
	}

	/*
	 * Helpers
	 */

	private function get_props()
	{
		foreach ( get_object_vars( $this ) as $prop => $value ) {
			$getter = "get_{$prop}";
			if ( is_callable( array( $this, $getter ) ) ) {
				$this->{$getter}();
			}
		}
	}

	private function to_currency( $value, $digits = 2 )
	{
		$formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
		$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, $digits );
		return $formatter->formatCurrency( $value, 'USD' );
	}

	public function to_array( $exclude = array() )
	{
		return array_diff_key( get_object_vars( $this ), array_flip( $exclude ) );
	}
}
