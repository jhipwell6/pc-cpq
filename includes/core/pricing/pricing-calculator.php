<?php

namespace PC_CPQ\Core\Pricing;

use PC_CPQ\Core\Pricing\Strategies\Utilization_Pricing_Strategy;
use PC_CPQ\Core\Pricing\Strategies\Cost_Plus_Pricing_Strategy;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Pricing_Calculator
{
	const MODE_UTILIZATION = 'utilization';
	const MODE_COST_PLUS = 'cost_plus';

	protected $strategy;

	public function __construct( Pricing_Strategy_Interface $strategy = null, ?string $mode = null )
	{
		$this->strategy = $strategy ?: self::make_strategy( $mode );
	}

	public function calculate( $quantity_break, $Part, $Lead ): Pricing_Result
	{
		$context = new Pricing_Context( $quantity_break, $Part, $Lead );
		return $this->strategy->calculate( $context );
	}

	public function calculate_part_rows( $Part, $Lead ): array
	{
		$rows = array();

		foreach ( (array) $Part->get_Quantities() as $Quantity ) {
			$rows[] = $this->calculate( $Quantity->get_break_point(), $Part, $Lead );
		}

		return $rows;
	}

	public static function sanitize_mode( ?string $mode ): string
	{
		$mode = strtolower( trim( (string) $mode ) );

		return in_array( $mode, array( self::MODE_UTILIZATION, self::MODE_COST_PLUS ), true )
			? $mode
			: self::MODE_UTILIZATION;
	}

	public static function get_modes(): array
	{
		return array(
			self::MODE_UTILIZATION => 'Utilization',
			self::MODE_COST_PLUS => 'Cost Plus',
		);
	}

	protected static function make_strategy( ?string $mode ): Pricing_Strategy_Interface
	{
		switch ( self::sanitize_mode( $mode ) ) {
			case self::MODE_COST_PLUS:
				return new Cost_Plus_Pricing_Strategy();
			case self::MODE_UTILIZATION:
			default:
				return new Utilization_Pricing_Strategy();
		}
	}
}
