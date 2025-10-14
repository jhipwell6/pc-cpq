<?php

namespace PC_CPQ\Controllers;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Core\Nutshell_Service;
use \PC_CPQ\Helpers\Constants;
use \PC_CPQ\Helpers\Access;
use \PC_CPQ\Helpers\Geometry;
use \PC_CPQ\Helpers\Utilities;
use PC_CPQ\Models\Customer;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Forms extends MVC_Controller_Registry
{
	const QUOTE_FORM_ID = 1;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 *
	 * @return void
	 */
	protected function __construct()
	{		
		add_filter( 'gform_field_content', array( $this, 'create_select_optgroup' ), 10, 2 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'update_parts_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'update_business_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'update_quantities_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'update_lead_is_authorized_meta' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'update_quote_number' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'save_customer_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'send_to_nutshell' ), 20, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . self::QUOTE_FORM_ID, array( $this, 'add_parts_to_index' ), 10, 4 );
		add_filter( 'gform_email_background_color_label', array( $this, 'set_email_label_color' ), 10, 3 );
		add_filter( 'gform_entry_meta', array( $this, 'entry_is_authorized_meta' ), 10, 2 );
		add_filter( 'gform_field_content_' . self::QUOTE_FORM_ID . '_11', array( $this, 'modify_upload_field' ), 10, 5 );

		add_filter( 'gform_confirmation', array( $this, 'maybe_get_company_details' ), 10, 4 );
		add_action( 'gform_after_submission', array( $this, 'save_company_details_to_customer' ), 10, 2 );

//		add_action( 'wp', array( $this, 'fix_nutshell_leads' ), 10 );
	}

	public function fix_nutshell_leads()
	{
		if ( ! isset( $_GET['nutshell'] ) )
			return false;

		$leads = get_posts( array(
			'post_type' => 'lead',
			'posts_per_page' => -1
			) );

		if ( ! empty( $leads ) ) {
			foreach ( $leads as $post ) {
				$nutshell = new Nutshell_Service( $post->ID );
				$nutshell->fix_lead_ids();
			}
		}
	}

	public function maybe_get_company_details( $confirmation, $form, $entry, $ajax )
	{
		if ( $form['id'] == self::QUOTE_FORM_ID ) {
			$created_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
			if ( ! empty( $created_posts ) ) {
				$Lead = PC_CPQ()->lead( array_first( $created_posts ) );
				if ( ! $Lead->has_customer() || ( $Lead->has_customer() && ! $Lead->get_Customer()->has_completed_profile() ) ) {
					$confirmation = array(
						'redirect' => $this->get_company_redirect_url( $Lead )
					);
				}
			}
		}

		return $confirmation;
	}

	public function save_company_details_to_customer( $entry, $form )
	{
		if ( $form['id'] == 2 ) {
			$customer_id = rgar( $entry, 5 );
			if ( $customer_id ) {
				$Customer = PC_CPQ()->customer( $customer_id );
				$billing = array(
					'street_address' => rgar( $entry, '4.1' ),
					'street_address_2' => rgar( $entry, '4.2' ),
					'city' => rgar( $entry, '4.3' ),
					'state' => rgar( $entry, '4.4' ),
					'zip' => rgar( $entry, '4.5' ),
					'country' => rgar( $entry, '4.6' ),
				);
				$data = array(
					'name' => rgar( $entry, 1 ),
					'website' => rgar( $entry, 3 ),
					'phone' => rgar( $entry, 6 ),
					'fax' => rgar( $entry, 7 ),
					'billing' => array_filter( $billing )
				);

				$data = array_filter( $data );

				$Customer->set_props( $data );
				$Customer = $Customer->save();
			}
		}
	}

	public function send_to_nutshell( $post_id, $feed, $entry, $form )
	{
		$nutshell = new Nutshell_Service( $post_id );
		$nutshell->maybe_send_lead( $entry, $form );
	}

	public function add_parts_to_index( $post_id, $feed, $entry, $form )
	{
		if ( have_rows( 'part_data', $post_id ) ) {
			while ( have_rows( 'part_data', $post_id ) ) {
				the_row();
				$args = array(
					'drawing_number' => get_sub_field( 'drawing_number' ),
					'revision_number' => get_sub_field( 'revision_number' ),
					'part_number' => get_sub_field( 'part_number' ),
				);
				$hash = md5( implode( '', $args ) );
				$result = PC_CPQ()->part_lookup->search_parts( $hash );
				if ( empty( $result ) ) {
					PC_CPQ()->part_lookup->insert_part( array(
						'post_id' => get_the_ID(),
						'part_name' => $hash,
					) );
				}
			}
		}
	}

	public function create_select_optgroup( $input, $field )
	{
		if ( 'select' !== $field->type || ! preg_match( '#value=[\'"]optgroup[\'"]#', $input ) || ! preg_match( '#<select[^>]*>(.*)</select>#', $input, $option_matches ) || ! preg_match_all( '#<option.*?/option>#', $all_options_html = $option_matches[1], $option_element_matches ) || ! ( $options = reset( $option_element_matches ) )
		) {
			return $input;
		}

		$label = '';
		$groups = [];
		foreach ( $options as $option ) {
			if ( preg_match( '#value=[\'"]optgroup[\'"][^>]*>(.*)</option>#', $option, $option_matches ) ) {
				$label = $option_matches[1];
			} else {
				$groups[$label][] = $option;
			}
		}

		$grouped_options = array_map( function ( $options, $group_label ) {
			$html = join( "\n", $options );

			return $group_label ? "<optgroup label='$group_label'>$html</optgroup>" : $html;
		}, $groups, array_keys( $groups ) );

		return str_replace( $all_options_html, join( '', $grouped_options ), $input );
	}

	public function update_parts_lead_data( $post_id, $feed, $entry, $form )
	{
		$field_id = 31;
		$parts = json_decode( rgar( $entry, $field_id ), true );
		$data = array();

		if ( ! empty( $parts ) ) {
			$i = 0;
			foreach ( $parts as $part_arr ) {
				$data[$i] = array();
				foreach ( $part_arr as $key => $value ) {
					$new_key = Utilities::decamelize( $key );
					switch ( $new_key ) {
						case 'area':
						case 'area_computed':
							$new_key = 'area_computed';
							$value = Geometry::mm2_to_ft2( $value );
							break;

						case 'volume':
						case 'volume_computed':
							$new_key = 'volume_computed';
							$value = Geometry::mm3_to_in3( $value );
							break;

						case 'd_x':
						case 'd_x_computed':
							$new_key = 'd_x_computed';
							$value = Geometry::mm_to_in( $value );
							break;
						
						case 'd_y':
						case 'd_y_computed':
							$new_key = 'd_y_computed';
							$value = Geometry::mm_to_in( $value );
							break;
							
						case 'd_z':
						case 'd_z_computed':
							$new_key = 'd_z_computed';
							$value = Geometry::mm_to_in( $value );
							break;

						case 'processes':
							if ( ! empty( $value ) ) {
								$value = array_map( function ( $process ) {
									$process['min_thickness'] = $process['minThickness'];
									$process['max_thickness'] = $process['maxThickness'];
									unset( $process['minThickness'] );
									unset( $process['maxThickness'] );
									return $process;
								}, $value );
							}
							break;
					}
					$data[$i][$new_key] = $value;
				}
				$i ++;
			}

			// update lead with part data
			update_field( 'part_data', $data, $post_id );
		}
	}
	
	public function update_business_lead_data( $post_id, $feed, $entry, $form )
	{
		$field_id = 5;
		$is_business = rgar( $entry, $field_id );
		switch ( $is_business ) {
			case 'A business':
			case 'a business':
				$value = 'I am a business';
				break;
			
			case 'Not a business':
			case 'not a business':
				$value = 'I am not a business';
				break;
		}
		
		update_field( 'business', $value, $post_id );
	}

	public function update_quantities_lead_data( $post_id, $feed, $entry, $form )
	{
		$quantities = maybe_unserialize( rgar( $entry, 27 ) );
		if ( ! empty( $quantities ) ) {
			$Lead = PC_CPQ()->lead( $post_id );
			if ( ! empty( $Lead->get_Parts() ) ) {
				foreach ( $Lead->get_Parts() as $Part ) {
					$i = 0;
					foreach ( $quantities as $quantity ) {
						$Part->add_Quantity( $i, [ 'break_point' => $quantity ], false );
						$i++;
					}
					$Part->save_Quantities();
				}
			}	
		}
	}

	public function update_lead_is_authorized_meta( $post_id, $feed, $entry, $form )
	{
		$entry_id = rgar( $entry, 'id' );
		$is_authorized = gform_get_meta( $entry_id, 'is_authorized' );

		// update lead with is_authorized
		update_post_meta( $post_id, 'is_authorized', filter_var( $is_authorized, FILTER_VALIDATE_BOOLEAN ) );
	}

	public function update_quote_number( $post_id, $feed, $entry, $form )
	{
		$Lead = PC_CPQ()->lead( $post_id );
		// next quote number will be generated
		$Lead->update_prop( 'quote_number', false );
	}

	public function save_customer_data( $post_id, $feed, $entry, $form )
	{
		$company = rgar( $entry, 4 );
		if ( $company ) {
			$Customer = Customer::get_customer_by( 'name', $company );
		}
		
		if ( ! $Customer ) {
			$email = rgar( $entry, 3 );
			$Customer = Customer::get_customer_by( 'email', $email );
		}
		
		if ( ! $Customer ) {
			$CustomerModel = new Customer();
			$CustomerModel->set_props( [
				'name' => rgar( $entry, 4 ),
				'phone' => rgar( $entry, 26 ),
			] );
			$Customer = $CustomerModel->save();
		}
		
		$this->update_customer_contacts( $entry, $Customer );
		$this->assign_customer_to_lead( $post_id, $Customer );
	}

	private function update_customer_contacts( $entry, \PC_CPQ\Models\Customer $Customer )
	{
		$name = ucwords( rgar( $entry, 1 ) ) . ' ' . ucwords( rgar( $entry, 2 ) );
		$phone = rgar( $entry, 26 );
		$email = rgar( $entry, 3 );
		
		if ( ! $Customer->has_Contact( $email ) ) {
			$new_contact = array(
				'name' => trim( $name ),
				'phone' => $phone,
				'email' => $email,
			);
			
			$Customer->add_Contact( null, $new_contact );
		}
	}

	private function assign_customer_to_lead( $lead_id, \PC_CPQ\Models\Customer $Customer )
	{
		$Lead = PC_CPQ()->lead( $lead_id );
		$Lead->update_prop( 'raw_customer', $Customer->get_id() );
	}

	public function set_email_label_color( $color, $field, $lead )
	{
		return '#f1f2f7';
	}

	public function entry_is_authorized_meta( $entry_meta, $form_id )
	{
		$entry_meta['is_authorized'] = array(
			'label' => 'Authorized?',
			'is_numeric' => false,
			'update_entry_meta_callback' => array( $this, 'update_is_authorized_meta' ),
			'is_default_column' => true
		);

		return $entry_meta;
	}

	public function update_is_authorized_meta( $key, $entry, $form )
	{
		$email_field_id = 3;
		$email = rgar( $entry, $email_field_id );

		return Access::is_whitelisted( $email );
	}

	public function modify_upload_field( $content, $field, $value, $lead_id, $form_id )
	{
		$content = str_ireplace( 'drop files here or', 'Drag and drop files here or', $content );
		return $content;
	}

	private function get_company_redirect_url( $Lead )
	{
		$args = array();
		$props = array(
			'company_id' => 'get_id',
			'company' => 'get_name',
			'website' => 'get_website',
			'phone' => 'get_phone',
			'fax' => 'get_fax',
		);

		foreach ( $props as $prop => $getter ) {
			if ( $Lead->get_Customer()->{$getter}() ) {
				$args[$prop] = $Lead->get_Customer()->{$getter}();
			}
		}

		return add_query_arg( $args, site_url( '/get-company-details/' ) );
	}

}

PC_CPQ_Forms::instance();
