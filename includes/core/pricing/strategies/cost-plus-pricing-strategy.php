<?php

namespace PC_CPQ\Core\Pricing\Strategies;

use PC_CPQ\Core\Pricing\Pricing_Context;
use PC_CPQ\Core\Pricing\Pricing_Result;
use PC_CPQ\Core\Pricing\Pricing_Strategy_Interface;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Cost_Plus_Pricing_Strategy implements Pricing_Strategy_Interface
{
	const INVESTMENT = 0;

	public function calculate( Pricing_Context $context ): Pricing_Result
	{
		$Part = $context->get_Part();
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $Part->get_pieces_per_hour();
		$total_time = $this->calculate_total_time( $context );
		$labor_rate = $this->calculate_labor_rate( $context );
		$labor_cost_per_unit = $this->calculate_labor_cost_per_unit( $context, $labor_rate, $total_time );
		$total_cost_per_unit = $this->calculate_total_cost_per_unit( $context, $labor_cost_per_unit );
		$sale_price_per_each = $this->calculate_sale_price_per_each( $context, $total_cost_per_unit );
		$final_price_per_unit = $this->calculate_final_price_per_unit( $context, $sale_price_per_each );

		return new Pricing_Result( array(
			'quantity_min' => $context->get_quantity_min(),
			'quantity_max' => $context->get_quantity_max(),
			'quantity_range' => $context->get_quantity_range(),
			'normalized_quantity_min' => $normalized_quantity,
			'price_adjuster' => $context->get_price_adjuster(),
			'pieces_per_hour' => $pieces_per_hour > 0 ? $pieces_per_hour : null,
			'actual_rate' => $labor_rate,
			'utilization_rate' => null,
			'total_time' => $total_time,
			'cost_per_unit' => $labor_cost_per_unit,
			'price_per_unit' => $total_cost_per_unit,
			'final_price_per_unit' => $final_price_per_unit,
			'base_rate' => $labor_rate,
			'base_cost_per_unit' => $labor_cost_per_unit,
			'base_price_per_unit' => $total_cost_per_unit,
			'base_final_price_per_unit' => $final_price_per_unit,
		) );
	}

	protected function calculate_total_time( Pricing_Context $context )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $context->get_Part()->get_pieces_per_hour();
		if ( null === $normalized_quantity || $pieces_per_hour <= 0 ) {
			return null;
		}

		return $normalized_quantity / $pieces_per_hour;
	}

	protected function calculate_labor_rate( Pricing_Context $context )
	{
		$pricing = $context->get_Part()->get_Pricing();
		$efficiency = $pricing->get_eff();
		$efficiency_multiplier = $efficiency > 0 ? 100 / $efficiency : null;

		if ( null === $efficiency_multiplier ) {
			return null;
		}

		return PC_CPQ()->Settings()->get_hourly_rate()
			* $pricing->get_people()
			* $efficiency_multiplier;
	}

	protected function calculate_labor_cost_per_unit( Pricing_Context $context, $labor_rate, $total_time )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		if ( null === $labor_rate || null === $total_time || null === $normalized_quantity || $normalized_quantity <= 0 ) {
			return null;
		}

		return ( $labor_rate * $total_time ) / $normalized_quantity;
	}

	protected function calculate_total_cost_per_unit( Pricing_Context $context, $labor_cost_per_unit )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		if ( null === $labor_cost_per_unit || null === $normalized_quantity || $normalized_quantity <= 0 ) {
			return null;
		}

		$Part = $context->get_Part();

		return $labor_cost_per_unit
			+ $Part->get_material_cost()
			+ ( ( $Part->get_Pricing()->get_break_in() + self::INVESTMENT ) / $normalized_quantity );
	}

	protected function calculate_sale_price_per_each( Pricing_Context $context, $total_cost_per_unit )
	{
		if ( null === $total_cost_per_unit ) {
			return null;
		}

		$margin = $context->get_Part()->get_Pricing()->get_margin();

		return $total_cost_per_unit * ( 1 + ( $margin / 100 ) );
	}

	protected function calculate_final_price_per_unit( Pricing_Context $context, $sale_price_per_each )
	{
		$price_adjuster = $context->get_price_adjuster();
		if ( null === $sale_price_per_each || null === $price_adjuster ) {
			return null;
		}

		return $sale_price_per_each * $price_adjuster;
	}
}
