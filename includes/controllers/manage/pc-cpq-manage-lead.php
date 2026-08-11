<?php

namespace PC_CPQ\Controllers\Manage;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \GFAPI;
use \PC_CPQ\Core\Nutshell_Service;
use \PC_CPQ\Helpers\Form_Handler;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Manage_Lead extends MVC_Controller_Registry
{

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		$lead_actions = array(
			'send_quote' => 'send_quote',
			'preview_quote' => 'preview_quote',
			'requote' => 'requote',
			'delete_lead' => 'delete_lead',
			'clone_lead' => 'clone_lead',
			'edit_lead' => 'edit_lead',
			'send_message' => 'send_message',
			'add_part' => 'add_part',
			'clone_part' => 'clone_part',
			'delete_part' => 'delete_part',
			'add_part_quantity' => 'add_part_quantity',
			'delete_part_quantity' => 'delete_part_quantity',
			'add_part_process' => 'add_part_process',
			'refresh_part_process_views' => 'refresh_part_process_views',
			'add_part_operation' => 'add_part_operation',
			'delete_part_process' => 'delete_part_process',
			'delete_part_operation' => 'delete_part_operation',
			'send_to_nutshell' => 'send_to_nutshell',
			'search_customers' => 'search_customers',
			'save_customer' => 'save_customer',
		);

		foreach ( $lead_actions as $hook => $method ) {
			add_action( 'wp_ajax_' . $hook, array( $this, 'authorize_quote_management' ), 0 );
			add_action( 'wp_ajax_' . $hook, array( $this, $method ) );
		}
	}

	public function authorize_quote_management()
	{
		PC_CPQ()->User()->assert_can_manage_quotes();
	}

	public function send_quote()
	{
		// Get form data
		$send_quote_form = Form_Handler::get_form_data( 'quote' );
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );

		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $send_quote_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'send_quote_nonce', 'send_quote', $send_quote_form );

		$Lead = PC_CPQ()->lead( $lead_id );
		$Lead->update_prop( 'quote_pricing_type', $send_quote_form['quote_pricing_type'] );
		$this->apply_selected_fees_to_lead( $Lead, $send_quote_form );
		PC_CPQ()->Quote( $Lead )->send_quote( $send_quote_form['recipients'] );

		$html = PC_CPQ()->view( 'manage/partials/quote-details', array( 'Lead' => $Lead ) );
		wp_send_json_success( array(
			'html' => $html,
		) );
	}

	public function send_quote_with_validator()
	{
		$send_quote_form = Form_Handler::get_form_data( 'quote' );
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );

		Form_Handler::validate_form_data( $send_quote_form );
		Form_Handler::pre_validate_form( 'send_quote_nonce', 'send_quote', $send_quote_form );

		$Lead = PC_CPQ()->lead( $lead_id );
		$Quote = PC_CPQ()->Quote( $Lead );

		$Lead->update_prop( 'quote_pricing_type', $send_quote_form['quote_pricing_type'] );
		$this->apply_selected_fees_to_lead( $Lead, $send_quote_form );

		try {

			// Domain enforcement lives in the model now
			$Quote->assert_can_send();

			$Quote->send_quote( $send_quote_form['recipients'] );
		} catch ( \Exception $e ) {

			wp_send_json_error( [
				'message' => $e->getMessage(),
				'issues' => $Lead->get_override_issues(), // model method
			] );

			return;
		}

		$html = PC_CPQ()->view(
			'manage/partials/quote-details',
			array( 'Lead' => $Lead )
		);

		wp_send_json_success( [
			'html' => $html,
		] );
	}

	public function preview_quote()
	{
		// Get form data
		$preview_quote_form = Form_Handler::get_form_data( 'preview' );
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );

		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $preview_quote_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'preview_quote_nonce', 'preview_quote', $preview_quote_form );

		$Lead = PC_CPQ()->lead( $lead_id );
		$Lead->update_prop( 'quote_pricing_type', $preview_quote_form['quote_pricing_type'] );
		$this->apply_selected_fees_to_lead( $Lead, $preview_quote_form );
		$url = PC_CPQ()->Quote( $Lead )->get_preview_quote_url();

		wp_send_json_success( array(
			'url' => $url,
		) );
	}

	public function requote()
	{
		$requote_form = array(
			'requote_nonce' => Form_Handler::filter_input( 'requote_nonce' ),
		);
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );

		Form_Handler::validate_form_data( $requote_form );
		Form_Handler::pre_validate_form( 'requote_nonce', 'requote', $requote_form );

		$Lead = PC_CPQ()->lead( $lead_id );
		PC_CPQ()->Quote( $Lead )->requote();
		$Lead->clear_override();

		$this->render_lead_for_js( $Lead );
	}

	public function send_message()
	{
		// Get form data
		$send_message_form = Form_Handler::get_form_data( 'message' );
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );

		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $send_message_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'send_message_nonce', 'send_message', $send_message_form );

		$Lead = PC_CPQ()->lead( $lead_id );
		PC_CPQ()->Quote( $Lead )->send_message( $send_message_form['recipients'], $send_message_form['message'] );

		wp_send_json_success();
	}

	public function delete_lead()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Lead->delete();

		wp_send_json_success();
	}

	public function clone_lead()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Cloned_Lead = $Lead->clone_to_new();

		wp_send_json_success( array(
			'leadID' => $Cloned_Lead->get_id(),
			'url' => $Cloned_Lead->get_manage_url(),
		) );
	}

	public function edit_lead()
	{
		// Get form data
		$edit_lead_form = Form_Handler::get_form_data( 'edit_lead_form' );

		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_lead_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_lead_nonce', 'edit_lead', $edit_lead_form );
		$this->assert_lead_editable( $edit_lead_form['lead_id'] );

		// Save the data
		$Lead = PC_CPQ()->lead( $edit_lead_form['lead_id'] );
		$this->process_lead_data( $edit_lead_form, $Lead );
		$Lead->set_props( $edit_lead_form );
		$Lead = $Lead->save();
		$Lead->sync_legacy_certification_fee_flag();
		$Lead->clear_override();

		$this->render_lead_for_js( $Lead );
	}

	public function add_contact()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$Customer = PC_CPQ()->customer( $customer_id );

		// handle existing contacts
		$this->refresh_contact_data( $Customer );

		// add new contact
		$Customer->add_Contact();

		$this->render_contacts_for_js( $Customer );
	}

	public function add_part()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$Lead = PC_CPQ()->lead( $lead_id );

		// handle existing parts
		$this->refresh_part_data( $Lead );

		// add new part
		$Lead->add_Part( null, [], true, true );
		$Lead->clear_override();

		$this->render_parts_for_js( $Lead );
	}

	public function clone_part()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$clone_part = Form_Handler::filter_input( 'clone_part' );
		$Lead = PC_CPQ()->lead( $lead_id );

		$Cloned_Part = $Lead->get_Part( $clone_part );

		// handle existing parts
		$this->refresh_part_data( $Lead );

		// add new part
		$Lead->add_Part( null, $Cloned_Part->get_clone_data() );

		$this->render_parts_for_js( $Lead );
	}

	public function delete_part()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$index = Form_Handler::filter_input( 'index' );
		$Lead = PC_CPQ()->lead( $lead_id );

		// handle existing parts
		$this->refresh_part_data( $Lead );

		// delete part
		$Lead->delete_Part( $index );

		$this->render_parts_for_js( $Lead );
	}

	public function add_part_quantity()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing quantities
		$this->refresh_part_quantity_data( $Part, $part_id );

		// add new quantity
		$Part->add_Quantity();

		$this->render_part_quantities_for_js( $Part, $part_id );
	}

	public function delete_part_quantity()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$index = Form_Handler::filter_input( 'index' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing quantities
		$this->refresh_part_quantity_data( $Part, $part_id );

		// delete quantity
		$Part->delete_Quantity( $index );

		$this->render_part_quantities_for_js( $Part, $part_id );
	}

	public function add_part_process()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing processes
		$this->refresh_part_process_data( $Part, $part_id );

		// add new process
		$Part->add_Process();

		$this->render_processes_for_js( $Part, $part_id );
	}

	public function delete_part_process()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$index = Form_Handler::filter_input( 'index' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing parts
		$this->refresh_part_process_data( $Part, $part_id );

		// delete part
		$Part->delete_Process( $index );

		$this->render_processes_for_js( $Part, $part_id );
	}

	public function refresh_part_process_views()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		$this->refresh_part_process_data( $Part, $part_id );

		$processes_html = PC_CPQ()->view( 'manage/partials/part-tab-processes', array( 'Part' => $Part, 'i' => $part_id ) );
		$operations_html = PC_CPQ()->view( 'manage/partials/part-tab-plating', array( 'Part' => $Part, 'i' => $part_id ) );

		wp_send_json_success( array(
			'i' => $part_id,
			'processesHtml' => minify_html( $processes_html ),
			'operationsHtml' => minify_html( $operations_html ),
		) );
	}

	public function add_part_operation()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing processes
		$this->refresh_part_operation_data( $Part, $part_id );

		// add new process
		$Part->add_Operation();

		$this->render_operations_for_js( $Part, $part_id );
	}

	public function delete_part_operation()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$part_id = Form_Handler::filter_input( 'part_id' );
		$index = Form_Handler::filter_input( 'index' );
		$Lead = PC_CPQ()->lead( $lead_id );
		$Parts = $Lead->get_Parts();
		$Part = $Parts[$part_id];

		// handle existing parts
		$this->refresh_part_operation_data( $Part, $part_id );

		// delete part
		$Part->delete_Operation( $index );

		$this->render_operations_for_js( $Part, $part_id );
	}

	public function send_to_nutshell()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$Lead = PC_CPQ()->lead( $lead_id );
		$nutshell = new Nutshell_Service( $lead_id );

		if ( ! $Lead->get_form_entry_id() ) {
			$Lead->maybe_create_entry();
//			wp_send_json_error();
		}

		$entry = GFAPI::get_entry( $Lead->get_form_entry_id() );
		$nutshell->maybe_send_lead( $entry );

		$html = PC_CPQ()->view( 'manage/fields/nutshell-input', [ 'Lead' => $Lead ] );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'leadID' => $Lead->get_id(),
			'entryID' => $Lead->get_form_entry_id(),
		) );
	}

	public function search_customers()
	{
		$q = sanitize_text_field( $_GET['q'] ?? '' );
		$page = max( 1, intval( $_GET['page'] ?? 1 ) );
		$per_page = 20;

		$args = [
			'post_type' => 'customer',
			'post_status' => 'publish',
			's' => $q,
			'orderby' => 'title',
			'order' => 'ASC',
			'posts_per_page' => $per_page,
			'paged' => $page,
			'fields' => 'ids',
		];

		$query = new \WP_Query( $args );
		$results = array_map( function ( $id ) {
			return [
				'id' => $id,
				'text' => get_the_title( $id ),
			];
		}, $query->posts );

		wp_send_json( [
			'results' => $results,
			'pagination' => [ 'more' => ($page * $per_page) < (int) $query->found_posts ],
		] );
	}

	public function save_customer()
	{
		$lead_id = Form_Handler::filter_input( 'lead_id' );
		$this->assert_lead_editable( $lead_id );
		$found_customer = Form_Handler::filter_input( 'foundCustomer' );
		$create_customer = Form_Handler::filter_input( 'createCustomer' );
		$Lead = PC_CPQ()->lead( $lead_id );

		if ( $found_customer && $found_customer != 'null' ) {
			$Customer = PC_CPQ()->customer( $found_customer );
			$Lead->update_prop( 'raw_customer', $found_customer );
			$Lead->update_prop( 'company', $Customer->get_name() );
		} else if ( $create_customer ) {
			$Lead->update_prop( 'company', $create_customer );
			$Customer = $Lead->create_customer_from_lead();
			$Lead->update_prop( 'raw_customer', $Customer->get_id() );
		}

		$this->render_lead_for_js( $Lead );
	}

	/* Private Helpers */

	private function render_lead_for_js( $Lead )
	{
		$html = PC_CPQ()->view( 'manage/form-edit-lead', array(
			'Lead' => $Lead,
			'post_lock' => PC_CPQ()->Post_Lock()->get_editor_lock_data( $Lead->get_id(), 'lead' ),
		) );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'leadID' => $Lead->get_id(),
		) );
	}

	private function render_parts_for_js( $Lead )
	{
		$html = PC_CPQ()->view( 'manage/partials/lead-parts', array( 'Lead' => $Lead ) );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'partsCount' => $Lead->get_Parts_count(),
		) );
	}

	private function render_part_quantities_for_js( $Part, $i )
	{
		$html = PC_CPQ()->view( 'manage/partials/part-tab-quantities', array( 'Part' => $Part, 'i' => $i ) );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'i' => $i,
			'quantitiesCount' => $Part->get_Quantities_count(),
		) );
	}

	private function render_processes_for_js( $Part, $i )
	{
		$html = PC_CPQ()->view( 'manage/partials/part-tab-processes', array( 'Part' => $Part, 'i' => $i ) );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'i' => $i,
			'processesCount' => $Part->get_Processes_count(),
		) );
	}

	private function render_operations_for_js( $Part, $i )
	{
		$html = PC_CPQ()->view( 'manage/partials/part-tab-plating', array( 'Part' => $Part, 'i' => $i ) );

		wp_send_json_success( array(
			'html' => minify_html( $html ),
			'i' => $i,
			'operationsCount' => $Part->get_Operations_count(),
		) );
	}

	private function refresh_part_data( &$Lead )
	{
		$live_parts = Form_Handler::get_form_data( 'live_parts' );
		if ( $live_parts ) {
			$Lead->set_raw_parts( $live_parts['raw_parts'] );
		}
		$Lead->refresh_Parts();
	}

	private function refresh_part_quantity_data( &$Part, $part_id )
	{
		$live_part_quantities = Form_Handler::get_form_data( 'live_part_quantities' );
		if ( $Part && $live_part_quantities ) {
			$Part->set_raw_quantities( $live_part_quantities['raw_parts'][$part_id]['quantities'] );
			$Part->refresh_Quantities();
		}
	}

	private function refresh_part_process_data( &$Part, $part_id )
	{
		$live_part_processes = Form_Handler::get_form_data( 'live_part_processes' );
		if ( $Part && $live_part_processes ) {
			$Part->set_raw_processes( $live_part_processes['raw_parts'][$part_id]['processes'] );
			$Part->refresh_Processes();
		}
	}

	private function refresh_part_operation_data( &$Part, $part_id )
	{
		$live_part_operations = Form_Handler::get_form_data( 'live_part_operations' );
		if ( $Part && $live_part_operations ) {
			$Part->set_raw_routing( $live_part_operations['raw_parts'][$part_id]['routing'] );
			$Part->refresh_Operations();
		}
	}

	private function process_lead_data( &$data, $Lead = null )
	{
		if ( isset( $data['status'] ) && $data['status'] == 'New' ) {
			$data['status'] = 'Pending';
		}

		if ( ! isset( $data['title'] ) ) {
			$data['first_name'] . ' ' . $data['last_name'] . ' - ' . date( 'm/d/Y' );
		}

		if ( ! isset( $data['certification'] ) ) {
			$data['certification'] = 0;
		}

		if ( ! isset( $data['include_metal_factor'] ) ) {
			$data['include_metal_factor'] = 0;
		}

		if ( isset( $data['raw_parts'] ) && ! empty( $data['raw_parts'] ) ) {
			$i = 0;
			foreach ( $data['raw_parts'] as $raw_part ) {
				if ( isset( $data['raw_parts'][$i]['pricing'] ) && isset( $data['raw_parts'][$i]['pricing'][0] ) ) {
					$data['raw_parts'][$i]['pricing'] = array_first( $raw_part['pricing'] );
				}
				$i ++;
			}
		}
	}

	private function prepare_quote_fees( $form_data )
	{
		$selected_fees = isset( $form_data['selected_fees'] ) ? array_filter( (array) $form_data['selected_fees'] ) : array();

		return array_map( function( $fee ) {
			return array(
				'fee' => $fee,
			);
		}, array_values( $selected_fees ) );
	}

	private function apply_selected_fees_to_lead( $Lead, $form_data )
	{
		$Lead->update_prop( 'fees', $this->prepare_quote_fees( $form_data ) );
		$Lead->sync_legacy_certification_fee_flag();
	}

	private function assert_lead_editable( $lead_id )
	{
		$lead_id = absint( $lead_id );
		if ( ! $lead_id ) {
			return;
		}

		PC_CPQ()->Post_Lock()->assert_editable( $lead_id, 'lead' );
	}
}

PC_CPQ_Manage_Lead::instance();
