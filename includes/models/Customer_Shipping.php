<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Customer_Shipping extends Repeater_Model
{
	protected $shipping_street_address;
	protected $shipping_street_address_2;
	protected $shipping_city;
	protected $shipping_state;
	protected $shipping_zip;
	protected $shipping_country;
	
	public function get_shipping_street_address()
	{
		return $this->get_prop( 'shipping_street_address' );
	}
	
	public function get_shipping_street_address_2()
	{
		return $this->get_prop( 'shipping_street_address_2' );
	}
	
	public function get_shipping_city()
	{
		return $this->get_prop( 'shipping_city' );
	}
	
	public function get_shipping_state()
	{
		return $this->get_prop( 'shipping_state' );
	}
	
	public function get_shipping_zip()
	{
		return $this->get_prop( 'shipping_zip' );
	}
	
	public function get_shipping_country()
	{
		return $this->get_prop( 'shipping_country' );
	}
}
