<?php

namespace PC_CPQ\Controllers;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Core\Nutshell_Service;
use \PC_CPQ\Helpers\Geometry;
use PC_CPQ\Models\Customer;
use PC_CPQ\Models\Part_Pricing_Inputs;
use GFCommon;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Forms extends MVC_Controller_Registry
{
	private const STP_UPLOAD_FIELD_ID = 11;
	private const PARTS_DATA_FIELD_ID = 31;
	private const STP_PROCESSING_DIR = 'pc-cpq-stp-processing';
	private const STP_PROCESSING_META_FILE = 'metadata.json';
	private const STP_PROCESSING_CLEANUP_HOOK = 'pc_cpq_cleanup_stp_processing_uploads';
	private const STP_PROCESSING_MAX_AGE = 259200;
	private const STP_MEASUREMENT_AJAX_ACTION = 'pc_cpq_process_step_upload';
	private const STP_MEASUREMENT_API_URL = 'https://stp-api.snowberrymedia.com/measure.php';
	private const STP_MEASUREMENT_API_KEY = '9f4c8e1a7b3d6c2f0e5a4b8c1d9e7f6a2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7';

	protected $quote_form_id = 1;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 *
	 * @return void
	 */
	protected function __construct()
	{		
		$this->quote_form_id = PC_CPQ()->Pdf_Config()->get_quote_form_id();

		add_filter( 'gform_field_content', array( $this, 'create_select_optgroup' ), 10, 2 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_parts_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_business_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_quantities_lead_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_lead_fee_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_lead_is_authorized_meta' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'update_quote_number' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'save_customer_data' ), 10, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'send_to_nutshell' ), 20, 4 );
		add_action( 'gform_advancedpostcreation_post_after_creation_' . $this->quote_form_id, array( $this, 'add_parts_to_index' ), 10, 4 );
		add_filter( 'gform_email_background_color_label', array( $this, 'set_email_label_color' ), 10, 3 );
		add_filter( 'gform_entry_meta', array( $this, 'entry_is_authorized_meta' ), 10, 2 );
		add_filter( 'gform_field_content_' . $this->quote_form_id . '_' . self::STP_UPLOAD_FIELD_ID, array( $this, 'modify_upload_field' ), 10, 5 );
		add_action( 'gform_post_multifile_upload_' . $this->quote_form_id, array( $this, 'stage_step_upload' ), 10, 5 );
		add_action( 'gform_after_submission_' . $this->quote_form_id, array( $this, 'recover_staged_step_uploads' ), 5, 2 );

		add_filter( 'gform_confirmation', array( $this, 'maybe_get_company_details' ), 10, 4 );
		add_action( 'gform_after_submission', array( $this, 'save_company_details_to_customer' ), 10, 2 );
		add_action( 'wp_ajax_' . self::STP_MEASUREMENT_AJAX_ACTION, array( $this, 'ajax_process_step_upload' ) );
		add_action( 'wp_ajax_nopriv_' . self::STP_MEASUREMENT_AJAX_ACTION, array( $this, 'ajax_process_step_upload' ) );
		add_action( 'init', array( $this, 'schedule_step_processing_cleanup' ) );
		add_action( self::STP_PROCESSING_CLEANUP_HOOK, array( $this, 'cleanup_abandoned_processing_uploads' ) );

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
		if ( intval( $form['id'] ) === intval( $this->quote_form_id ) ) {
			$created_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
			if ( ! empty( $created_posts ) ) {
				$post_id = array_first( $created_posts );
				$Lead = PC_CPQ()->lead( $post_id );
				if ( ! $Lead->has_customer() || ( $Lead->has_customer() && ! $Lead->get_Customer()->has_completed_profile() ) ) {
					$this->save_customer_data( $post_id, null, $entry, $form );
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
				$data = array(
					'name' => rgar( $entry, 1 ),
					'website' => rgar( $entry, 3 ),
					'phone' => rgar( $entry, 6 ),
					'fax' => rgar( $entry, 7 ),
					'billing_street_address' => rgar( $entry, '4.1' ),
					'billing_street_address_2' => rgar( $entry, '4.2' ),
					'billing_city' => rgar( $entry, '4.3' ),
					'billing_state' => rgar( $entry, '4.4' ),
					'billing_zip' => rgar( $entry, '4.5' ),
					'billing_country' => rgar( $entry, '4.6' ),
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
		$field_id = self::PARTS_DATA_FIELD_ID;
		$parts = json_decode( rgar( $entry, $field_id ), true );
		$data = array();

		if ( ! empty( $parts ) ) {
			$i = 0;
			foreach ( $parts as $part_arr ) {
				$data[$i] = array();
				$pricing = [];
				foreach ( $part_arr as $key => $value ) {
					$new_key = decamelize( $key );
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

						case 'quantities':
							if ( ! empty( $value ) ) {
								$value = array_values( array_filter( array_map( function ( $quantity ) {
									if ( ! isset( $quantity['breakPoint'] ) || '' === $quantity['breakPoint'] ) {
										return null;
									}

									return [
										'break_point' => $quantity['breakPoint'],
									];
								}, $value ) ) );
							}
							break;

						case 'price_unit':
							$pricing['price_unit'] = Part_Pricing_Inputs::sanitize_price_unit( (string) $value );
							continue 2;
					}
					$data[$i][$new_key] = $value;
				}

				if ( ! empty( $pricing ) ) {
					$data[$i]['pricing'] = $pricing;
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
					if ( ! empty( $Part->get_Quantities() ) ) {
						continue;
					}

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

	public function update_lead_fee_data( $post_id, $feed, $entry, $form )
	{
		$Lead = PC_CPQ()->lead( $post_id );
		$Lead->update_prop( 'fees', $Lead->get_fees() );
		$Lead->sync_legacy_certification_fee_flag();
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
		$Customer = false;
		$match_type = 'none';
		$company = trim( (string) rgar( $entry, 4 ) );
		$email = trim( (string) rgar( $entry, 3 ) );
		if ( $company ) {
			$Customer = Customer::get_customer_by( 'name', $company );
			if ( $Customer ) {
				$match_type = 'company';
			}
		}
		
		// Only fall back to email when no company was supplied.
		// The same contact can submit quotes for multiple companies, and
		// matching by email in that case can attach a new lead to the wrong customer.
		if ( ! $Customer && ! $company ) {
			$Customer = Customer::get_customer_by( 'email', $email );
			if ( $Customer ) {
				$match_type = 'email';
			}
		}
		
		if ( ! $Customer ) {
			$CustomerModel = new Customer();
			$CustomerModel->set_props( [
				'name' => rgar( $entry, 4 ),
				'phone' => rgar( $entry, 26 ),
			] );
			$Customer = $CustomerModel->save();
			$match_type = 'created';
		}

		$this->log_customer_assignment( $post_id, $Customer, $match_type, $company, $email );
		
		$this->update_customer_contacts( $entry, $Customer );
		$this->assign_customer_to_lead( $post_id, $Customer );
	}

	private function log_customer_assignment( $lead_id, \PC_CPQ\Models\Customer $Customer, $match_type, $company, $email )
	{
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		error_log( sprintf(
			'[PC_CPQ customer assignment] lead_id=%d customer_id=%d match=%s company="%s" email="%s"',
			(int) $lead_id,
			(int) $Customer->get_id(),
			sanitize_key( $match_type ),
			sanitize_text_field( (string) $company ),
			sanitize_email( (string) $email )
		) );
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

		return PC_CPQ()->Settings()->is_whitelisted( $email );
	}

	public function modify_upload_field( $content, $field, $value, $lead_id, $form_id )
	{
		$content = str_ireplace( 'drop files here or', 'Drag and drop files here or', $content );
		return $content;
	}

	public function schedule_step_processing_cleanup()
	{
		if ( wp_next_scheduled( self::STP_PROCESSING_CLEANUP_HOOK ) ) {
			return;
		}

		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::STP_PROCESSING_CLEANUP_HOOK );
	}

	public function stage_step_upload( $form, $field, $uploaded_filename, $tmp_file_name, $file_path )
	{
		if ( ! $this->should_handle_step_upload( $form, $field, $uploaded_filename ) ) {
			return;
		}

		$metadata = array(
			'form_id' => absint( rgar( $form, 'id' ) ),
			'field_id' => absint( rgar( $field, 'id' ) ),
			'uploaded_filename' => sanitize_file_name( wp_basename( (string) $uploaded_filename ) ),
			'tmp_filename' => sanitize_file_name( wp_basename( (string) $tmp_file_name ) ),
			'gf_tmp_path' => $this->sanitize_local_path( $file_path ),
			'gf_tmp_exists_before_copy' => file_exists( $file_path ),
			'created_at' => time(),
			'status' => 'staged',
		);

		$this->gf_log(
			'debug',
			'Staging STP upload after Gravity Forms async upload.',
			$metadata
		);

		if ( empty( $metadata['gf_tmp_exists_before_copy'] ) ) {
			$this->gf_log( 'error', 'Gravity Forms temp upload missing before staging copy.', $metadata );
			return;
		}

		$processing_dir = $this->get_processing_upload_dir( $metadata['form_id'], $metadata['field_id'], $metadata['tmp_filename'] );
		if ( is_wp_error( $processing_dir ) ) {
			$this->gf_log( 'error', 'Unable to build STP processing directory.', array_merge( $metadata, array(
				'error' => $processing_dir->get_error_message(),
			) ) );
			return;
		}

		if ( ! wp_mkdir_p( $processing_dir ) ) {
			$this->gf_log( 'error', 'Unable to create STP processing directory.', array_merge( $metadata, array(
				'processing_dir' => $this->sanitize_local_path( $processing_dir ),
			) ) );
			return;
		}

		$extension = strtolower( pathinfo( $metadata['uploaded_filename'], PATHINFO_EXTENSION ) );
		$base_name = sanitize_file_name( pathinfo( $metadata['uploaded_filename'], PATHINFO_FILENAME ) );
		$persistent_filename = wp_unique_filename( $processing_dir, $base_name . '-' . substr( md5( $metadata['tmp_filename'] ), 0, 12 ) . '.' . $extension );
		$persistent_path = trailingslashit( $processing_dir ) . $persistent_filename;
		$copy_success = @copy( $file_path, $persistent_path );

		$metadata['processing_copy_path'] = $this->sanitize_local_path( $persistent_path );
		$metadata['processing_copy_exists'] = file_exists( $persistent_path );
		$metadata['copy_success'] = (bool) $copy_success && ! empty( $metadata['processing_copy_exists'] );

		if ( ! $metadata['copy_success'] ) {
			$this->gf_log( 'error', 'Failed to create persistent STP processing copy.', $metadata );
			return;
		}

		$this->write_processing_metadata( $processing_dir, $metadata );
		$this->gf_log( 'debug', 'Persistent STP processing copy created.', $metadata );
	}

	public function ajax_process_step_upload()
	{
		if ( ! check_ajax_referer( self::STP_MEASUREMENT_AJAX_ACTION, 'nonce', false ) ) {
			$this->gf_log( 'error', 'STP processing AJAX nonce validation failed.' );
			wp_send_json_error( array( 'message' => 'Invalid request.' ), 403 );
		}

		$form_id = absint( rgpost( 'form_id' ) );
		$field_id = absint( rgpost( 'field_id' ) );
		$tmp_filename = sanitize_file_name( wp_basename( (string) rgpost( 'temp_filename' ) ) );
		$uploaded_filename = sanitize_file_name( wp_basename( (string) rgpost( 'uploaded_filename' ) ) );

		if ( ! $this->is_step_filename( $uploaded_filename ) ) {
			wp_send_json_error( array( 'message' => 'Unsupported file type.' ), 400 );
		}

		if ( absint( $form_id ) !== absint( $this->quote_form_id ) || absint( $field_id ) !== self::STP_UPLOAD_FIELD_ID ) {
			wp_send_json_error( array( 'message' => 'Unsupported upload field.' ), 400 );
		}

		$processing_dir = $this->get_processing_upload_dir( $form_id, $field_id, $tmp_filename );
		if ( is_wp_error( $processing_dir ) ) {
			$this->gf_log( 'error', 'Invalid processing directory requested for STP AJAX processing.', array(
				'form_id' => $form_id,
				'field_id' => $field_id,
				'tmp_filename' => $tmp_filename,
			) );
			wp_send_json_error( array( 'message' => 'File could not be prepared.' ), 400 );
		}

		$metadata = $this->read_processing_metadata( $processing_dir );
		if ( empty( $metadata ) ) {
			$this->gf_log( 'error', 'No staged STP metadata found for AJAX processing.', array(
				'form_id' => $form_id,
				'field_id' => $field_id,
				'tmp_filename' => $tmp_filename,
				'uploaded_filename' => $uploaded_filename,
			) );
			wp_send_json_error( array( 'message' => 'File staging metadata is missing.' ), 404 );
		}

		$persistent_path = isset( $metadata['processing_copy_path'] ) ? $metadata['processing_copy_path'] : '';
		$persistent_exists = $persistent_path && file_exists( $persistent_path );

		$this->gf_log( 'debug', 'Starting STP measurement API processing.', array(
			'form_id' => $form_id,
			'field_id' => $field_id,
			'tmp_filename' => $tmp_filename,
			'uploaded_filename' => $uploaded_filename,
			'gf_tmp_path' => isset( $metadata['gf_tmp_path'] ) ? $metadata['gf_tmp_path'] : '',
			'gf_tmp_exists_before_api' => ! empty( $metadata['gf_tmp_path'] ) && file_exists( $metadata['gf_tmp_path'] ),
			'processing_copy_path' => $this->sanitize_local_path( $persistent_path ),
			'processing_copy_exists' => $persistent_exists,
		) );

		if ( ! $persistent_exists ) {
			$this->gf_log( 'error', 'Persistent STP processing copy missing before API request.', $metadata );
			wp_send_json_error( array( 'message' => 'Processing copy is missing.' ), 404 );
		}

		if ( ! empty( $metadata['measurement'] ) ) {
			wp_send_json_success( array( 'measurement' => $metadata['measurement'] ) );
		}

		$result = $this->request_step_measurements( $persistent_path, $uploaded_filename );
		$metadata['processing_copy_exists_after_api'] = file_exists( $persistent_path );

		if ( is_wp_error( $result ) ) {
			$metadata['status'] = 'api_failed';
			$metadata['api_error'] = $result->get_error_message();
			$this->write_processing_metadata( $processing_dir, $metadata );
			$this->gf_log( 'error', 'STP measurement API request failed.', array(
				'form_id' => $form_id,
				'field_id' => $field_id,
				'tmp_filename' => $tmp_filename,
				'uploaded_filename' => $uploaded_filename,
				'processing_copy_exists_after_api' => $metadata['processing_copy_exists_after_api'],
				'error' => $result->get_error_message(),
			) );
			wp_send_json_error( array( 'message' => 'Measurement processing failed.' ), 500 );
		}

		$measurement = array_first( $result['filesInfo'] );
		$metadata['measurement'] = is_array( $measurement ) ? $measurement : array();
		$metadata['status'] = 'processed';
		$metadata['processed_at'] = time();
		$this->write_processing_metadata( $processing_dir, $metadata );

		$this->gf_log( 'debug', 'STP measurement API processing completed successfully.', array(
			'form_id' => $form_id,
			'field_id' => $field_id,
			'tmp_filename' => $tmp_filename,
			'uploaded_filename' => $uploaded_filename,
			'processing_copy_exists_after_api' => $metadata['processing_copy_exists_after_api'],
			'api_success' => true,
		) );

		wp_send_json_success( array( 'measurement' => $metadata['measurement'] ) );
	}

	public function recover_staged_step_uploads( $entry, $form )
	{
		if ( absint( rgar( $form, 'id' ) ) !== absint( $this->quote_form_id ) ) {
			return;
		}

		$field_id = self::STP_UPLOAD_FIELD_ID;
		$field = $this->get_form_field( $form, $field_id );
		$entry_value = rgar( $entry, (string) $field_id );
		$current_urls = $this->get_file_urls_from_entry_value( $field, $entry_value );
		$current_step_urls = array_values( array_filter( $current_urls, array( $this, 'is_step_filename' ) ) );
		$submitted_uploads = $this->get_submitted_uploads_for_field( $field_id );

		$this->gf_log( 'debug', 'Inspecting final Gravity Forms STP upload state.', array(
			'entry_id' => absint( rgar( $entry, 'id' ) ),
			'field_id' => $field_id,
			'final_field_value' => $entry_value,
			'current_step_urls' => $current_step_urls,
			'submitted_upload_count' => count( $submitted_uploads ),
		) );

		if ( empty( $submitted_uploads ) ) {
			return;
		}

		$current_existing_paths = array();
		foreach ( $current_step_urls as $url ) {
			$current_existing_paths[ $url ] = $this->get_gravity_forms_file_path_from_url( $url );
		}

		$submitted_step_uploads = array_values( array_filter( $submitted_uploads, function ( $upload ) {
			return $this->is_step_filename( rgar( $upload, 'uploaded_filename' ) );
		} ) );
		$existing_permanent_step_count = count( array_filter( $current_existing_paths, function ( $path ) {
			return $path && file_exists( $path );
		} ) );

		if ( $existing_permanent_step_count >= count( $submitted_step_uploads ) ) {
			foreach ( $submitted_step_uploads as $upload ) {
				$processing_dir = $this->get_processing_upload_dir(
					absint( rgar( $form, 'id' ) ),
					$field_id,
					sanitize_file_name( wp_basename( (string) rgar( $upload, 'temp_filename' ) ) )
				);

				if ( ! is_wp_error( $processing_dir ) ) {
					$this->delete_processing_directory( $processing_dir, 'Gravity Forms retained all submitted STP files.' );
				}
			}

			$this->gf_log( 'debug', 'Gravity Forms retained all submitted STP files; recovery not required.', array(
				'entry_id' => absint( rgar( $entry, 'id' ) ),
				'current_step_file_count' => $existing_permanent_step_count,
				'submitted_step_file_count' => count( $submitted_step_uploads ),
			) );
			return;
		}

		$recovered = false;
		foreach ( $submitted_uploads as $upload ) {
			$uploaded_filename = sanitize_file_name( wp_basename( (string) rgar( $upload, 'uploaded_filename' ) ) );
			$tmp_filename = sanitize_file_name( wp_basename( (string) rgar( $upload, 'temp_filename' ) ) );

			if ( ! $this->is_step_filename( $uploaded_filename ) ) {
				continue;
			}

			$existing_match = $this->find_existing_step_url_for_uploaded_filename( $current_existing_paths, $uploaded_filename );
			$processing_dir = $this->get_processing_upload_dir( absint( rgar( $form, 'id' ) ), $field_id, $tmp_filename );
			$metadata = is_wp_error( $processing_dir ) ? array() : $this->read_processing_metadata( $processing_dir );

			$this->gf_log( 'debug', 'Verifying final STP upload persistence for entry.', array(
				'entry_id' => absint( rgar( $entry, 'id' ) ),
				'uploaded_filename' => $uploaded_filename,
				'tmp_filename' => $tmp_filename,
				'permanent_match_url' => $existing_match,
				'permanent_match_exists' => $existing_match ? file_exists( (string) $current_existing_paths[ $existing_match ] ) : false,
				'recovery_required' => empty( $existing_match ),
			) );

			if ( $existing_match ) {
				$this->delete_processing_directory( $processing_dir, 'Permanent Gravity Forms file already exists.' );
				continue;
			}

			if ( empty( $metadata ) || empty( $metadata['processing_copy_path'] ) || ! file_exists( $metadata['processing_copy_path'] ) ) {
				$this->gf_log( 'error', 'Unable to recover missing STP upload because the persistent copy is unavailable.', array(
					'entry_id' => absint( rgar( $entry, 'id' ) ),
					'uploaded_filename' => $uploaded_filename,
					'tmp_filename' => $tmp_filename,
				) );
				continue;
			}

			$target = GFFormsModel::get_file_upload_path( absint( rgar( $form, 'id' ) ), $uploaded_filename );
			$target_path = rgar( $target, 'path' );
			$target_url = rgar( $target, 'url' );

			if ( empty( $target_path ) || empty( $target_url ) ) {
				$this->gf_log( 'error', 'Gravity Forms did not provide a valid permanent upload target during STP recovery.', array(
					'entry_id' => absint( rgar( $entry, 'id' ) ),
					'uploaded_filename' => $uploaded_filename,
				) );
				continue;
			}

			if ( ! wp_mkdir_p( dirname( $target_path ) ) ) {
				$this->gf_log( 'error', 'Failed to create Gravity Forms permanent upload directory during STP recovery.', array(
					'entry_id' => absint( rgar( $entry, 'id' ) ),
					'uploaded_filename' => $uploaded_filename,
					'target_path' => $this->sanitize_local_path( $target_path ),
				) );
				continue;
			}

			$recovery_success = file_exists( $target_path ) ? true : @copy( $metadata['processing_copy_path'], $target_path );
			$target_exists = file_exists( $target_path );

			$this->gf_log( 'debug', 'Attempted STP recovery into Gravity Forms permanent upload location.', array(
				'entry_id' => absint( rgar( $entry, 'id' ) ),
				'uploaded_filename' => $uploaded_filename,
				'target_path' => $this->sanitize_local_path( $target_path ),
				'target_exists' => $target_exists,
				'recovery_success' => (bool) $recovery_success && $target_exists,
			) );

			if ( ! $recovery_success || ! $target_exists ) {
				continue;
			}

			$current_urls[] = $target_url;
			$current_step_urls[] = $target_url;
			$current_existing_paths[ $target_url ] = $target_path;
			$recovered = true;

			$this->delete_processing_directory( $processing_dir, 'Recovered missing STP upload from persistent copy.' );
		}

		if ( $recovered ) {
			$updated_value = wp_json_encode( array_values( array_unique( $current_urls ) ) );
			GFAPI::update_entry_field( $entry['id'], $field_id, $updated_value );
			$this->gf_log( 'debug', 'Updated Gravity Forms entry field value after STP recovery.', array(
				'entry_id' => absint( rgar( $entry, 'id' ) ),
				'field_id' => $field_id,
				'final_field_value' => $updated_value,
			) );
		}
	}

	public function cleanup_abandoned_processing_uploads()
	{
		$base_dir = $this->get_processing_base_dir();
		if ( is_wp_error( $base_dir ) || ! is_dir( $base_dir ) ) {
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $base_dir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		$now = time();
		$deleted = array();

		foreach ( $iterator as $item ) {
			if ( ! $item->isDir() ) {
				continue;
			}

			$metadata = $this->read_processing_metadata( $item->getPathname() );
			if ( empty( $metadata ) ) {
				continue;
			}

			$created_at = absint( rgar( $metadata, 'created_at' ) );
			if ( ! $created_at || ( $now - $created_at ) < self::STP_PROCESSING_MAX_AGE ) {
				continue;
			}

			$deleted[] = $this->sanitize_local_path( $item->getPathname() );
			$this->delete_processing_directory( $item->getPathname(), 'Cleanup of abandoned STP processing copy.' );
		}

		if ( ! empty( $deleted ) ) {
			$this->gf_log( 'debug', 'Completed abandoned STP processing cleanup.', array(
				'deleted_directories' => $deleted,
			) );
		}
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

	private function should_handle_step_upload( $form, $field, $uploaded_filename )
	{
		return absint( rgar( $form, 'id' ) ) === absint( $this->quote_form_id )
			&& absint( rgar( $field, 'id' ) ) === self::STP_UPLOAD_FIELD_ID
			&& $this->is_step_filename( $uploaded_filename );
	}

	private function is_step_filename( $filename )
	{
		return (bool) preg_match( '/\.(stp|step)$/i', (string) $filename );
	}

	private function get_processing_base_dir()
	{
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return new \WP_Error( 'pc_cpq_upload_dir', $upload_dir['error'] );
		}

		return trailingslashit( $upload_dir['basedir'] ) . self::STP_PROCESSING_DIR;
	}

	private function get_processing_upload_dir( $form_id, $field_id, $tmp_filename )
	{
		$base_dir = $this->get_processing_base_dir();
		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$tmp_token = $this->sanitize_storage_token( $tmp_filename );
		if ( '' === $tmp_token ) {
			return new \WP_Error( 'pc_cpq_invalid_tmp_token', 'Invalid temporary filename.' );
		}

		return trailingslashit( $base_dir ) . 'form-' . absint( $form_id ) . DIRECTORY_SEPARATOR . 'field-' . absint( $field_id ) . DIRECTORY_SEPARATOR . $tmp_token;
	}

	private function sanitize_storage_token( $token )
	{
		return trim( preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $token ) );
	}

	private function get_processing_metadata_path( $processing_dir )
	{
		return trailingslashit( $processing_dir ) . self::STP_PROCESSING_META_FILE;
	}

	private function write_processing_metadata( $processing_dir, array $metadata )
	{
		$metadata_path = $this->get_processing_metadata_path( $processing_dir );
		$metadata['processing_dir'] = $this->sanitize_local_path( $processing_dir );
		file_put_contents( $metadata_path, wp_json_encode( $metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	private function read_processing_metadata( $processing_dir )
	{
		if ( empty( $processing_dir ) || ! is_dir( $processing_dir ) ) {
			return array();
		}

		$metadata_path = $this->get_processing_metadata_path( $processing_dir );
		if ( ! file_exists( $metadata_path ) ) {
			return array();
		}

		$raw = file_get_contents( $metadata_path );
		$data = json_decode( (string) $raw, true );

		return is_array( $data ) ? $data : array();
	}

	private function request_step_measurements( $file_path, $uploaded_filename )
	{
		if ( ! function_exists( 'curl_init' ) ) {
			return new \WP_Error( 'pc_cpq_missing_curl', 'The cURL extension is required for STP measurement processing.' );
		}

		$ch = curl_init( self::STP_MEASUREMENT_API_URL );
		$post_fields = array(
			'file' => curl_file_create( $file_path, 'application/octet-stream', $uploaded_filename ),
		);

		curl_setopt_array( $ch, array(
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array(
				'X-API-KEY: ' . self::STP_MEASUREMENT_API_KEY,
			),
			CURLOPT_POSTFIELDS => $post_fields,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
		) );

		$response = curl_exec( $ch );
		$curl_error = curl_error( $ch );
		$status_code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( false === $response ) {
			return new \WP_Error( 'pc_cpq_measurement_api_request_failed', $curl_error ?: 'The measurement request failed.' );
		}

		$body = (string) $response;
		if ( strpos( $body, '[{ "isSuccess"' ) !== false ) {
			$body = preg_replace( '/\[\{/', '[{}],', $body, 1 );
		}

		$decoded = json_decode( $body, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			return new \WP_Error( 'pc_cpq_measurement_api_invalid_json', 'The measurement API returned invalid JSON.' );
		}

		if ( $status_code >= 400 || empty( $decoded['isSuccess'] ) || empty( $decoded['filesInfo'] ) || ! is_array( $decoded['filesInfo'] ) ) {
			return new \WP_Error( 'pc_cpq_measurement_api_unsuccessful', 'The measurement API did not return a successful result.' );
		}

		return $decoded;
	}

	private function get_form_field( $form, $field_id )
	{
		if ( class_exists( 'GFAPI' ) ) {
			$field = \GFAPI::get_field( $form, $field_id );
			if ( $field ) {
				return $field;
			}
		}

		if ( ! empty( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( absint( rgar( $field, 'id' ) ) === absint( $field_id ) ) {
					return $field;
				}
			}
		}

		return null;
	}

	private function get_file_urls_from_entry_value( $field, $value )
	{
		if ( $field && method_exists( $field, 'to_array' ) ) {
			$urls = $field->to_array( $value );
			return is_array( $urls ) ? $urls : array();
		}

		$decoded = json_decode( (string) $value, true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}

		return $value ? array( $value ) : array();
	}

	private function get_submitted_uploads_for_field( $field_id )
	{
		$posted = rgpost( 'gform_uploaded_files' );
		if ( empty( $posted ) ) {
			return array();
		}

		$decoded = json_decode( stripslashes( (string) $posted ), true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		$key = 'input_' . absint( $field_id );
		$uploads = rgar( $decoded, $key );

		return is_array( $uploads ) ? $uploads : array();
	}

	private function get_gravity_forms_file_path_from_url( $url )
	{
		if ( empty( $url ) ) {
			return '';
		}

		if ( method_exists( 'GFFormsModel', 'get_physical_file_path' ) ) {
			return GFFormsModel::get_physical_file_path( $url );
		}

		$uploads = wp_upload_dir();
		if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) ) {
			return '';
		}

		return str_replace( $uploads['baseurl'], $uploads['basedir'], $url );
	}

	private function find_existing_step_url_for_uploaded_filename( array $existing_paths, $uploaded_filename )
	{
		$uploaded_filename = sanitize_file_name( wp_basename( (string) $uploaded_filename ) );
		foreach ( $existing_paths as $url => $path ) {
			$basename = sanitize_file_name( wp_basename( parse_url( $url, PHP_URL_PATH ) ) );
			if ( $basename === $uploaded_filename && $path && file_exists( $path ) ) {
				return $url;
			}
		}

		return '';
	}

	private function delete_processing_directory( $processing_dir, $reason = '' )
	{
		if ( empty( $processing_dir ) || ! is_dir( $processing_dir ) ) {
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $processing_dir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				@rmdir( $item->getPathname() );
				continue;
			}

			@unlink( $item->getPathname() );
		}

		@rmdir( $processing_dir );

		$this->gf_log( 'debug', 'Removed STP processing directory.', array(
			'processing_dir' => $this->sanitize_local_path( $processing_dir ),
			'reason' => $reason,
		) );
	}

	private function sanitize_local_path( $path )
	{
		return str_replace( array( '../', '..\\' ), '', (string) $path );
	}

	private function gf_log( $level, $message, array $context = array() )
	{
		$line = 'PC_CPQ STP workflow: ' . $message;
		if ( ! empty( $context ) ) {
			$line .= ' | ' . wp_json_encode( $context );
		}

		if ( class_exists( 'GFCommon' ) && method_exists( 'GFCommon', 'log_debug' ) ) {
			if ( 'error' === $level && method_exists( 'GFCommon', 'log_error' ) ) {
				GFCommon::log_error( $line );
				return;
			}

			GFCommon::log_debug( $line );
			return;
		}

		error_log( $line );
	}

}

PC_CPQ_Forms::instance();
