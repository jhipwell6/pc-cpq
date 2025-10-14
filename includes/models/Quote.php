<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

use \GFAPI;

class Quote
{
	const QUOTE_FORM_ID = 1;
	protected $Lead;

	public function __construct( \PC_CPQ\Models\Lead $Lead )
	{
		$this->Lead = $Lead;
		$this->maybe_create_entry();
		
		return $this;
	}
	
	public function send_quote( $recipients )
	{
		// trigger the notification
		$form = GFAPI::get_form( self::QUOTE_FORM_ID );
		$entry = GFAPI::get_entry( $this->Lead->get_form_entry_id() );
		GFAPI::update_entry_field( $this->Lead->get_form_entry_id(), 3, $recipients );
		GFAPI::send_notifications( $form, $entry, 'quote_created' );

		// update the lead
		$this->update_lead();
	}
	
	public function get_preview_quote_url()
	{		
		return $this->Lead->get_quote_pdf();
	}
	
	public function send_message( $recipients, $message )
	{
		// trigger the notification
		$form = GFAPI::get_form( self::QUOTE_FORM_ID );
		$entry = GFAPI::get_entry( $this->Lead->get_form_entry_id() );
		GFAPI::update_entry_field( $this->Lead->get_form_entry_id(), 3, $recipients );
		GFAPI::update_entry_field( $Lead->get_form_entry_id(), 34, $message );
		GFAPI::send_notifications( $form, $entry, 'message' );
	}
	
	public function update_lead()
	{
		$this->Lead->update_prop( 'sent', 1 );
		$this->Lead->update_prop( 'status', 'Quoted' );
		$this->Lead->update_prop( 'quote_date', strtotime('now') );
		$this->Lead->update_prop( 'follow_up_date', strtotime( '+ ' . PC_CPQ()->Settings()->get_follow_up_after() . ' days' ) );
		$this->Lead->update_prop( 'expiration_date', strtotime( '+ ' . PC_CPQ()->Settings()->get_quote_expires_after() . ' days' ) );
	}
	
	public function entry_exists()
	{
		return (bool) $this->Lead->get_form_entry_id();
	}
	
	private function maybe_create_entry()
	{
		if ( ! $this->entry_exists() ) {
			// create entry now
			$form_entry_id = self::create_entry( $this->Lead );
			$this->Lead->update_prop( 'form_entry_id', $form_entry_id );
		}
	}
	
	static public function create_entry( $Lead )
	{
		$date = date( 'Y-m-d H:i:s' );
		$entry_data = array(
			'form_id' => self::QUOTE_FORM_ID,
			'date_created' => $date,
			'date_updated' => $date,
			'is_starred' => 0,
			'is_read' => 0,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'source_url' => site_url(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'currency' => 'USD',
			'status' => 'active',
			'created_by' => get_current_user_id(),
			'is_authorized' => 'Yes',
			'1' => $Lead->get_first_name(),
			'2' => $Lead->get_last_name(),
			'3' => $Lead->get_email(),
			'4' => $Lead->get_company(),
			'5' => $Lead->get_business(),
			'6' => $Lead->get_stage(),
			'8' => $Lead->get_service(),
			'9' => $Lead->get_industry(),
			'10' => $Lead->get_finishing_type(),
			'11' => '',
			'13' => $Lead->get_notes(),
			'25.1' => $Lead->get_certification(),
			'26' => $Lead->get_phone(),
		);

		return GFAPI::add_entry( $entry_data );
	}
}