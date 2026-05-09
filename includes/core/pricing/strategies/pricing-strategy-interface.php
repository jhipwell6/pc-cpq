<?php

namespace PC_CPQ\Core\Pricing;

if ( ! defined( 'ABSPATH' ) )
	exit;

interface Pricing_Strategy_Interface
{
	public function calculate( Pricing_Context $context ): Pricing_Result;
}
