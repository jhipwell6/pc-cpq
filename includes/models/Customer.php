<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Post_Model;
use \WP_Query;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Customer extends Post_Model
{
	const POST_TYPE = 'customer';
	const UNIQUE_KEY = 'customer_code';
	const WP_PROPS = array(
		'post_title' => 'name',
		'post_content' => 'description',
		'post_date' => 'date',
	);
	const ALIASES = array(
		'contacts' => 'raw_contacts',
		'shipping' => 'raw_shipping',
	);
	const HIDDEN = array();

	protected $customer_code;
	protected $name;
	protected $description;
	protected $phone;
	protected $fax;
	protected $website;
	protected $sales_id;
	protected $terms_code;
	protected static $Contact_Class = 'PC_CPQ\Models\Customer_Contact';
	protected $raw_contacts;
	protected $Contacts;
	protected $billing_street_address;
	protected $billing_street_address_2;
	protected $billing_city;
	protected $billing_state;
	protected $billing_zip;
	protected $billing_country;
	protected static $Shipping_Class = 'PC_CPQ\Models\Customer_Shipping';
	protected $raw_shipping;
	protected $Shipping;
	// computed
	protected $edit_url;
	protected $manage_url;
	protected $formatted_address;

	/*
	 * Getters
	 */

	public function get_name()
	{
		$name = $this->get_post_title();
		if ( $name == 'Customers' || $name == 'Leads' ) {
			$this->name = '';
		}
		return $this->name;
	}

	public function get_description( $apply_filters = false )
	{
		return $this->get_post_content( $apply_filters );
	}

	public function get_date( $format = 'Y-m-d h:i:s' )
	{
		return $this->get_post_date( $format );
	}

	public function get_customer_code()
	{
		return $this->get_prop( 'customer_code' );
	}

	public function get_phone()
	{
		return $this->get_prop( 'phone' );
	}

	public function get_fax()
	{
		return $this->get_prop( 'fax' );
	}

	public function get_website()
	{
		return $this->get_prop( 'website' );
	}

	public function get_sales_id()
	{
		return $this->get_prop( 'sales_id' );
	}

	public function get_terms_code()
	{
		return $this->get_prop( 'terms_code' );
	}

	public function get_raw_contacts()
	{
		if ( null === $this->raw_contacts ) {
			$this->raw_contacts = $this->get_meta( 'contacts' );
		}
		return $this->raw_contacts;
	}

	public function has_contacts()
	{
		return ! empty( $this->get_raw_contacts() );
	}

	public function get_Contacts( $force_update = false )
	{
		if ( null === $this->Contacts || $force_update ) {
			$this->Contacts = array();
			if ( ! empty( $this->get_raw_contacts() ) ) {
				foreach ( $this->get_raw_contacts() as $index => $raw_contact ) {
					$this->add_Contact( $index, $raw_contact, false );
				}
			}
		}
		return $this->Contacts;
	}

	public function get_billing_street_address()
	{
		return $this->get_prop( 'billing_street_address' );
	}

	public function get_billing_street_address_2()
	{
		return $this->get_prop( 'billing_street_address_2' );
	}

	public function get_billing_city()
	{
		return $this->get_prop( 'billing_city' );
	}

	public function get_billing_state()
	{
		return $this->get_prop( 'billing_state' );
	}

	public function get_billing_zip()
	{
		return $this->get_prop( 'billing_zip' );
	}

	public function get_billing_country()
	{
		return $this->get_prop( 'billing_country' );
	}

	public function has_billing()
	{
		return (bool) $this->get_billing_street_address();
	}

	public function get_raw_shipping()
	{
		if ( null === $this->raw_shipping ) {
			$this->raw_shipping = $this->get_meta( 'shipping' );
		}
		return $this->raw_shipping;
	}

	public function get_Shipping( $force_update = false )
	{
		if ( null === $this->Shipping || $force_update ) {
			$this->Shipping = array();
			if ( ! empty( $this->get_raw_shipping() ) ) {
				foreach ( $this->get_raw_shipping() as $index => $raw_shipping ) {
					$this->add_Shipping( $index, $raw_shipping, false );
				}
			}
		}
		return $this->Shipping;
	}

	/*
	 * Computed
	 */

	public function has_completed_profile()
	{
		if (
			(bool) $this->get_name() &&
			(bool) $this->get_phone() &&
			(bool) $this->get_website() &&
			$this->has_contacts() &&
			$this->has_billing()
		) {
			return true;
		}

		return false;
	}

	public function get_input_value()
	{
		return $this->get_id();
	}

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
			$this->manage_url = PC_CPQ()->Site()->get_customers_page_url() . $this->get_id();
		}
		return $this->manage_url;
	}
	
	public function get_formatted_address()
	{
		if ( null === $this->formatted_address ) {
			$this->formatted_address = '';
			if ( $this->has_billing() ) {
				$this->formatted_address = strtr(
					'{company}{street_address}{street_address_2}{city} {state} {zip}{country}',
					array(
						'{company}' => $this->get_name() ? $this->get_name() . '<br />' : '',
						'{street_address}' => $this->get_billing_street_address() ? $this->get_billing_street_address() . '<br />' : '',
						'{street_address_2}' => $this->get_billing_street_address_2() ? $this->get_billing_street_address_2() . '<br />' : '',
						'{city}' => $this->get_billing_city() ? $this->get_billing_city() . ', ' : '',
						'{state}' => $this->get_billing_state(),
						'{zip}' => $this->get_billing_zip(),
						'{country}' => $this->get_billing_country() ? '<br />' . $this->get_billing_country() : '',
					)
				);
			}
		}
		return $this->formatted_address;
	}

	/*
	 * Setters
	 */

	public function set_name( $value )
	{
		return $this->set_prop( 'name', $value );
	}

	public function set_description( $value )
	{
		return $this->set_prop( 'description', $value );
	}

	public function set_date( $value, $format = 'Y-m-d h:i:s' )
	{
		return $this->set_prop( 'date', $this->to_datetime( $value, $format ) );
	}

	public function set_customer_code( $value )
	{
		return $this->set_prop( 'customer_code', $value );
	}

	public function set_phone( $value )
	{
		return $this->set_prop( 'phone', $value );
	}

	public function set_fax( $value )
	{
		return $this->set_prop( 'fax', $value );
	}

	public function set_website( $value )
	{
		return $this->set_prop( 'website', $value );
	}

	public function set_sales_id( $value )
	{
		return $this->set_prop( 'sales_id', $value );
	}

	public function set_terms_code( $value )
	{
		return $this->set_prop( 'terms_code', $value );
	}

	public function set_raw_contacts( $value )
	{
		return $this->set_prop( 'raw_contacts', $value );
	}

	public function set_billing( $value )
	{
		return $this->set_prop( 'billing', $value );
	}
	
	public function set_billing_street_address( $value )
	{
		return $this->set_prop( 'billing_street_address', $value );
	}
	
	public function set_billing_street_address_2( $value )
	{
		return $this->set_prop( 'billing_street_address_2', $value );
	}
	
	public function set_billing_city( $value )
	{
		return $this->set_prop( 'billing_city', $value );
	}
	
	public function set_billing_state( $value )
	{
		return $this->set_prop( 'billing_state', $value );
	}
	
	public function set_billing_zip( $value )
	{
		return $this->set_prop( 'billing_zip', $value );
	}
	
	public function set_billing_country( $value )
	{
		return $this->set_prop( 'billing_country', $value );
	}

	public function set_raw_shipping( $value )
	{
		return $this->set_prop( 'raw_shipping', $value );
	}

	/*
	 * Savers
	 */

	public function save_name_meta( $value )
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

	public function save_raw_contacts_meta( $value )
	{
		$result = update_field( 'contacts', $value, $this->get_id() );
		$this->refresh_Contacts();
		return $result;
	}

	public function save_raw_shipping_meta( $value )
	{
		$result = update_field( 'shipping', $value, $this->get_id() );
		$this->refresh_Shipping();
		return $result;
	}

	public function save_Contacts()
	{
		$contacts = array_map( function ( $Contact ) {
			return $Contact->to_array();
		}, $this->get_Contacts() );

		$this->update_prop( 'raw_contacts', $contacts );
	}

	public function save_Shipping()
	{
		$shipping = array_map( function ( $Ship ) {
			return $Ship->to_array();
		}, $this->get_Shipping() );

		$this->update_prop( 'raw_shipping', $shipping );
	}

	/*
	 * Helpers
	 */
	
	public function get_by_name( $name = null )
	{
		$name = ! $name ? $this->get_name() : $name;
		$post = get_page_by_title( $name, OBJECT, $this->get_post_type() );
		
		$instance = $this;
		if ( $post ) {
			$instance = new static( $post->ID );
		}
		return $instance;
	}

	public function get_Contacts_count()
	{
		return count( $this->get_Contacts() );
	}

	public function add_Contact( $index = null, $raw_contact = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Contacts_count();
		}
		$this->Contacts[] = new self::$Contact_Class( $index, $raw_contact );

		if ( $save ) {
			$this->save_Contacts();
		}
	}

	public function delete_Contact( $index )
	{
		$Contacts = $this->get_Contacts();
		if ( isset( $Contacts[$index] ) ) {
			unset( $Contacts[$index] );
		}
		$this->Contacts = array_values( $Contacts );

		$this->save_Contacts();
	}
	
	public function refresh_Contacts()
	{
		$this->get_Contacts( true );
	}
	
	public function has_Contact( $email )
	{
		if ( ! $this->has_contacts() ) {
			return false;
		}
		
		$found = array_filter( $this->get_Contacts(), function( $Contact ) use ( $email ) {
			return $Contact->get_email() === $email;
		});
		
		return ! empty( $found );
	}

	public function get_Shipping_count()
	{
		return count( $this->get_Shipping() );
	}

	public function add_Shipping( $index = null, $raw_shipping = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Shipping_count();
		}
		$this->Shipping[] = new self::$Shipping_Class( $index, $raw_shipping );

		if ( $save ) {
			$this->save_Shipping();
		}
	}

	public function delete_Shipping( $index )
	{
		$Shipping = $this->get_Shipping();
		if ( isset( $Shipping[$index] ) ) {
			unset( $Shipping[$index] );
		}
		$this->Shipping = array_values( $Shipping );

		$this->save_Shipping();
	}
	
	public function refresh_Shipping()
	{
		$this->get_Shipping( true );
	}

	public static function get_customer_by( $prop = 'id', $value )
	{
		$args = [
			'post_type' => 'customer',
			'posts_per_page' => 1,
			'fields' => 'ids',
		];
		
		switch ( $prop ) {
			case 'name';
			case 'title';
				$args['title'] = $value;
				break;
			
			case 'email';
				$args['meta_query'] = [[
					'key' => 'contacts_$_email',
					'value' => $value,
					'compare' => '=',
				]];
				break;
			
			case 'ID':
			default: // id
				$args['p'] = (int) $value;
				break;
		}
		
		$Customer = false;
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$post_id = array_first( $query->posts );
			$Customer = new self( $post_id );
		}
		
		return $Customer;
	}
}
