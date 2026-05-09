<?php

namespace PC_CPQ\Controllers\Manage;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Helpers\Form_Handler;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Manage_Customer extends MVC_Controller_Registry
{

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		$customer_actions = array(
			'delete_customer' => 'delete_customer',
			'edit_customer' => 'edit_customer',
			'add_contact' => 'add_contact',
			'delete_contact' => 'delete_contact',
			'add_shipping' => 'add_shipping',
			'delete_shipping' => 'delete_shipping',
		);

		foreach ( $customer_actions as $hook => $method ) {
			add_action( 'wp_ajax_' . $hook, array( $this, 'authorize_quote_management' ), 0 );
			add_action( 'wp_ajax_' . $hook, array( $this, $method ) );
		}
	}

	public function authorize_quote_management()
	{
		PC_CPQ()->User()->assert_can_manage_quotes();
	}
	
	public function delete_customer()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$this->assert_customer_editable( $customer_id );
		$Customer = PC_CPQ()->customer( $customer_id );
		$Customer->delete();
		
		wp_send_json_success();
	}

	public function edit_customer()
	{
		// Get form data
		$edit_customer_form = Form_Handler::get_form_data( 'edit_customer_form' );
		
		// Check for valid form data, bail if invalid
		Form_Handler::validate_form_data( $edit_customer_form );

		// Check for valid form action and nonce, bail if invalid
		Form_Handler::pre_validate_form( 'edit_customer_nonce', 'edit_customer', $edit_customer_form );
		$this->assert_customer_editable( $edit_customer_form['customer_id'] );

		// Save the data
		$Customer = PC_CPQ()->customer( $edit_customer_form['customer_id'] );
		$Customer->set_props( $edit_customer_form );
		$Customer = $Customer->save();
		
		$this->render_customer_for_js( $Customer );
	}
	
	private function render_customer_for_js( $Customer )
	{
		$html = PC_CPQ()->view( 'manage/form-edit-customer', array(
			'Customer' => $Customer,
			'post_lock' => PC_CPQ()->Post_Lock()->get_editor_lock_data( $Customer->get_id(), 'customer' ),
		) );
		
		wp_send_json_success( array(
			'html' => $html,
		) );
	}
	
	public function add_contact()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$this->assert_customer_editable( $customer_id );
		$Customer = PC_CPQ()->customer( $customer_id );
		
		// handle existing contacts
		$this->refresh_contact_data( $Customer );
		
		// add new contact
		$Customer->add_Contact();
		
		$this->render_contacts_for_js( $Customer );
	}

	public function delete_contact()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$this->assert_customer_editable( $customer_id );
		$index = Form_Handler::filter_input( 'index' );
		$Customer = PC_CPQ()->customer( $customer_id );
		
		// handle existing contacts
		$this->refresh_contact_data( $Customer );
		
		// delete contact
		$Customer->delete_Contact( $index );
		
		$this->render_contacts_for_js( $Customer );
	}
	
	private function render_contacts_for_js( $Customer )
	{
		$html = PC_CPQ()->view( 'manage/partials/customer-contacts', array( 'Customer' => $Customer ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'contactsCount' => $Customer->get_Contacts_count(),
		) );
	}
	
	public function add_shipping()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$this->assert_customer_editable( $customer_id );
		$Customer = PC_CPQ()->customer( $customer_id );
		
		// handle existing shipping
		$this->refresh_shipping_data( $Customer );
		
		// add new shipping
		$Customer->add_Shipping();
		
		$this->render_shipping_for_js( $Customer );
	}

	public function delete_shipping()
	{
		$customer_id = Form_Handler::filter_input( 'customer_id' );
		$this->assert_customer_editable( $customer_id );
		$index = Form_Handler::filter_input( 'index' );
		$Customer = PC_CPQ()->customer( $customer_id );
		
		// handle existing shipping
		$this->refresh_shipping_data( $Customer );
		
		// delete shipping
		$Customer->delete_Shipping( $index );
		
		$this->render_shipping_for_js( $Customer );
	}
	
	private function render_shipping_for_js( $Customer )
	{
		$html = PC_CPQ()->view( 'manage/partials/customer-shipping', array( 'Customer' => $Customer ) );
		
		wp_send_json_success( array(
			'html' => $html,
			'shippingCount' => $Customer->get_Shipping_count(),
		) );
	}
	
	private function refresh_contact_data( &$Customer )
	{
		$live_contacts = Form_Handler::get_form_data( 'live_contacts' );
		$Customer->set_raw_contacts( $live_contacts['raw_contacts'] );
		$Customer->refresh_Contacts();
	}
	
	private function refresh_shipping_data( &$Customer )
	{
		$live_shipping = Form_Handler::get_form_data( 'live_shipping' );
		$Customer->set_raw_shipping( $live_shipping['raw_shipping'] );
		$Customer->refresh_Shipping();
	}

	private function assert_customer_editable( $customer_id )
	{
		PC_CPQ()->Post_Lock()->assert_editable( $customer_id, 'customer' );
	}
}

PC_CPQ_Manage_Customer::instance();
