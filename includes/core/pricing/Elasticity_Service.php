<?php

namespace PC_CPQ\Core\Pricing;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Elasticity_Service
{

	/**
	 * Log gap in quantity between breaks
	 */
	public function log_gap_volume( float $q1, float $q2 ): float
	{
		if ( $q1 <= 0 || $q2 <= 0 ) {
			return 0.0;
		}

		return log( $q2 ) - log( $q1 );
	}

	/**
	 * Log gap in price per unit between breaks
	 */
	public function log_gap_price( float $p1, float $p2 ): float
	{
		if ( $p1 <= 0 || $p2 <= 0 ) {
			return 0.0;
		}

		return log( $p2 ) - log( $p1 );
	}

	/**
	 * Volume-rebate elasticity
	 *
	 * Elasticity = % change in price / % change in quantity
	 * Using log differences:
	 * E = Δln(P) / Δln(Q)
	 */
	public function volume_rebate_elasticity(
		float $q1,
		float $q2,
		float $p1,
		float $p2
	): float
	{

		$log_q_gap = $this->log_gap_volume( $q1, $q2 );
		$log_p_gap = $this->log_gap_price( $p1, $p2 );

		if ( $log_q_gap == 0 ) {
			return 0.0;
		}

		return $log_p_gap / $log_q_gap;
	}
}
