<?php

namespace PC_CPQ\Controllers\Admin;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use PC_CPQ\Helpers\CSV_Import_Export_Options;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Plating_Admin extends MVC_Controller_Registry
{

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		$racks = new CSV_Import_Export_Options( 'racks', 'plating-options', '\PC_CPQ\Models\Settings\Rack' );
		$barrels = new CSV_Import_Export_Options( 'barrels', 'plating-options', '\PC_CPQ\Models\Settings\Barrel' );
		$lines = new CSV_Import_Export_Options( 'lines', 'plating-options', '\PC_CPQ\Models\Settings\Line' );
		$plating_metals = new CSV_Import_Export_Options( 'plating_metals', 'plating-options', '\PC_CPQ\Models\Settings\Plating_Metal' );
		$metals = new CSV_Import_Export_Options( 'metals', 'plating-options', '\PC_CPQ\Models\Settings\Metal' );
	}

}

PC_CPQ_Plating_Admin::instance();
