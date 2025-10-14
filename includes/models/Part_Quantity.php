<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Quantity extends Repeater_Model
{
	protected $break_point;

	public function get_break_point()
	{
		if ( null === $this->break_point ) {
			$break_point = (int) $this->get_meta( 'break_point' );
			$this->break_point = $break_point > 0 ? $break_point : 1;
		}

		return $this->break_point;
	}

}
