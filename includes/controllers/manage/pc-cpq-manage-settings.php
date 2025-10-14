<?php

namespace PC_CPQ\Controllers\Manage;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Helpers\Form_Handler;
use \PC_CPQ\Helpers\CSV_Import_Export_Options;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Manage_Settings extends MVC_Controller_Registry
{

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{		
		// handle edit settings form
		add_action( 'wp_ajax_edit_settings_parts', array( $this, 'edit_settings_parts' ) );
		add_action( 'wp_ajax_edit_settings_quotes', array( $this, 'edit_settings_quotes' ) );
		add_action( 'wp_ajax_edit_settings_plating', array( $this, 'edit_settings_plating' ) );
		add_action( 'wp_ajax_edit_settings_processes', array( $this, 'edit_settings_processes' ) );
		add_action( 'wp_ajax_edit_settings_templates', array( $this, 'edit_settings_templates' ) );
		
		// handle email templates
		add_action( 'wp_ajax_add_email_template', array( $this, 'add_email_template' ) );
		add_action( 'wp_ajax_delete_email_template', array( $this, 'delete_email_template' ) );
		
		// handle metals
		add_action( 'wp_ajax_add_metal', array( $this, 'add_metal' ) );
		add_action( 'wp_ajax_delete_metal', array( $this, 'delete_metal' ) );
		
		// handle plating_metals
		add_action( 'wp_ajax_add_plating_metal', array( $this, 'add_plating_metal' ) );
		add_action( 'wp_ajax_delete_plating_metal', array( $this, 'delete_plating_metal' ) );
		
		// handle lines
		add_action( 'wp_ajax_add_line', array( $this, 'add_line' ) );
		add_action( 'wp_ajax_delete_line', array( $this, 'delete_line' ) );
		
		// handle barrels
		add_action( 'wp_ajax_add_barrel', array( $this, 'add_barrel' ) );
		add_action( 'wp_ajax_delete_barrel', array( $this, 'delete_barrel' ) );
		
		// handle racks
		add_action( 'wp_ajax_add_rack', array( $this, 'add_rack' ) );
		add_action( 'wp_ajax_delete_rack', array( $this, 'delete_rack' ) );
		
		// handle operations
		add_action( 'wp_ajax_add_operation', array( $this, 'add_operation' ) );
		add_action( 'wp_ajax_delete_operation', array( $this, 'delete_operation' ) );
		
		// handle imports & exports
		add_action( 'wp_ajax_import_settings', array( $this, 'import_settings' ) );
		add_action( 'wp_ajax_export_settings', array( $this, 'export_settings' ) );
		
	}
	
	public function import_settings()
	{
		// Get form data
		$type = Form_Handler::filter_input( 'type' );
		$file_name = Form_Handler::filter_input( 'file_name' );
		$file = Form_Handler::get_file_data( $file_name );
		if ( $file ) {
			CSV_Import_Export_Options::import( $type );
		}
		
		wp_send_json_success();
	}
	
	public function export_settings()
	{
		// Get form data
		$type = Form_Handler::filter_input( 'type' );
		$model = self::get_model_from_slug( $type );
		if ( $type && $model ) {
			CSV_Import_Export_Options::export( $type, $model );
		}
		
		wp_send_json_success();
	}

	public function edit_settings_parts()
	{
		// Get form data
		$edit_settings_parts_form = Form_Handler::get_form_data( 'edit_settings_parts_form' );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_settings_parts_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_settings_parts_nonce', 'edit_settings_parts', $edit_settings_parts_form );

		// Save the data
		$Settings = PC_CPQ()->Settings();
		foreach ( $edit_settings_parts_form as $field => $value ) {
			$Settings->update_prop( $field, $value );
		}
		
		$this->render_settings_for_js( 'parts', $Settings );
	}
	
	public function edit_settings_quotes()
	{
		// Get form data
		$edit_settings_quotes_form = Form_Handler::get_form_data( 'edit_settings_quotes_form' );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_settings_quotes_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_settings_quotes_nonce', 'edit_settings_quotes', $edit_settings_quotes_form );

		// Save the data
		$Settings = PC_CPQ()->Settings();
		foreach ( $edit_settings_quotes_form as $field => $value ) {
			$Settings->update_prop( $field, $value );
		}
		
		$this->render_settings_for_js( 'quotes', $Settings );
	}
	
	public function edit_settings_plating()
	{
		// Get form data
		$edit_settings_plating_form = Form_Handler::get_form_data( 'edit_settings_plating_form' );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_settings_plating_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_settings_plating_nonce', 'edit_settings_plating', $edit_settings_plating_form );

		// Save the data
		$Settings = PC_CPQ()->Settings();
		$this->process_settings_data( $edit_settings_plating_form );
		foreach ( $edit_settings_plating_form as $field => $value ) {
			$Settings->update_prop( $field, $value );
		}
		
		$this->render_settings_for_js( 'plating', $Settings );
	}
	
	public function edit_settings_processes()
	{		
		// Get form data
		$edit_settings_processes_form = Form_Handler::get_form_data( 'edit_settings_processes_form', true );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_settings_processes_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_settings_processes_nonce', 'edit_settings_processes', $edit_settings_processes_form );

		// Save the data
		$Settings = PC_CPQ()->Settings();
		foreach ( $edit_settings_processes_form as $field => $value ) {
			$Settings->update_prop( $field, $value );
		}
		
		$this->render_settings_for_js( 'processes', $Settings );
	}
	
	public function edit_settings_templates()
	{
		// Get form data
		$edit_settings_templates_form = Form_Handler::get_form_data( 'edit_settings_templates_form' );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_settings_templates_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_settings_templates_nonce', 'edit_settings_templates', $edit_settings_templates_form );

		// Save the data
		$Settings = PC_CPQ()->Settings();
		foreach ( $edit_settings_templates_form as $field => $value ) {
			$Settings->update_prop( $field, $value );
		}
		
		$this->render_settings_for_js( 'templates', $Settings );
	}
	
	private function render_settings_for_js( $page, $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/form-' . $page, array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
		) );
	}
	
	public function add_email_template()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing email_templates
		$this->refresh_email_template_data( $Settings );
		
		// add new email_template
		$Settings->add_Email_Template();
		
		$this->render_email_templates_for_js( $Settings );
	}

	public function delete_email_template()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing email_templates
		$this->refresh_email_template_data( $Settings );
		
		// delete email_template
		$Settings->delete_Email_Template( $index );
		
		$this->render_email_templates_for_js( $Settings );
	}
	
	private function render_email_templates_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/partials/email-templates', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'emailTemplatesCount' => $Settings->get_Email_Templates_count(),
		) );
	}
	
	private function refresh_email_template_data( &$Settings )
	{
		$live_email_templates = Form_Handler::get_form_data( 'live_email_templates' );
		$Settings->set_raw_email_templates( $live_email_templates['raw_email_templates'] );
		$Settings->refresh_Email_Templates();
	}
	
	public function add_metal()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing metals
		$this->refresh_metal_data( $Settings );
		
		// add new metal
		$Settings->add_Metal();
		
		$this->render_metals_for_js( $Settings );
	}

	public function delete_metal()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing metals
		$this->refresh_metal_data( $Settings );
		
		// delete metal
		$Settings->delete_Metal( $index );
		
		$this->render_metals_for_js( $Settings );
	}
	
	private function render_metals_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/metals', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'metalsCount' => $Settings->get_Metals_count(),
		) );
	}
	
	private function refresh_metal_data( &$Settings )
	{
		$live_metals = Form_Handler::get_form_data( 'live_metals' );
		$Settings->set_raw_metals( $live_metals['raw_metals'] );
		$Settings->refresh_Metals();
	}
	
	public function add_plating_metal()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing plating_metals
		$this->refresh_plating_metal_data( $Settings );
		
		// add new plating_metal
		$Settings->add_Plating_Metal();
		
		$this->render_plating_metals_for_js( $Settings );
	}

	public function delete_plating_metal()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing plating_metals
		$this->refresh_plating_metal_data( $Settings );
		
		// delete plating_metal
		$Settings->delete_Plating_Metal( $index );
		
		$this->render_plating_metals_for_js( $Settings );
	}
	
	private function render_plating_metals_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/plating-metals', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'platingMetalsCount' => $Settings->get_Plating_Metals_count(),
		) );
	}
	
	private function refresh_plating_metal_data( &$Settings )
	{
		$live_plating_metals = Form_Handler::get_form_data( 'live_plating_metals' );
		$Settings->set_raw_plating_metals( $live_plating_metals['raw_plating_metals'] );
		$Settings->refresh_Plating_Metals();
	}
	
	public function add_line()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing lines
		$this->refresh_line_data( $Settings );
		
		// add new line
		$Settings->add_Line();
		
		$this->render_lines_for_js( $Settings );
	}

	public function delete_line()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing lines
		$this->refresh_line_data( $Settings );
		
		// delete line
		$Settings->delete_Line( $index );
		
		$this->render_lines_for_js( $Settings );
	}
	
	private function render_lines_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/lines', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'linesCount' => $Settings->get_Lines_count(),
		) );
	}
	
	private function refresh_line_data( &$Settings )
	{
		$live_lines = Form_Handler::get_form_data( 'live_lines' );
		$Settings->set_raw_lines( $live_lines['raw_lines'] );
		$Settings->refresh_Lines();
	}
	
	public function add_barrel()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing barrels
		$this->refresh_barrel_data( $Settings );
		
		// add new barrel
		$Settings->add_Barrel();
		
		$this->render_barrels_for_js( $Settings );
	}

	public function delete_barrel()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing barrels
		$this->refresh_barrel_data( $Settings );
		
		// delete barrel
		$Settings->delete_Barrel( $index );
		
		$this->render_barrels_for_js( $Settings );
	}
	
	private function render_barrels_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/barrels', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'barrelsCount' => $Settings->get_Barrels_count(),
		) );
	}
	
	private function refresh_barrel_data( &$Settings )
	{
		$live_barrels = Form_Handler::get_form_data( 'live_barrels' );
		$Settings->set_raw_barrels( $live_barrels['raw_barrels'] );
		$Settings->refresh_Barrels();
	}
	
	public function add_rack()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing racks
		$this->refresh_rack_data( $Settings );
		
		// add new rack
		$Settings->add_Rack();
		
		$this->render_racks_for_js( $Settings );
	}

	public function delete_rack()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing racks
		$this->refresh_rack_data( $Settings );
		
		// delete rack
		$Settings->delete_Rack( $index );
		
		$this->render_racks_for_js( $Settings );
	}
	
	private function render_racks_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/racks', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'racksCount' => $Settings->get_Racks_count(),
		) );
	}
	
	private function refresh_rack_data( &$Settings )
	{
		$live_racks = Form_Handler::get_form_data( 'live_racks' );
		$Settings->set_raw_racks( $live_racks['raw_racks'] );
		$Settings->refresh_Racks();
	}
	
	public function add_operation()
	{
		$Settings = PC_CPQ()->Settings();
		
		// handle existing operations
		$this->refresh_operation_data( $Settings );
		
		// add new operation
		$Settings->add_Operation();
		
		$this->render_operations_for_js( $Settings );
	}

	public function delete_operation()
	{
		$index = Form_Handler::filter_input( 'index' );
		$Settings = PC_CPQ()->Settings();
		
		// handle existing operations
		$this->refresh_operation_data( $Settings );
		
		// delete operation
		$Settings->delete_Operation( $index );
		
		$this->render_operations_for_js( $Settings );
	}
	
	private function render_operations_for_js( $Settings )
	{
		$html = PC_CPQ()->view( 'manage/settings/partials/operations', array( 'Settings' => $Settings ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'operationsCount' => $Settings->get_Operations_count(),
		) );
	}
	
	private function refresh_operation_data( &$Settings )
	{
		$live_operations = Form_Handler::get_form_data( 'live_operations' );
		$Settings->set_raw_operations( $live_operations['raw_operations'] );
		$Settings->refresh_Operations();
	}
	
	static public function get_model_from_slug( $slug )
	{
		$mapping = array(
			'metals' => '\PC_CPQ\Models\Settings\Metal',
			'plating_metals' => '\PC_CPQ\Models\Settings\Plating_Metal',
			'lines' => '\PC_CPQ\Models\Settings\Line',
			'barrels' => '\PC_CPQ\Models\Settings\Barrel',
			'racks' => '\PC_CPQ\Models\Settings\Rack',
			'operations' => '\PC_CPQ\Models\Settings\Operation',
		);
		
		return isset( $mapping[ $slug ] ) ? $mapping[ $slug ] : false;
	}
	
	private function process_settings_data( &$data )
	{
		if ( isset( $data['raw_plating_metals'] ) && ! empty( $data['raw_plating_metals'] ) ) {
			$i = 0;
			foreach ( $data['raw_plating_metals'] as $raw_plating_metal ) {
				if ( ! isset( $data['raw_plating_metals'][ $i ]['unit_visible'] ) ) {
					$data['raw_plating_metals'][ $i ]['unit_visible'] = 0;
				}
				if ( ! isset( $data['raw_plating_metals'][ $i ]['precious_metal'] ) ) {
					$data['raw_plating_metals'][ $i ]['precious_metal'] = 0;
				}
				if ( ! isset( $data['raw_plating_metals'][ $i ]['hide'] ) ) {
					$data['raw_plating_metals'][ $i ]['hide'] = 0;
				}
				$i++;
			}
		}
	}
}

PC_CPQ_Manage_Settings::instance();