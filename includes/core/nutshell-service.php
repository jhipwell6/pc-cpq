<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Nutshell_Service
{
	private static $api_user = 'quotes@spc1925.com';
	private static $api_key = 'dd6c0be6bcf56fc743aeb7e52c4649e539e447ec';
	private $api;
	private $post_id;
	private $entry;
	private $new_contact_id;
	private $new_account_id;
	private $lead_id;
	private $nutshell_id;
	private $Lead;

	public function __construct( $post_id )
	{
		$this->post_id = $post_id;
		$this->Lead = PC_CPQ()->lead( $post_id );
		$this->connect();
	}

	public function maybe_send_lead( $entry, $form = null, $skip_validation = false )
	{
		$this->entry = $entry;

		// bail if the lead is not valid
		if ( ! $skip_validation ) {
			if ( ! $this->is_valid_lead() )
				return false;
		}

		// create the contact, account, and lead in Nutshell
		$this->process_lead();
	}

	public function fix_lead_ids()
	{
		$external_id = get_field( 'external_id', $this->post_id );
		$nutshell_id = get_field( 'nutshell_id', $this->post_id );

		if ( ! $nutshell_id && $external_id ) {
			$lead = $this->api->call( 'getLead', array( 'leadId' => $external_id ) );
			if ( $lead ) {
				$this->lead_id = $lead->id;
				$this->nutshell_id = $this->format_lead_id( $lead );
				$this->save_lead_ids();
			}
		}
	}

	public function update_lead_status()
	{
		if ( $this->Lead->get_external_id() ) {
			$lead = $this->api->call( 'getLead', array( 'leadId' => $this->Lead->get_external_id() ) );
		}
	}

	public function get_milestones()
	{
		if ( $this->Lead->get_external_id() ) {
			$milestones = $this->api->call( 'findMilestones' );
		}
	}

	public function get_outcomes()
	{
		if ( $this->Lead->get_external_id() ) {
			$outcomes = $this->api->call( 'findLead_Outcomes' );
		}
	}

	/*
	 * Private Methods
	 */

	private function connect()
	{
		if ( null === $this->api ) {
			$this->api = new \NutshellApi( self::$api_user, self::$api_key );
		}
	}

	private function process_lead()
	{
		// create the contact
		$this->create_contact();

		// create the account
		$this->create_account();

		// create the lead
		$this->create_lead();

		// save the lead ids
		$this->save_lead_ids();
	}

	/*
	 * Create a new contact and save its ID to $this->new_contact_id
	 */

	private function create_contact()
	{
		$params = array(
			'contact' => array()
		);

		$name = $this->get_prop( 1 ) . ' ' . $this->get_prop( 2 );
		$phone = $this->get_prop( 26 );
		$email = $this->get_prop( 3 );

		if ( $name && ( $name != '' || $name != ' ' ) ) {
			$params['contact']['name'] = $name;
		}

		if ( $phone && $phone != '' ) {
			$params['contact']['phone'] = array( $phone );
		}

		if ( $email && $email != '' ) {
			$params['contact']['email'] = array( $email );
		}

		$new_contact = $this->api->call( 'newContact', $params );
		$this->new_contact_id = $new_contact->id;
	}

	/*
	 * Create a new account that includes the contact we just added
	 */

	private function create_account()
	{
		$params = array(
			'account' => array(
				'contacts' => array(
					array(
						'id' => $this->new_contact_id,
						'relationship' => 'Contact Form Lead'
					)
				)
			)
		);

		$name = $this->get_prop( 4 );
		$phone = $this->get_prop( 26 );

		if ( $name && $name != '' ) {
			$params['account']['name'] = $name;
		}
		if ( $phone && $phone != '' ) {
			$params['account']['phone'] = array( $phone );
		}

		$new_account = $this->api->call( 'newAccount', $params );
		$this->new_account_id = $new_account->id;
	}

	/*
	 * Create a lead that includes the account we just added
	 */

	private function create_lead()
	{
		$note = (bool) $this->get_prop( 13 ) ? $this->get_prop( 13 ) : 'N/A';
		$params = array(
			'lead' => array(
				'primaryAccount' => array( 'id' => $this->new_account_id ),
//				'name' => 'Q: ' . $this->Lead->get_quote_number(),
				'confidence' => 70,
				'contacts' => array(
					array(
						'relationship' => 'First Contact',
						'id' => $this->new_contact_id,
					),
				),
				'note' => $note,
			),
		);

		$new_lead = $this->api->call( 'newLead', $params );
		$this->lead_id = $new_lead->id;
		$this->nutshell_id = $this->format_lead_id( $new_lead );
	}

	private function save_lead_ids()
	{
		update_field( 'external_id', $this->lead_id, $this->post_id );
		update_field( 'nutshell_id', $this->nutshell_id, $this->post_id );
	}

	private function is_valid_lead()
	{
		return ( $this->is_lead_a_business() && $this->is_lead_functional_finishing() );
	}
	
	private function is_lead_a_business()
	{
		if ( $this->Lead->get_business() == 'I am a business' || $this->Lead->get_business() == 'A business' ) {
			return true;
		}
		
		if ( $this->get_prop( 5 ) == 'I am a business' || $this->get_prop( 5 ) == 'A business' ) {
			return true;
		}
		
		return false;
	}
	
	private function is_lead_functional_finishing()
	{
		if ( $this->Lead->get_finishing_type() == 'Functional Finishing' ) {
			return true;
		}
		
		if ( $this->get_prop( 10 ) == 'Functional Finishing' ) {
			return true;
		}
		
		return false;
	}

	private function format_lead_id( $lead )
	{
		return str_replace( '/lead/', '', $lead->htmlUrlPath );
	}

	private function get_prop( $prop )
	{
		return rgar( $this->entry, $prop );
	}

}
