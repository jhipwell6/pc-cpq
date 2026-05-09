<?php

namespace PC_CPQ\Core\Pricing;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Pricing_Context
{
	protected $Part;
	protected $Lead;
	protected $quantity_min;
	protected $quantity_max;
	protected $quantity_range;
	protected $normalized_quantity_min;
	protected $price_adjuster;

	public function __construct( $quantity_break, $Part, $Lead )
	{
		$this->quantity_min = $quantity_break;
		$this->Part = $Part;
		$this->Lead = $Lead;
	}

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
			$quantities = $this->Part->get_Quantities();
			if ( $quantities ) {
				foreach ( $quantities as $k => $Quantity ) {
					if ( $Quantity->get_break_point() == $this->quantity_min ) {
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
			$this->quantity_range = sprintf( '%s - %s', $this->get_quantity_min(), $this->get_quantity_max() );
		}
		return $this->quantity_range;
	}

	public function get_normalized_quantity_min()
	{
		if ( null === $this->normalized_quantity_min ) {
			$multiplier = $this->get_price_adjuster();
			$this->normalized_quantity_min = null === $multiplier ? null : floatval( $this->quantity_min ) * $multiplier;
		}
		return $this->normalized_quantity_min;
	}

	public function get_price_adjuster()
	{
		if ( null === $this->price_adjuster ) {
			$unit = $this->Part->get_Pricing()->get_price_unit();

			switch ( $unit ) {
				case 'lb':
					$this->price_adjuster = $this->get_parts_per_pound();
					break;
				case 'g':
					$parts_per_pound = $this->get_parts_per_pound();
					$this->price_adjuster = null === $parts_per_pound ? null : $parts_per_pound / 453.59237;
					break;
				case 'kg':
					$parts_per_pound = $this->get_parts_per_pound();
					$this->price_adjuster = null === $parts_per_pound ? null : $parts_per_pound * 2.2046226218;
					break;
				case 'k':
					$this->price_adjuster = 1000;
					break;
				case 'c':
					$this->price_adjuster = 100;
					break;
				case 'ea':
				default:
					$this->price_adjuster = 1;
			}
		}

		return $this->price_adjuster;
	}

	protected function get_parts_per_pound()
	{
		$weight = floatval( $this->Part->get_weight() );
		return $weight > 0 ? 1 / $weight : null;
	}
}
