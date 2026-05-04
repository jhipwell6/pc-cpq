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
	private $normalized_quantity_min;
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

	public function get_normalized_quantity_min()
	{
		if ( null === $this->normalized_quantity_min ) {
			$this->normalized_quantity_min = $this->normalize_quantity_to_each( $this->get_quantity_min() );
		}
		return $this->normalized_quantity_min;
	}

	public function get_price_adjuster()
	{
		if ( null === $this->price_adjuster ) {
			$this->price_adjuster = $this->get_unit_quantity_multiplier();
		}
		return $this->price_adjuster;
	}

	public function get_actual_rate()
	{
		if ( null === $this->actual_rate ) {
			$utilization_rate = $this->get_utilization_rate();
			if ( null === $utilization_rate ) {
				return null;
			}
			$this->actual_rate = ( ( Constants::$hourly_rate * ( ( 1 - $this->get_Part()->get_Pricing()->get_eff() / 100 ) + ( 1 + $this->get_Part()->get_Pricing()->get_margin() / 100 ) * ( 1 + ( 1 - $utilization_rate ) ) ) ) * $this->get_Part()->get_Pricing()->get_people() );
		}
		return $this->actual_rate;
	}

	public function get_utilization_rate()
	{
		if ( null === $this->utilization_rate ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			if ( null === $normalized_quantity || $this->get_Part()->get_pieces_per_hour() <= 0 ) {
				return null;
			}
			$shifts = $this->get_Part()->get_Pricing()->get_shift();
			$total_hours = $normalized_quantity / $this->get_Part()->get_pieces_per_hour();
			if ( $total_hours > self::YEARLY_HOURS ) {
				$shifts = round( $total_hours / self::YEARLY_HOURS, 0 );
			}
			if ( $shifts < 1 ) {
				$shifts = 1;
			}
			$this->utilization_rate = $total_hours / ( self::YEARLY_HOURS * $shifts );
		}
		return $this->utilization_rate;
	}

	public function get_total_time( $context = 'raw' )
	{
		if ( null === $this->total_time ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			if ( null === $normalized_quantity || $this->get_Part()->get_pieces_per_hour() <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->total_time = ( $normalized_quantity / $this->get_Part()->get_pieces_per_hour() );
		}
		return $context != 'raw' ? sprintf( _n( "%s hr", "%s hrs", ceil( $this->total_time ) ), ceil( $this->total_time ) ) : $this->total_time;
	}

	public function get_cost_per_unit( $context = 'raw' )
	{
		if ( null === $this->cost_per_unit ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			if ( null === $normalized_quantity || $this->get_Part()->get_pieces_per_hour() <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$actual_rate = $this->get_actual_rate();
			$utilization_rate = $this->get_utilization_rate();
			if ( null === $actual_rate || null === $utilization_rate ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$hours = $normalized_quantity > $this->get_Part()->get_pieces_per_hour() ? floor( $normalized_quantity / $this->get_Part()->get_pieces_per_hour() ) : 1;
			$hours = $hours > self::WEEKLY_HOURS ? self::WEEKLY_HOURS : $hours;
			$thruput = $this->get_Part()->get_pieces_per_hour() * $hours;
			$max_rate = ( $actual_rate * 2 ) * ( 1 + ( 1 - $utilization_rate ) );
			$rate_increment = ( $max_rate - $actual_rate ) / ( self::WEEKLY_HOURS - 1 );

			$hr_rate = $actual_rate + ( ( self::WEEKLY_HOURS - $hours ) * $rate_increment );
			$price_per_sale = $hr_rate * $hours;

			$this->cost_per_unit = $thruput > 0 ? $price_per_sale / $thruput : null;
		}
		return $context != 'raw' ? $this->to_currency( $this->cost_per_unit ) : $this->cost_per_unit;
	}

	public function get_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->price_per_unit ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			$cost_per_unit = $this->get_cost_per_unit();
			if ( null === $normalized_quantity || null === $cost_per_unit || $normalized_quantity <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->price_per_unit = $cost_per_unit + $this->get_Part()->get_material_cost() + ( ( $this->get_Part()->get_Pricing()->get_break_in() + self::INVESTMENT ) / $normalized_quantity );
		}
		return $context != 'raw' ? $this->to_currency( $this->price_per_unit ) : $this->price_per_unit;
	}

	public function get_final_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->final_price_per_unit ) {
			$price_per_unit = $this->get_price_per_unit();
			$price_adjuster = $this->get_price_adjuster();
			if ( null === $price_per_unit || null === $price_adjuster ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->final_price_per_unit = $price_per_unit * $price_adjuster;
		}
		return $context != 'raw' ? $this->to_currency( $this->final_price_per_unit ) : $this->final_price_per_unit;
	}

	public function get_base_rate( $context = 'raw' )
	{
		if ( null === $this->base_rate ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			if ( null === $normalized_quantity || $this->get_Part()->get_pieces_per_hour() <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$total_hours = ceil( $normalized_quantity / $this->get_Part()->get_pieces_per_hour() );
			$actual_rate = $this->get_actual_rate();
			if ( null === $actual_rate ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->base_rate = $actual_rate * $total_hours;
		}
		return $context != 'raw' ? $this->to_currency( $this->base_rate ) : $this->base_rate;
	}

	public function get_base_cost_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_cost_per_unit ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			$base_rate = $this->get_base_rate();
			if ( null === $normalized_quantity || null === $base_rate || $normalized_quantity <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->base_cost_per_unit = $base_rate / $normalized_quantity;
		}
		return $context != 'raw' ? $this->to_currency( $this->base_cost_per_unit ) : $this->base_cost_per_unit;
	}

	public function get_base_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_price_per_unit ) {
			$normalized_quantity = $this->get_normalized_quantity_min();
			$base_cost_per_unit = $this->get_base_cost_per_unit();
			if ( null === $normalized_quantity || null === $base_cost_per_unit || $normalized_quantity <= 0 ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->base_price_per_unit = $base_cost_per_unit + $this->get_Part()->get_material_cost() + ( ( $this->get_Part()->get_Pricing()->get_break_in() + self::INVESTMENT ) / $normalized_quantity );
		}
		return $context != 'raw' ? $this->to_currency( $this->base_price_per_unit ) : $this->base_price_per_unit;
	}

	public function get_base_final_price_per_unit( $context = 'raw' )
	{
		if ( null === $this->base_final_price_per_unit ) {
			$base_price_per_unit = $this->get_base_price_per_unit();
			$price_adjuster = $this->get_price_adjuster();
			if ( null === $base_price_per_unit || null === $price_adjuster ) {
				return $context != 'raw' ? 'N/A' : null;
			}
			$this->base_final_price_per_unit = $base_price_per_unit * $price_adjuster;
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
		if ( null === $value ) {
			return 'N/A';
		}
		$formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
		$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, $digits );
		return $formatter->formatCurrency( $value, 'USD' );
	}

	private function normalize_quantity_to_each( $quantity )
	{
		$multiplier = $this->get_unit_quantity_multiplier();

		if ( null === $multiplier ) {
			return null;
		}

		return floatval( $quantity ) * $multiplier;
	}

	private function get_unit_quantity_multiplier()
	{
		$unit = $this->get_Part()->get_Pricing()->get_price_unit();

		switch ( $unit ) {
			case 'lb':
				return $this->get_parts_per_pound();
			case 'g':
				$parts_per_pound = $this->get_parts_per_pound();
				return null === $parts_per_pound ? null : $parts_per_pound / 453.59237;
			case 'kg':
				$parts_per_pound = $this->get_parts_per_pound();
				return null === $parts_per_pound ? null : $parts_per_pound * 2.2046226218;
			case 'k':
				return 1000;
			case 'c':
				return 100;
			case 'ea':
			default:
				return 1;
		}
	}

	private function get_parts_per_pound()
	{
		$weight = floatval( $this->get_Part()->get_weight() );
		return $weight > 0 ? 1 / $weight : null;
	}

	public function to_array( $exclude = array() )
	{
		return array_diff_key( get_object_vars( $this ), array_flip( $exclude ) );
	}
}
