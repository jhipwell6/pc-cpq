<?php

namespace PC_CPQ\Controllers\Admin;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Helpers\CSV_Import_Export_Options;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Processes_Admin extends MVC_Controller_Registry
{
	
	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		$operations = new CSV_Import_Export_Options( 'operations', 'process-options', '\PC_CPQ\Models\Settings\Operation' );
	}
}

PC_CPQ_Processes_Admin::instance();