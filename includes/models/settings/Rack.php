<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Rack extends Repeater_Model
{
	protected $name;
	protected $size_limit;
	protected $weight_limit;
	protected $piece_count;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_size_limit()
	{
		return $this->get_prop( 'size_limit' );
	}
	
	public function get_weight_limit()
	{
		return $this->get_prop( 'weight_limit' );
	}
	
	public function get_piece_count()
	{
		return $this->get_prop( 'piece_count' );
	}
}