<?php

namespace PC_CPQ\Core\Pricing;

use NumberFormatter;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Pricing_Result
{
	protected $data = array();

	public function __construct( array $data )
	{
		$this->data = $data;
	}

	public function get( string $key )
	{
		return $this->data[$key] ?? null;
	}

	public function get_quantity_min()
	{
		return $this->get( 'quantity_min' );
	}

	public function get_quantity_max()
	{
		return $this->get( 'quantity_max' );
	}

	public function get_quantity_range()
	{
		return $this->get( 'quantity_range' );
	}

	public function get_normalized_quantity_min()
	{
		return $this->get( 'normalized_quantity_min' );
	}

	public function get_price_adjuster()
	{
		return $this->get( 'price_adjuster' );
	}

	public function get_actual_rate()
	{
		return $this->get( 'actual_rate' );
	}

	public function get_utilization_rate()
	{
		return $this->get( 'utilization_rate' );
	}

	public function get_total_time( $context = 'raw' )
	{
		$value = $this->get( 'total_time' );
		if ( 'raw' === $context ) {
			return $value;
		}
		if ( null === $value ) {
			return 'N/A';
		}
		return sprintf( _n( '%s hr', '%s hrs', ceil( $value ) ), ceil( $value ) );
	}

	public function get_cost_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'cost_per_unit', $context );
	}

	public function get_price_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'price_per_unit', $context );
	}

	public function get_final_price_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'final_price_per_unit', $context );
	}

	public function get_base_rate( $context = 'raw' )
	{
		return $this->get_currency_value( 'base_rate', $context );
	}

	public function get_base_cost_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'base_cost_per_unit', $context );
	}

	public function get_base_price_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'base_price_per_unit', $context );
	}

	public function get_base_final_price_per_unit( $context = 'raw' )
	{
		return $this->get_currency_value( 'base_final_price_per_unit', $context );
	}

	public function to_array(): array
	{
		return $this->data;
	}

	protected function get_currency_value( string $key, $context = 'raw' )
	{
		$value = $this->get( $key );
		if ( 'raw' === $context ) {
			return $value;
		}
		return $this->to_currency( $value );
	}

	protected function to_currency( $value, $digits = 2 )
	{
		if ( null === $value ) {
			return 'N/A';
		}
		$formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
		$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, $digits );
		return $formatter->formatCurrency( $value, 'USD' );
	}
}
