<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Pricing_Inputs
{
	const PRICE_UNITS = [
		'ea' => [
			'price_label' => 'Per Ea',
			'quantity_label' => 'Ea',
		],
		'lb' => [
			'price_label' => 'Per Lb',
			'quantity_label' => 'Lbs',
		],
		'k' => [
			'price_label' => 'Per K',
			'quantity_label' => 'K',
		],
		'c' => [
			'price_label' => 'Per C',
			'quantity_label' => 'C',
		],
		'g' => [
			'price_label' => 'Per G',
			'quantity_label' => 'G',
		],
		'kg' => [
			'price_label' => 'Per Kg',
			'quantity_label' => 'Kg',
		],
	];
	
	protected $margin;
	protected $eff;
	protected $people;
	protected $eau;
	protected $shift;
	protected $break_in;
	protected $metal_adder;
	protected $price_unit;
	
	/**
	 * Initializes variables.
	 * @return void
	 */
	public function __construct( $raw_data )
	{
		$this->set_raw_data( $raw_data );

		// fill properties
		$this->get_props();
		return $this;
	}
	
	public function get_margin(): float
	{
		return floatval( $this->get_prop( 'margin' ) );
	}
	
	public function get_eff(): float
	{
		return floatval( $this->get_prop( 'eff' ) );
	}
	
	public function get_people(): float
	{
		return floatval( $this->get_prop( 'people' ) );
	}
	
	public function get_eau(): float
	{
		return floatval( $this->get_prop( 'eau' ) );
	}
	
	public function get_shift(): float
	{
		return floatval( $this->get_prop( 'shift' ) );
	}
	
	public function get_break_in(): float
	{
		return floatval( $this->get_prop( 'break_in' ) );
	}
	
	public function get_metal_adder(): float
	{
		return floatval( $this->get_prop( 'metal_adder' ) );
	}
	
	public function get_price_unit(): string
	{
		return self::sanitize_price_unit( (string) $this->get_prop( 'price_unit' ) );
	}

	public function get_price_unit_label(): string
	{
		$definition = self::get_price_unit_definition( $this->get_price_unit() );
		return $definition['price_label'];
	}

	public function get_quantity_unit_label(): string
	{
		$definition = self::get_price_unit_definition( $this->get_price_unit() );
		return $definition['quantity_label'];
	}

	public static function get_price_unit_definition( string $value ): array
	{
		$unit = self::sanitize_price_unit( $value );
		return self::PRICE_UNITS[$unit];
	}

	public static function get_price_units(): array
	{
		return self::PRICE_UNITS;
	}

	public static function sanitize_price_unit( string $value ): string
	{
		$value = strtolower( trim( $value ) );

		if ( 'each' === $value || '' === $value ) {
			return 'ea';
		}

		return array_key_exists( $value, self::PRICE_UNITS ) ? $value : 'ea';
	}
	
	/*
	 * Setters
	 */
	
	private function set_raw_data( $raw_data )
	{
		$this->raw_data = isset( $raw_data[0] ) ? array_first( $raw_data ) : $raw_data;
		return $this->raw_data;
	}
	
	/*
	 * Helpers
	 */
	
	protected function get_raw_data( $prop = null )
	{
		return ( $prop && isset( $this->raw_data[$prop] ) ) ? $this->raw_data[$prop] : $this->raw_data;
	}
	
	protected function get_meta( $prop )
	{
		if ( isset( $this->raw_data[$prop] ) ) {
			return $this->raw_data[$prop];
		}
		return false;
	}
	
	protected function get_prop( $prop )
	{
		if ( ! $this->has_prop( $prop ) )
			return false;

		if ( null === $this->{$prop} || empty( $this->{$prop} ) ) {
			$this->{$prop} = $this->get_meta( $prop );
		}
		return $this->{$prop};
	}

	protected function get_props()
	{
		foreach ( get_object_vars( $this ) as $prop => $value ) {
			$getter = "get_{$prop}";
			if ( is_callable( array( $this, $getter ) ) ) {
				$this->{$getter}();
			}
		}
	}

	public function has_prop( $prop )
	{
		return property_exists( $this, $prop );
	}
	
	public function to_array( $exclude = array() )
	{
		$data = get_object_vars( $this );
		unset( $data['raw_data'] );
		$data['price_unit'] = $this->get_price_unit();
		return array_diff_key( $data, array_flip( $exclude ) );
	}
}
