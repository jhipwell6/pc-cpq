<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Post_Model;
use \NumberFormatter;
use \GFAPI;
use \GPDFAPI;
use PC_CPQ\Models\Customer;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Lead extends Post_Model
{
	const POST_TYPE = 'lead';
	const UNIQUE_KEY = 'quote_number';
	const WP_PROPS = array(
		'post_title' => 'title',
		'post_content' => 'description',
		'post_date' => 'date',
	);
	const ALIASES = array(
		'customer' => 'raw_customer',
	);
	const HIDDEN = array(
		'raw_specs',
		'Part_Class',
	);

	protected $quote_number;
	protected $status;
	protected $title;
	protected $description;
	protected $first_name;
	protected $last_name;
	protected $email;
	protected $phone;
	protected $company;
	protected $certification;
	protected $include_metal_factor;
	protected $coating;
	protected $service;
	protected $industry;
	protected $business;
	protected $finishing_type;
	protected $stage;
	protected $notes;
	protected $quote_notes;
	protected $quote_pricing_type;
	protected $quote_date;
	protected $follow_up_date;
	protected $expiration_date;
	protected $raw_specs;
	protected $external_id;
	protected $nutshell_id;
	protected $form_entry_id;
	protected $sent;
	protected $no_quote_email_message;
	protected $recipient;
	protected static $Part_Class = 'PC_CPQ\Models\Part';
	protected $raw_parts;
	protected $Parts;
	protected static $Customer_Class = 'PC_CPQ\Models\Customer';
	protected $raw_customer;
	protected $Customer;
	// computed
	protected $edit_url;
	protected $manage_url;
	protected $full_name;
	protected $address;
	protected $formatted_address;
	private $files;
	private $pdfs;
	private $quote_pdf;
	private $routing_pdf;
	private $min_lot_charge;

	/*
	 * Getters
	 */

	public function get_quote_number()
	{
		return $this->get_prop( 'quote_number' ) ?: 0;
	}

	public function get_status()
	{
		if ( null === $this->status ) {
			$status = $this->get_meta( 'status' );
			$this->status = ( $status == '' || ! $status ) ? 'New' : $status;
		}
		return $this->status;
	}

	public function get_title()
	{
		return $this->get_post_title();
//		$title = $this->get_post_title();
//		if ( $title == '' || $title == ' ' ) {
//			$this->title = $this->get_full_name() . ' - ' . date( 'm/d/Y' );
//		}
//		return $this->title;
	}

	public function get_description( $apply_filters = false )
	{
		return $this->get_post_content( $apply_filters );
	}

	public function get_date( $format = 'Y-m-d h:i:s' )
	{
		return $this->get_post_date( $format );
	}

	public function get_first_name()
	{
		return $this->get_prop( 'first_name' );
	}

	public function get_last_name()
	{
		return $this->get_prop( 'last_name' );
	}

	public function get_full_name()
	{
		if ( null === $this->full_name ) {
			$this->full_name = $this->get_first_name() . ' ' . $this->get_last_name();
		}
		return $this->full_name;
	}

	public function get_email()
	{
		return $this->get_prop( 'email' );
	}

	public function get_phone()
	{
		return $this->get_prop( 'phone' );
	}

	public function get_company()
	{
		return $this->get_prop( 'company' );
	}

	public function get_certification()
	{
		return $this->get_prop( 'certification' );
	}

	public function needs_certification()
	{
		return (bool) $this->get_certification();
	}

	public function get_include_metal_factor()
	{
		return $this->get_prop( 'include_metal_factor' );
	}

	public function include_metal_factor()
	{
		return (bool) $this->get_include_metal_factor();
	}

	public function get_service()
	{
		return $this->get_prop( 'service' );
	}

	public function get_industry()
	{
		return $this->get_prop( 'industry' );
	}

	public function get_business()
	{
		return $this->get_prop( 'business' );
	}

	public function get_finishing_type()
	{
		return $this->get_prop( 'finishing_type' );
	}

	public function get_stage()
	{
		return $this->get_prop( 'stage' );
	}

	public function get_notes()
	{
		return $this->get_prop( 'notes' );
	}

	public function get_quote_notes()
	{
		return $this->get_prop( 'quote_notes' );
	}

	public function get_quote_pricing_type()
	{
		return $this->get_prop( 'quote_pricing_type' );
	}

	public function get_quote_date( $format = 'm/d/Y h:s a' )
	{
		if ( null === $this->quote_date ) {
			$this->quote_date = $this->get_meta( 'quote_date' );
		}
		return $this->quote_date ? $this->to_datetime( $this->quote_date, $format ) : '';
	}

	public function get_follow_up_date( $format = 'm/d/Y h:s a' )
	{
		if ( null === $this->follow_up_date ) {
			$this->follow_up_date = $this->get_meta( 'follow_up_date' );
		}
		return $this->follow_up_date ? $this->to_datetime( $this->follow_up_date, $format ) : '';
	}

	public function get_expiration_date( $format = 'm/d/Y h:s a' )
	{
		if ( null === $this->expiration_date ) {
			$this->expiration_date = $this->get_meta( 'expiration_date' );
		}
		return $this->expiration_date ? $this->to_datetime( $this->expiration_date, $format ) : '';
	}

	public function get_raw_customer()
	{
		if ( null === $this->raw_customer ) {
			$this->raw_customer = $this->get_meta( 'customer' );
		}
		return $this->raw_customer;
	}

	public function get_Customer( $force_update = false )
	{
		if ( null === $this->Customer || $force_update ) {
			$this->Customer = new self::$Customer_Class( $this->get_raw_customer() );
		}
		return $this->Customer;
	}

	public function has_customer()
	{
		return (bool) $this->get_raw_customer();
	}

	public function get_raw_specs()
	{
		return $this->get_prop( 'raw_specs' );
	}

	public function get_external_id()
	{
		return $this->get_prop( 'external_id' );
	}

	public function get_nutshell_id()
	{
		return $this->get_prop( 'nutshell_id' );
	}

	public function get_form_entry_id()
	{
		if ( null === $this->form_entry_id ) {
			$this->form_entry_id = get_post_meta( $this->get_id(), 'form_entry_id', true );
		}
		return $this->form_entry_id;
	}

	public function get_sent()
	{
		if ( null === $this->sent ) {
			$this->sent = get_post_meta( $this->get_id(), 'sent', true );
		}
		return $this->sent;
	}

	public function get_no_quote_email_message()
	{
		return $this->get_prop( 'no_quote_email_message' );
	}

	public function get_recipient()
	{
		return $this->get_prop( 'recipient' );
	}

	public function get_raw_parts()
	{
		if ( null === $this->raw_parts ) {
			$this->raw_parts = $this->get_meta( 'part_data' );
		}
		return $this->raw_parts;
	}

	public function get_Parts( $force_update = false )
	{
		if ( null === $this->Parts || $force_update ) {
			$this->Parts = [];
			if ( ! empty( $this->get_raw_parts() ) ) {
				foreach ( $this->get_raw_parts() as $index => $raw_part ) {
					$this->add_Part( $index, $raw_part, false );
				}
			}
		}
		return $this->Parts;
	}

	public function get_min_lot_charge()
	{
		if ( null === $this->min_lot_charge ) {
			$min_lot_charge = 0;
			if ( ! empty( $this->get_Parts() ) ) {
				$charges = array_map( function ( $Part ) {
					return $Part->get_min_lot_charge();
				}, $this->get_Parts() );
				$min_lot_charge = max( $charges );
			}
			$this->min_lot_charge = $min_lot_charge;
		}
		return $this->min_lot_charge;
	}

	/*
	 * Setters
	 */

	public function set_quote_number( $value )
	{
		return $this->set_prop( 'quote_number', $value );
	}

	public function set_status( $value )
	{
		return $this->set_prop( 'status', $value );
	}

	public function set_title( $value )
	{
		if ( $value == '' ) {
			$value = $this->get_full_name() . ' - ' . date( 'm/d/Y' );
		}
		return $this->set_prop( 'title', $value );
	}

	public function set_description( $value )
	{
		return $this->set_prop( 'description', $value );
	}

	public function set_date( $value, $format = 'Y-m-d h:i:s' )
	{
		return $this->set_prop( 'date', $this->to_datetime( $value, $format ) );
	}

	public function set_first_name( $value )
	{
		return $this->set_prop( 'first_name', $value );
	}

	public function set_last_name( $value )
	{
		return $this->set_prop( 'last_name', $value );
	}

	public function set_email( $value )
	{
		return $this->set_prop( 'email', $value );
	}

	public function set_phone( $value )
	{
		return $this->set_prop( 'phone', $value );
	}

	public function set_company( $value )
	{
		return $this->set_prop( 'company', $value );
	}

	public function set_certification( $value )
	{
		return $this->set_prop( 'certification', $value );
	}

	public function set_include_metal_factor( $value )
	{
		return $this->set_prop( 'include_metal_factor', $value );
	}

	public function set_service( $value )
	{
		return $this->set_prop( 'service', $value );
	}

	public function set_industry( $value )
	{
		return $this->set_prop( 'industry', $value );
	}

	public function set_business( $value )
	{
		return $this->set_prop( 'business', $value );
	}

	public function set_finishing_type( $value )
	{
		return $this->set_prop( 'finishing_type', $value );
	}

	public function set_stage( $value )
	{
		return $this->set_prop( 'stage', $value );
	}

	public function set_notes( $value )
	{
		return $this->set_prop( 'notes', $value );
	}

	public function set_quote_notes( $value )
	{
		return $this->set_prop( 'quote_notes', $value );
	}

	public function set_quote_pricing_type( $value )
	{
		return $this->set_prop( 'quote_pricing_type', $value );
	}

	public function set_quote_date( $value )
	{
		return $this->set_prop( 'quote_date', $value );
	}

	public function set_follow_up_date( $value )
	{
		return $this->set_prop( 'follow_up_date', $value );
	}

	public function set_expiration_date( $value )
	{
		return $this->set_prop( 'expiration_date', $value );
	}

	public function set_raw_customer( $value )
	{
		return $this->set_prop( 'raw_customer', $value );
	}

	public function set_raw_specs( $value )
	{
		return $this->set_prop( 'raw_specs', $value );
	}

	public function set_external_id( $value )
	{
		return $this->set_prop( 'external_id', $value );
	}

	public function set_nutshell_id( $value )
	{
		return $this->set_prop( 'nutshell_id', $value );
	}

	public function set_form_entry_id( $value )
	{
		return $this->set_prop( 'form_entry_id', $value );
	}

	public function set_sent( $value )
	{
		return $this->set_prop( 'sent', $value );
	}

	public function set_no_quote_email_message( $value )
	{
		return $this->set_prop( 'no_quote_email_message', $value );
	}

	public function set_raw_parts( $value )
	{
		return $this->set_prop( 'raw_parts', $value );
	}

	public function set_parts( $value )
	{
		return $this->set_prop( 'parts', $value );
	}

	/*
	 * Savers
	 */

	public function after_save()
	{
		$this->maybe_create_customer();
		if ( $this->get_title() == '' ) {
			$this->update_prop( 'title', $this->get_full_name() . ' - ' . date( 'm/d/Y' ) );
		}

		$this->maybe_create_entry();
	}

	public function save_quote_number_meta( $value )
	{
		if ( $value == '' || ! $value ) {
			$value = $this->get_next_quote_number();
		}

		return update_field( 'quote_number', $value, $this->get_id() );
	}

	public function save_title_meta( $value )
	{
		return $this->save_post_title( $value );
	}

	public function save_description_meta( $value )
	{
		if ( is_array( $value ) || ! $value ) {
			$value = ' ';
		}
		return $this->save_post_content( $value );
	}

	public function save_date_meta( $value, $return_format = '' )
	{
		return $this->save_post_date( $this->to_datetime( $value ), $return_format );
	}

	public function save_form_entry_id_meta( $value )
	{
		return update_post_meta( $this->get_id(), 'form_entry_id', $value );
	}

	public function save_sent_meta( $value )
	{
		return update_post_meta( $this->get_id(), 'sent', $value );
	}

	public function save_raw_customer_meta( $value )
	{
		$result = update_field( 'customer', $value, $this->get_id() );
		$this->refresh_Customer();
		return $result;
	}

	public function save_raw_parts_meta( $value )
	{
		$result = update_field( 'part_data', $value, $this->get_id() );
		$this->refresh_Parts();
		return $result;
	}

	public function save_Parts()
	{
		$parts = array_map( function ( $Part ) {
			return $Part->to_array();
		}, $this->get_Parts() );

		$this->update_prop( 'raw_parts', $parts );
	}

	/*
	 * Getters (computed)
	 */

	public function get_edit_url()
	{
		if ( null === $this->edit_url ) {
			$this->edit_url = get_edit_post_link( $this->get_id() );
		}
		return $this->edit_url;
	}

	public function get_manage_url()
	{
		if ( null === $this->manage_url ) {
			$this->manage_url = PC_CPQ()->Site()->get_leads_page_url() . $this->get_id();
		}
		return $this->manage_url;
	}

	public function get_address()
	{
		if ( null === $this->address ) {
			$this->address = '';
			if ( null != $this->get_Customer() ) {
				$this->address = get_field( 'billing', $this->get_Customer()->get_id() );
			}
		}
		return $this->address;
	}

	public function get_formatted_address()
	{
		if ( null === $this->formatted_address ) {
			$this->formatted_address = '';
			if ( $address = $this->get_address() ) {
				$state = $address['state'] == 'Other' ? $address['other_state'] : $address['state'];
				$this->formatted_address = strtr(
					'{company}{street_address}{street_address_2}{city} {state} {zip}{country}',
					array(
						'{company}' => $this->get_company() ? $this->get_company() . '<br />' : '',
						'{street_address}' => $address['street_address'] ? $address['street_address'] . '<br />' : '',
						'{street_address_2}' => $address['street_address_2'] ? $address['street_address_2'] . '<br />' : '',
						'{city}' => $address['city'] ? $address['city'] . ', ' : '',
						'{state}' => $state,
						'{zip}' => $address['zip'],
						'{country}' => $address['country'] ? '<br />' . $address['country'] : '',
					)
				);
			}
		}
		return $this->formatted_address;
	}

	public function get_files()
	{
		if ( null === $this->files ) {
			$entry = GFAPI::get_entry( $this->get_form_entry_id() );
			$file_json = rgar( $entry, '11' );
			if ( $file_json && $file_json != '' ) {
				$this->files = json_decode( $file_json, true );
			} else {
				$this->files = [];
			}
		}
		return $this->files;
	}

	public function get_pdfs()
	{
		if ( null === $this->pdfs ) {
			$this->pdfs = [];
			if ( $this->get_form_entry_id() ) {
				$pdfs = GPDFAPI::get_entry_pdfs( $this->get_form_entry_id() );
				$this->pdfs = ! is_wp_error( $pdfs ) ? $pdfs : [];
			}
		}
		return $this->pdfs;
	}

	public function has_pdfs()
	{
		return ! empty( $this->get_pdfs() );
	}

	public function get_quote_pdf()
	{
		if ( null === $this->quote_pdf ) {
			$this->quote_pdf = $this->get_pdf_by_name( 'Quote PDF' );
		}
		return $this->quote_pdf;
	}

	public function get_routing_pdf()
	{
		if ( null === $this->routing_pdf ) {
			$this->routing_pdf = $this->get_pdf_by_name( 'Routing PDF' );
		}
		return $this->routing_pdf;
	}

	/*
	 * Helpers
	 */

	public function maybe_create_customer()
	{
		if ( ! $this->has_customer() ) {
			$Customer = false;
			if ( $this->get_company() ) {
				$Customer = Customer::get_customer_by( 'name', $this->get_company() );
			}
			
			if ( ! $Customer && $this->get_email() ) {
				$Customer = Customer::get_customer_by( 'email', $this->get_email() );
			}

			if ( ! $Customer && $this->get_company() ) {
				$Customer = $this->create_customer_from_lead();
			}
			
			if ( $Customer ) {
				$this->update_prop( 'raw_customer', $Customer->get_id() );
			}
		}
	}
	
	public function create_customer_from_lead()
	{
		$CustomerModel = new Customer();
		$CustomerModel->set_props( [
			'name' => $this->get_company(),
			'phone' => $this->get_phone(),
			'raw_contacts' => [ [
					'name' => $this->get_full_name(),
					'phone' => $this->get_phone(),
					'email' => $this->get_email(),
				] ],
		] );
		return $CustomerModel->save();
	}

	private function flatten_array_values( $array, $key )
	{
		return array_filter( array_column( (array) $array, $key ) );
	}

	private function get_pdf_by_name( $name )
	{
		if ( $this->has_pdfs() ) {
			$pdf_array = wp_list_filter( $this->get_pdfs(), [ 'name' => $name ] );
			if ( ! empty( $pdf_array ) ) {
				$pdf = array_first( $pdf_array );
				return do_shortcode( '[gravitypdf id="' . $pdf['id'] . '" entry="' . $this->get_form_entry_id() . '" type="view" raw="1"]' );
			}
		}
		return '';
	}

	public function is_sent()
	{
		return $this->get_sent();
	}

	public function get_Parts_count()
	{
		return count( $this->get_Parts() );
	}

	public function add_Part( $index = null, $raw_part = array(), $save = true, $is_new = false )
	{
		if ( null === $index ) {
			$index = $this->get_Parts_count();
		}
		$Part = new self::$Part_Class( $index, $raw_part, $this );

		if ( $is_new ) {
			$Part->set_default_pricing();
		}

		$this->Parts[] = $Part;

		if ( $save ) {
			$this->save_Parts();
		}
	}

	public function get_Part( $index )
	{
		$Parts = $this->get_Parts();
		if ( isset( $Parts[$index] ) ) {
			return $Parts[$index];
		}
		return false;
	}

	public function delete_Part( $index )
	{
		$Parts = $this->get_Parts();
		if ( isset( $Parts[$index] ) ) {
			unset( $Parts[$index] );
		}
		$this->Parts = array_values( $Parts );

		$this->save_Parts();
	}

	public function refresh_Parts()
	{
		$this->get_Parts( true );
	}

	public function refresh_Customer()
	{
		$this->get_Customer( true );
	}

	private function get_next_quote_number()
	{
		global $wpdb;
		$post_id = $this->get_id();
		$results = $wpdb->get_results( "SELECT `meta_value` FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = 'quote_number' AND `post_id` != {$post_id} ORDER BY `meta_id` DESC LIMIT 1" );
		$number = ! empty( $results ) ? reset( $results ) : null;
		return ( $number ) ? str_pad( (int) $number->meta_value + 1, 7, '0', STR_PAD_LEFT ) : PC_CPQ()->Settings()->get_starting_quote_number();
	}

	protected function to_datetime( $value, $format = 'Y-m-d h:i:s' )
	{
		if ( ! $value )
			return $value;

		$possible_formats = [
			'u',
			'U',
			'm/d/Y h:s a',
			'Y-m-d H:i:s',
			'Y-m-d h:i:s',
			'Y-m-d',
			'd/m/Y',
			'm/d/Y',
			'd-m-Y',
			'd.m.Y',
		];

		foreach ( $possible_formats as $try_format ) {
			$date = \DateTime::createFromFormat( $try_format, $value );
			if ( $date && $date->format( $try_format ) == $value ) {
				return $date->format( $format );
			}
		}

		// If no format matched, return original value or handle error
		return $value;
	}

	public function maybe_create_entry( $form_id = 1 )
	{
		if ( $this->search_form_entry_id() ) {
			return $this->get_form_entry_id();
		}

		$date = date( 'Y-m-d H:i:s' );
		$entry_data = array(
			'form_id' => $form_id,
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
			'1' => $this->get_first_name(),
			'2' => $this->get_last_name(),
			'3' => $this->get_email(),
			'4' => $this->get_company(),
			'5' => $this->get_business(),
			'6' => $this->get_stage(),
			'8' => $this->get_service(),
			'9' => $this->get_industry(),
			'10' => $this->get_finishing_type(),
			'11' => '',
			'13' => $this->get_notes(),
			'25.1' => $this->get_certification(),
			'26' => $this->get_phone(),
		);
		$entry_id = GFAPI::add_entry( $entry_data );
		if ( ! is_wp_error( $entry_id ) ) {
			$this->update_prop( 'form_entry_id', $entry_id );
			return $this->get_form_entry_id();
		}
		return false;
	}

	public function search_form_entry_id( $form_id = 1 )
	{
		if ( $this->get_form_entry_id() ) {
			return $this->get_form_entry_id();
		}

		$search_criteria = [];
		$search_criteria['field_filters'][] = [
			'key' => 'gravityformsadvancedpostcreation_post_id',
			'value' => 's:7:"post_id";i:' . $this->get_id() . ';',
			'operator' => 'contains',
		];
		$found_entries = GFAPI::get_entry_ids( $form_id, $search_criteria );
		if ( ! empty( $found_entries ) ) {
			$this->update_prop( 'form_entry_id', array_first( $found_entries ) );
			return $this->get_form_entry_id();
		}

		return false;
	}

}
