<?php

namespace PC_CPQ\Models\Settings;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Email_Template extends Repeater_Model
{
	protected $name;
	protected $template;

	public function get_name()
	{
		return $this->get_prop( 'name' );
	}
	
	public function get_template()
	{
		return $this->get_prop( 'template' );
	}
}