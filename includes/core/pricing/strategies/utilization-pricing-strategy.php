<?php

namespace PC_CPQ\Core\Pricing\Strategies;

use PC_CPQ\Core\Pricing\Pricing_Context;
use PC_CPQ\Core\Pricing\Pricing_Result;
use PC_CPQ\Core\Pricing\Pricing_Strategy_Interface;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Utilization_Pricing_Strategy implements Pricing_Strategy_Interface
{
	const YEARLY_HOURS = 2080;
	const WEEKLY_HOURS = 40;
	const INVESTMENT = 0;

	public function calculate( Pricing_Context $context ): Pricing_Result
	{
		$Part = $context->get_Part();
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $Part->get_pieces_per_hour();
		$actual_rate = $this->calculate_actual_rate( $context );
		$utilization_rate = $this->calculate_utilization_rate( $context );
		$total_time = $this->calculate_total_time( $context );
		$cost_per_unit = $this->calculate_cost_per_unit( $context, $actual_rate, $utilization_rate );
		$price_per_unit = $this->calculate_price_per_unit( $context, $cost_per_unit );
		$final_price_per_unit = $this->calculate_final_price_per_unit( $context, $price_per_unit );
		$base_rate = $this->calculate_base_rate( $context, $actual_rate );
		$base_cost_per_unit = $this->calculate_base_cost_per_unit( $normalized_quantity, $base_rate );
		$base_price_per_unit = $this->calculate_base_price_per_unit( $context, $base_cost_per_unit );
		$base_final_price_per_unit = $this->calculate_base_final_price_per_unit( $context, $base_price_per_unit );

		return new Pricing_Result( array(
			'quantity_min' => $context->get_quantity_min(),
			'quantity_max' => $context->get_quantity_max(),
			'quantity_range' => $context->get_quantity_range(),
			'normalized_quantity_min' => $normalized_quantity,
			'price_adjuster' => $context->get_price_adjuster(),
			'pieces_per_hour' => $pieces_per_hour > 0 ? $pieces_per_hour : null,
			'actual_rate' => $actual_rate,
			'utilization_rate' => $utilization_rate,
			'total_time' => $total_time,
			'cost_per_unit' => $cost_per_unit,
			'price_per_unit' => $price_per_unit,
			'final_price_per_unit' => $final_price_per_unit,
			'base_rate' => $base_rate,
			'base_cost_per_unit' => $base_cost_per_unit,
			'base_price_per_unit' => $base_price_per_unit,
			'base_final_price_per_unit' => $base_final_price_per_unit,
		) );
	}

	protected function calculate_actual_rate( Pricing_Context $context )
	{
		$utilization_rate = $this->calculate_utilization_rate( $context );
		if ( null === $utilization_rate ) {
			return null;
		}

		$pricing = $context->get_Part()->get_Pricing();

		return (
			PC_CPQ()->Settings()->get_hourly_rate()
			* (
				( 1 - $pricing->get_eff() / 100 )
				+ ( 1 + $pricing->get_margin() / 100 ) * ( 1 + ( 1 - $utilization_rate ) )
			)
			* $pricing->get_people()
		);
	}

	protected function calculate_utilization_rate( Pricing_Context $context )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $context->get_Part()->get_pieces_per_hour();
		if ( null === $normalized_quantity || $pieces_per_hour <= 0 ) {
			return null;
		}

		$shifts = $context->get_Part()->get_Pricing()->get_shift();
		$total_hours = $normalized_quantity / $pieces_per_hour;
		if ( $total_hours > self::YEARLY_HOURS ) {
			$shifts = round( $total_hours / self::YEARLY_HOURS, 0 );
		}
		if ( $shifts < 1 ) {
			$shifts = 1;
		}

		return $total_hours / ( self::YEARLY_HOURS * $shifts );
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

	protected function calculate_cost_per_unit( Pricing_Context $context, $actual_rate, $utilization_rate )
	{
		$Part = $context->get_Part();
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $Part->get_pieces_per_hour();
		if ( null === $normalized_quantity || $pieces_per_hour <= 0 || null === $actual_rate || null === $utilization_rate ) {
			return null;
		}

		$hours = $normalized_quantity > $pieces_per_hour ? floor( $normalized_quantity / $pieces_per_hour ) : 1;
		$hours = $hours > self::WEEKLY_HOURS ? self::WEEKLY_HOURS : $hours;
		$thruput = $pieces_per_hour * $hours;
		$max_rate = ( $actual_rate * 2 ) * ( 1 + ( 1 - $utilization_rate ) );
		$rate_increment = ( $max_rate - $actual_rate ) / ( self::WEEKLY_HOURS - 1 );
		$hr_rate = $actual_rate + ( ( self::WEEKLY_HOURS - $hours ) * $rate_increment );
		$price_per_sale = $hr_rate * $hours;

		return $thruput > 0 ? $price_per_sale / $thruput : null;
	}

	protected function calculate_price_per_unit( Pricing_Context $context, $cost_per_unit )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		if ( null === $normalized_quantity || null === $cost_per_unit || $normalized_quantity <= 0 ) {
			return null;
		}

		$Part = $context->get_Part();

		return $cost_per_unit
			+ $Part->get_material_cost()
			+ ( ( $Part->get_Pricing()->get_break_in() + self::INVESTMENT ) / $normalized_quantity );
	}

	protected function calculate_final_price_per_unit( Pricing_Context $context, $price_per_unit )
	{
		$price_adjuster = $context->get_price_adjuster();
		if ( null === $price_per_unit || null === $price_adjuster ) {
			return null;
		}

		return $price_per_unit * $price_adjuster;
	}

	protected function calculate_base_rate( Pricing_Context $context, $actual_rate )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		$pieces_per_hour = $context->get_Part()->get_pieces_per_hour();
		if ( null === $normalized_quantity || $pieces_per_hour <= 0 || null === $actual_rate ) {
			return null;
		}

		$total_hours = ceil( $normalized_quantity / $pieces_per_hour );
		return $actual_rate * $total_hours;
	}

	protected function calculate_base_cost_per_unit( $normalized_quantity, $base_rate )
	{
		if ( null === $normalized_quantity || null === $base_rate || $normalized_quantity <= 0 ) {
			return null;
		}

		return $base_rate / $normalized_quantity;
	}

	protected function calculate_base_price_per_unit( Pricing_Context $context, $base_cost_per_unit )
	{
		$normalized_quantity = $context->get_normalized_quantity_min();
		if ( null === $normalized_quantity || null === $base_cost_per_unit || $normalized_quantity <= 0 ) {
			return null;
		}

		$Part = $context->get_Part();

		return $base_cost_per_unit
			+ $Part->get_material_cost()
			+ ( ( $Part->get_Pricing()->get_break_in() + self::INVESTMENT ) / $normalized_quantity );
	}

	protected function calculate_base_final_price_per_unit( Pricing_Context $context, $base_price_per_unit )
	{
		$price_adjuster = $context->get_price_adjuster();
		if ( null === $base_price_per_unit || null === $price_adjuster ) {
			return null;
		}

		return $base_price_per_unit * $price_adjuster;
	}
}
