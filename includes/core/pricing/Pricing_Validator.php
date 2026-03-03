<?php
namespace PC_CPQ\Core\Pricing;

if ( ! defined( 'ABSPATH' ) ) exit;

use PC_CPQ\Core\Pricing\Elasticity_Service;

class Pricing_Validator {

    protected Elasticity_Service $elasticity;

    protected float $elasticity_threshold = 2.0;

    public function __construct() {
        $this->elasticity = new Elasticity_Service();
    }

    /**
     * Validate pricing breaks for a single Part
     *
     * @param array $breaks [
     *   ['qty'=>1,'price'=>10.00],
     *   ['qty'=>10,'price'=>8.00],
     * ]
     */
    public function validate_breaks( array $breaks ): array {

        $issues = [];

        if ( count( $breaks ) < 2 ) {
            return $issues;
        }

        for ( $i = 0; $i < count($breaks) - 1; $i++ ) {

            $b1 = $breaks[$i];
            $b2 = $breaks[$i + 1];

            $q1 = (float) $b1['qty'];
            $q2 = (float) $b2['qty'];
            $p1 = (float) $b1['price'];
            $p2 = (float) $b2['price'];

            $total1 = $q1 * $p1;
            $total2 = $q2 * $p2;

            /*
             * ----------------------------------------
             * 1️⃣ Elasticity magnitude > threshold
             * ----------------------------------------
             */
            $elasticity = $this->elasticity->volume_rebate_elasticity(
                $q1, $q2, $p1, $p2
            );

            if ( abs( $elasticity ) > $this->elasticity_threshold ) {
                $issues[] = [
                    'code'     => 'ELASTICITY_CLIFF',
                    'severity' => 'block',
                    'message'  => "Elasticity {$elasticity} between {$q1} and {$q2} exceeds threshold.",
                    'context'  => compact('q1','q2','p1','p2','elasticity'),
                ];
            }

            /*
             * ----------------------------------------
             * 2️⃣ Negative total jump
             * (ordering more costs LESS overall)
             * ----------------------------------------
             */
            if ( $total2 < $total1 ) {
                $issues[] = [
                    'code'     => 'NEGATIVE_TOTAL_JUMP',
                    'severity' => 'block',
                    'message'  => "Total price drops from {$total1} to {$total2} when moving from {$q1} to {$q2}.",
                    'context'  => compact('q1','q2','total1','total2'),
                ];
            }

            /*
             * ----------------------------------------
             * 3️⃣ Total price cliff
             * Large discontinuity in total pricing
             * ----------------------------------------
             */
            $total_delta_ratio = ($total2 - $total1) / max($total1, 0.00001);

            // Example: >50% total change between adjacent breaks
            if ( abs( $total_delta_ratio ) > 0.5 ) {
                $issues[] = [
                    'code'     => 'TOTAL_PRICE_CLIFF',
                    'severity' => 'block',
                    'message'  => "Total price cliff detected between {$q1} and {$q2}.",
                    'context'  => compact('q1','q2','total1','total2','total_delta_ratio'),
                ];
            }
        }

        return $issues;
    }
}