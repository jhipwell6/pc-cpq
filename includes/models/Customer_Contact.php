<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Customer_Contact extends Repeater_Model
{
	protected $name;
	protected $phone;
	protected $email;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}

	public function get_phone()
	{
		return $this->get_prop( 'phone' );
	}

	public function get_email()
	{
		return $this->get_prop( 'email' );
	}

}
