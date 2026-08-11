<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Abstract_Model;
use PC_CPQ\Core\Pricing\Pricing_Calculator;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Settings extends Abstract_Model
{
	/*
	 * Parts
	 */
	protected $hourly_rate;
	protected $default_margin;
	protected $default_eff;
	protected $default_people;
	protected $default_eau;
	protected $default_shift;
	protected $default_break_in;
	protected $default_metal_adder;
	protected $default_pricing_mode;
	protected $onboarding_completed_at;
	protected $enabled_integrations;
	protected $nutshell_account_name;
	protected $nutshell_api_user;
	protected $nutshell_api_key;

	/*
	 * Quotes
	 */
	protected $starting_quote_number;
	protected $quote_expires_after;
	protected $follow_up_after;
	protected $domain_whitelist;
	protected $email_whitelist;
	
	/*
	 * Templates
	 */
	protected $quote_header;
	protected $quote_footer;
	protected $quote_terms;
	protected $quote_form_id;
	protected $quote_pdf_id;
	protected $routing_pdf_id;
	protected $quote_pdf_layout;
	protected $routing_pdf_layout;
	protected $custom_site_head;
	protected $custom_site_css;
	protected $custom_site_body;
	protected static $Email_Template_Class = 'PC_CPQ\Models\Settings\Email_Template';
	protected $raw_email_templates;
	protected $Email_Templates;
	
	
	/*
	 * Plating
	 */
	protected static $Metal_Class = 'PC_CPQ\Models\Settings\Metal';
	protected $raw_metals;
	protected $Metals;
	protected static $Plating_Metal_Class = 'PC_CPQ\Models\Settings\Plating_Metal';
	protected $raw_plating_metals;
	protected $Plating_Metals;
	protected static $Line_Class = 'PC_CPQ\Models\Settings\Line';
	protected $raw_lines;
	protected $Lines;
	protected static $Barrel_Class = 'PC_CPQ\Models\Settings\Barrel';
	protected $raw_barrels;
	protected $Barrels;
	protected static $Rack_Class = 'PC_CPQ\Models\Settings\Rack';
	protected $raw_racks;
	protected $Racks;
	protected static $Operation_Class = 'PC_CPQ\Models\Settings\Operation';
	protected $raw_operations;
	protected $Operations;
	protected $raw_recipes;
	protected $Post_Operations;
	protected $post_ops_order;
	
	/*
	 * Fees
	 */
	protected static $Fee_Class = 'PC_CPQ\Models\Settings\Fee';
	protected $raw_fees;
	protected $Fees;

	/*
	 * Getters
	 */

	public function get_hourly_rate()
	{
		return $this->get_float_prop( 'hourly_rate' );
	}

	public function get_default_margin()
	{
		return $this->get_float_prop( 'default_margin' );
	}

	public function get_default_eff()
	{
		return $this->get_float_prop( 'default_eff' );
	}

	public function get_default_people()
	{
		return $this->get_float_prop( 'default_people' );
	}

	public function get_default_eau()
	{
		return $this->get_float_prop( 'default_eau' );
	}

	public function get_default_shift()
	{
		return $this->get_int_prop( 'default_shift' );
	}

	public function get_default_break_in()
	{
		return $this->get_float_prop( 'default_break_in' );
	}

	public function get_default_metal_adder()
	{
		return $this->get_float_prop( 'default_metal_adder' );
	}

	public function get_default_pricing_mode()
	{
		if ( null === $this->default_pricing_mode || '' === $this->default_pricing_mode || false === $this->default_pricing_mode ) {
			$this->default_pricing_mode = $this->get_meta( 'default_pricing_mode' );
		}

		return Pricing_Calculator::sanitize_mode( $this->get_string_prop( 'default_pricing_mode', Pricing_Calculator::MODE_UTILIZATION ) );
	}

	public function get_onboarding_completed_at()
	{
		return $this->get_string_prop( 'onboarding_completed_at' );
	}

	public function get_enabled_integrations()
	{
		if ( null === $this->enabled_integrations || false === $this->enabled_integrations ) {
			$this->enabled_integrations = $this->get_meta( 'enabled_integrations' );
		}

		$value = $this->get_prop( 'enabled_integrations' );
		if ( empty( $value ) ) {
			return array();
		}

		if ( is_array( $value ) ) {
			return array_values( array_filter( array_map( 'sanitize_key', $value ) ) );
		}

		$single = sanitize_key( (string) $value );
		return '' !== $single ? array( $single ) : array();
	}

	public function get_nutshell_account_name()
	{
		if ( null === $this->nutshell_account_name || false === $this->nutshell_account_name ) {
			$this->nutshell_account_name = $this->get_meta( 'nutshell_account_name' );
		}

		return $this->get_string_prop( 'nutshell_account_name' );
	}

	public function get_nutshell_api_user()
	{
		if ( null === $this->nutshell_api_user || false === $this->nutshell_api_user ) {
			$this->nutshell_api_user = $this->get_meta( 'nutshell_api_user' );
		}

		return $this->get_string_prop( 'nutshell_api_user' );
	}

	public function get_nutshell_api_key()
	{
		if ( null === $this->nutshell_api_key || false === $this->nutshell_api_key ) {
			$this->nutshell_api_key = $this->get_meta( 'nutshell_api_key' );
		}

		return $this->get_string_prop( 'nutshell_api_key' );
	}
	
	public function get_starting_quote_number()
	{
		return $this->get_digit_string_prop( 'starting_quote_number' );
	}
	
	public function get_quote_expires_after()
	{
		return $this->get_int_prop( 'quote_expires_after' );
	}
	
	public function get_follow_up_after()
	{
		return $this->get_int_prop( 'follow_up_after' );
	}
	
	public function get_domain_whitelist()
	{
		return $this->get_string_prop( 'domain_whitelist' );
	}
	
	public function get_email_whitelist()
	{
		return $this->get_string_prop( 'email_whitelist' );
	}
	
	public function get_quote_header()
	{
		return $this->get_string_prop( 'quote_header' );
	}
	
	public function get_quote_footer()
	{
		return $this->get_string_prop( 'quote_footer' );
	}
	
	public function get_quote_terms()
	{
		return $this->get_string_prop( 'quote_terms' );
	}

	public function get_quote_form_id()
	{
		return $this->get_int_prop( 'quote_form_id' );
	}

	public function get_quote_pdf_id()
	{
		return $this->get_string_prop( 'quote_pdf_id' );
	}

	public function get_routing_pdf_id()
	{
		return $this->get_string_prop( 'routing_pdf_id' );
	}

	public function get_quote_pdf_layout()
	{
		$value = sanitize_key( $this->get_string_prop( 'quote_pdf_layout', 'standard' ) );
		return in_array( $value, array( 'standard', 'compact' ), true ) ? $value : 'standard';
	}

	public function get_routing_pdf_layout()
	{
		$value = sanitize_key( $this->get_string_prop( 'routing_pdf_layout', 'standard' ) );
		return in_array( $value, array( 'standard', 'compact' ), true ) ? $value : 'standard';
	}

	public function get_custom_site_head()
	{
		return $this->get_string_prop( 'custom_site_head' );
	}

	public function get_custom_site_css()
	{
		return $this->get_string_prop( 'custom_site_css' );
	}

	public function get_custom_site_body()
	{
		return $this->get_string_prop( 'custom_site_body' );
	}

	public function get_raw_email_templates()
	{
		if ( null === $this->raw_email_templates ) {
			$this->raw_email_templates = $this->get_collection_meta( 'email_templates' );
		}
		return $this->raw_email_templates;
	}

	public function get_Email_Templates( $force_update = false )
	{
		if ( null === $this->Email_Templates || $force_update ) {
			$this->Email_Templates = array();
			if ( ! empty( $this->get_raw_email_templates() ) ) {
				foreach ( $this->get_raw_email_templates() as $index => $raw_email_template ) {
					$this->add_Email_Template( $index, $raw_email_template, false );
				}
			}
		}
		return $this->Email_Templates;
	}
	
	public function get_raw_metals()
	{
		if ( null === $this->raw_metals ) {
			$this->raw_metals = $this->get_collection_meta( 'metals' );
		}
		return $this->raw_metals;
	}

	public function get_Metals( $force_update = false )
	{
		if ( null === $this->Metals || $force_update ) {
			$this->Metals = array();
			if ( ! empty( $this->get_raw_metals() ) ) {
				foreach ( $this->get_raw_metals() as $index => $raw_metal ) {
					$this->add_Metal( $index, $raw_metal, false );
				}
			}
		}
		return $this->Metals;
	}
	
	public function get_raw_plating_metals()
	{
		if ( null === $this->raw_plating_metals ) {
			$this->raw_plating_metals = $this->get_collection_meta( 'plating_metals' );
		}
		return $this->raw_plating_metals;
	}

	public function get_Plating_Metals( $force_update = false )
	{
		if ( null === $this->Plating_Metals || $force_update ) {
			$this->Plating_Metals = array();
			if ( ! empty( $this->get_raw_plating_metals() ) ) {
				foreach ( $this->get_raw_plating_metals() as $index => $raw_plating_metal ) {
					$this->add_Plating_Metal( $index, $raw_plating_metal, false );
				}
			}
		}
		return $this->Plating_Metals;
	}
	
	public function get_Available_Plating_Metals()
	{
		return array_filter( $this->get_Plating_Metals(), function( $Plating_Metal ) {
			return ! $Plating_Metal->is_hidden();
		} );
	}

	public function get_metal_names()
	{
		return $this->get_collection_column( $this->get_raw_metals(), 'name' );
	}

	public function get_plating_metal_names( $include_hidden = true )
	{
		$metals = $include_hidden ? $this->get_raw_plating_metals() : $this->get_available_plating_metal_rows();
		return $this->get_collection_column( $metals, 'name' );
	}
	
	public function get_raw_lines()
	{
		if ( null === $this->raw_lines ) {
			$this->raw_lines = $this->get_collection_meta( 'lines' );
		}
		return $this->raw_lines;
	}

	public function get_Lines( $force_update = false )
	{
		if ( null === $this->Lines || $force_update ) {
			$this->Lines = array();
			if ( ! empty( $this->get_raw_lines() ) ) {
				foreach ( $this->get_raw_lines() as $index => $raw_line ) {
					$this->add_Line( $index, $raw_line, false );
				}
			}
		}
		return $this->Lines;
	}

	public function get_line_names()
	{
		return $this->get_collection_column( $this->get_raw_lines(), 'name' );
	}
	
	public function get_raw_barrels()
	{
		if ( null === $this->raw_barrels ) {
			$this->raw_barrels = $this->get_collection_meta( 'barrels' );
		}
		return $this->raw_barrels;
	}

	public function get_Barrels( $force_update = false )
	{
		if ( null === $this->Barrels || $force_update ) {
			$this->Barrels = array();
			if ( ! empty( $this->get_raw_barrels() ) ) {
				foreach ( $this->get_raw_barrels() as $index => $raw_barrel ) {
					$this->add_Barrel( $index, $raw_barrel, false );
				}
			}
		}
		return $this->Barrels;
	}

	public function get_barrel_names()
	{
		return $this->get_collection_column( $this->get_raw_barrels(), 'name' );
	}
	
	public function get_raw_racks()
	{
		if ( null === $this->raw_racks ) {
			$this->raw_racks = $this->get_collection_meta( 'racks' );
		}
		return $this->raw_racks;
	}

	public function get_Racks( $force_update = false )
	{
		if ( null === $this->Racks || $force_update ) {
			$this->Racks = array();
			if ( ! empty( $this->get_raw_racks() ) ) {
				foreach ( $this->get_raw_racks() as $index => $raw_rack ) {
					$this->add_Rack( $index, $raw_rack, false );
				}
			}
		}
		return $this->Racks;
	}

	public function get_rack_names()
	{
		return $this->get_collection_column( $this->get_raw_racks(), 'name' );
	}
	
	public function get_raw_operations()
	{
		if ( null === $this->raw_operations ) {
			$this->raw_operations = $this->get_collection_meta( 'operations' );
		}
		return $this->raw_operations;
	}

	public function get_Operations( $force_update = false )
	{
		if ( null === $this->Operations || $force_update ) {
			$this->Operations = array();
			if ( ! empty( $this->get_raw_operations() ) ) {
				foreach ( $this->get_raw_operations() as $index => $raw_operation ) {
					$this->add_Operation( $index, $raw_operation, false );
				}
			}
		}
		return $this->Operations;
	}

	public function get_operation_names()
	{
		return $this->get_collection_column( $this->get_raw_operations(), 'operation' );
	}

	public function get_operations_config()
	{
		return $this->normalize_snapshot_rows( $this->get_raw_operations() );
	}

	public function get_Post_Operations()
	{
		if ( null === $this->Post_Operations ) {
			$this->Post_Operations = array();
			$post_operations = array_filter( $this->get_Operations(), function( $Operation ) {
				return $Operation->get_type() == 'Post';
			} );
			
			if ( $this->get_post_ops_order() ) {
				$order = json_decode( $this->get_post_ops_order(), true );
				foreach ( $order as $item ) {
					$this->Post_Operations[] = array_first( array_filter( $post_operations, function( $Operation ) use ( $item ) {
						return $Operation->get_operation() == $item['operation'];
					} ) );
				}
			} else {
				$this->Post_Operations = array_values( $post_operations );
			}
		}
		return $this->Post_Operations;
	}
	
	public function get_post_ops_order()
	{
		return $this->get_prop( 'post_ops_order' );
	}

	public function get_raw_recipes()
	{
		if ( null === $this->raw_recipes ) {
			$this->raw_recipes = $this->get_collection_meta( 'recipes' );
		}
		return $this->raw_recipes;
	}

	public function get_recipes_by_base_metal( $base_metal )
	{
		return $this->filter_collection_rows( array( 'base_metal' => $base_metal ), $this->get_raw_recipes() );
	}
	
	public function get_raw_fees()
	{
		if ( null === $this->raw_fees ) {
			$this->raw_fees = $this->get_collection_meta( 'fees' );
		}
		return $this->raw_fees;
	}

	public function get_Fees( $force_update = false )
	{
		if ( null === $this->Fees || $force_update ) {
			$this->Fees = array();
			if ( ! empty( $this->get_raw_fees() ) ) {
				foreach ( $this->get_raw_fees() as $index => $raw_fee ) {
					$this->add_Fee( $index, $raw_fee, false );
				}
			}
		}
		return $this->Fees;
	}

	public function get_email_template_names()
	{
		return $this->get_collection_column( $this->get_raw_email_templates(), 'name' );
	}

	public function get_email_templates_config()
	{
		return $this->normalize_snapshot_rows( $this->get_raw_email_templates() );
	}

	public function get_available_plating_metal_rows()
	{
		return $this->filter_collection_rows( array( 'hide' => 0 ), $this->get_raw_plating_metals() );
	}

	public function get_pricing_defaults_snapshot()
	{
		return array(
			'hourly_rate' => $this->get_hourly_rate(),
			'default_margin' => $this->get_default_margin(),
			'default_eff' => $this->get_default_eff(),
			'default_people' => $this->get_default_people(),
			'default_eau' => $this->get_default_eau(),
			'default_shift' => $this->get_default_shift(),
			'default_break_in' => $this->get_default_break_in(),
			'default_metal_adder' => $this->get_default_metal_adder(),
			'default_pricing_mode' => $this->get_default_pricing_mode(),
		);
	}

	public function get_quote_defaults_snapshot()
	{
		return array(
			'starting_quote_number' => $this->get_starting_quote_number(),
			'quote_expires_after' => $this->get_quote_expires_after(),
			'follow_up_after' => $this->get_follow_up_after(),
		);
	}

	public function get_quote_content_snapshot()
	{
		return array(
			'quote_header' => $this->get_quote_header(),
			'quote_footer' => $this->get_quote_footer(),
			'quote_terms' => $this->get_quote_terms(),
		);
	}

	public function get_pdf_snapshot()
	{
		return array(
			'quote_form_id' => $this->get_quote_form_id(),
			'quote_pdf_id' => $this->get_quote_pdf_id(),
			'routing_pdf_id' => $this->get_routing_pdf_id(),
			'quote_pdf_layout' => $this->get_quote_pdf_layout(),
			'routing_pdf_layout' => $this->get_routing_pdf_layout(),
		);
	}

	public function get_access_snapshot()
	{
		return array(
			'domain_whitelist' => $this->get_domain_whitelist_entries(),
			'email_whitelist' => $this->get_email_whitelist_entries(),
		);
	}

	public function get_pricing_reference_snapshot()
	{
		return array(
			'metals' => $this->normalize_snapshot_rows( $this->get_raw_metals() ),
			'plating_metals' => $this->normalize_snapshot_rows( $this->get_raw_plating_metals() ),
			'lines' => $this->normalize_snapshot_rows( $this->get_raw_lines() ),
			'barrels' => $this->normalize_snapshot_rows( $this->get_raw_barrels() ),
			'racks' => $this->normalize_snapshot_rows( $this->get_raw_racks() ),
			'operations' => $this->normalize_snapshot_rows( $this->get_raw_operations() ),
			'recipes' => $this->normalize_snapshot_rows( $this->get_raw_recipes() ),
			'fees' => $this->normalize_snapshot_rows( $this->get_raw_fees() ),
		);
	}

	public function get_quote_snapshot_export()
	{
		$snapshot = array(
			'captured_at' => function_exists( 'current_time' ) ? current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' ),
			'plugin_version' => defined( 'PC_CPQ_VERSION' ) ? PC_CPQ_VERSION : null,
			'pricing_defaults' => $this->get_pricing_defaults_snapshot(),
			'quote_defaults' => $this->get_quote_defaults_snapshot(),
			'quote_content' => $this->get_quote_content_snapshot(),
			'pdf' => $this->get_pdf_snapshot(),
			'access' => $this->get_access_snapshot(),
			'pricing_reference' => $this->get_pricing_reference_snapshot(),
		);

		$snapshot['fingerprint'] = md5( wp_json_encode( $snapshot ) );

		return $snapshot;
	}

	public function is_integration_enabled( $integration )
	{
		return in_array( sanitize_key( $integration ), $this->get_enabled_integrations(), true );
	}

	public function is_nutshell_enabled()
	{
		return $this->is_integration_enabled( 'nutshell' );
	}

	public function has_nutshell_credentials()
	{
		return '' !== $this->get_nutshell_api_user() && '' !== $this->get_nutshell_api_key();
	}

	public function is_nutshell_configured()
	{
		return $this->is_nutshell_enabled() && $this->has_nutshell_credentials();
	}
	
	/*
	 * Setters
	 */

	public function set_hourly_rate( $value )
	{
		return $this->set_prop( 'hourly_rate', $value );
	}

	public function set_default_margin( $value )
	{
		return $this->set_prop( 'default_margin', $value );
	}

	public function set_default_eff( $value )
	{
		return $this->set_prop( 'default_eff', $value );
	}

	public function set_default_people( $value )
	{
		return $this->set_prop( 'default_people', $value );
	}

	public function set_default_eau( $value )
	{
		return $this->set_prop( 'default_eau', $value );
	}

	public function set_default_shift( $value )
	{
		return $this->set_prop( 'default_shift', $value );
	}

	public function set_default_break_in( $value )
	{
		return $this->set_prop( 'default_break_in', $value );
	}

	public function set_default_metal_adder( $value )
	{
		return $this->set_prop( 'default_metal_adder', $value );
	}

	public function set_default_pricing_mode( $value )
	{
		return $this->set_prop( 'default_pricing_mode', Pricing_Calculator::sanitize_mode( (string) $value ) );
	}

	public function set_onboarding_completed_at( $value )
	{
		return $this->set_prop( 'onboarding_completed_at', $value );
	}

	public function set_enabled_integrations( $value )
	{
		return $this->set_prop( 'enabled_integrations', is_array( $value ) ? array_values( array_filter( array_map( 'sanitize_key', $value ) ) ) : array() );
	}

	public function set_nutshell_account_name( $value )
	{
		return $this->set_prop( 'nutshell_account_name', $value );
	}

	public function set_nutshell_api_user( $value )
	{
		return $this->set_prop( 'nutshell_api_user', $value );
	}

	public function set_nutshell_api_key( $value )
	{
		return $this->set_prop( 'nutshell_api_key', $value );
	}
	
	public function set_starting_quote_number( $value )
	{
		return $this->set_prop( 'starting_quote_number', $value );
	}
	
	public function set_quote_expires_after( $value )
	{
		return $this->set_prop( 'quote_expires_after', $value );
	}
	
	public function set_follow_up_after( $value )
	{
		return $this->set_prop( 'follow_up_after', $value );
	}
	
	public function set_domain_whitelist( $value )
	{
		return $this->set_prop( 'domain_whitelist', $value );
	}
	
	public function set_email_whitelist( $value )
	{
		return $this->set_prop( 'email_whitelist', $value );
	}
	
	public function set_quote_header( $value )
	{
		return $this->set_prop( 'quote_header', $value );
	}
	
	public function set_quote_footer( $value )
	{
		return $this->set_prop( 'quote_footer', $value );
	}
	
	public function set_quote_terms( $value )
	{
		return $this->set_prop( 'quote_terms', $value );
	}

	public function set_quote_form_id( $value )
	{
		return $this->set_prop( 'quote_form_id', absint( $value ) );
	}

	public function set_quote_pdf_id( $value )
	{
		return $this->set_prop( 'quote_pdf_id', trim( (string) $value ) );
	}

	public function set_routing_pdf_id( $value )
	{
		return $this->set_prop( 'routing_pdf_id', trim( (string) $value ) );
	}

	public function set_quote_pdf_layout( $value )
	{
		$value = sanitize_key( (string) $value );
		return $this->set_prop( 'quote_pdf_layout', in_array( $value, array( 'standard', 'compact' ), true ) ? $value : 'standard' );
	}

	public function set_routing_pdf_layout( $value )
	{
		$value = sanitize_key( (string) $value );
		return $this->set_prop( 'routing_pdf_layout', in_array( $value, array( 'standard', 'compact' ), true ) ? $value : 'standard' );
	}

	public function set_custom_site_head( $value )
	{
		return $this->set_prop( 'custom_site_head', $value );
	}

	public function set_custom_site_css( $value )
	{
		return $this->set_prop( 'custom_site_css', $value );
	}

	public function set_custom_site_body( $value )
	{
		return $this->set_prop( 'custom_site_body', $value );
	}
	
	public function set_raw_email_templates( $value )
	{
		return $this->set_prop( 'raw_email_templates', $value );
	}
	
	public function set_raw_metals( $value )
	{
		return $this->set_prop( 'raw_metals', $value );
	}
	
	public function set_raw_plating_metals( $value )
	{
		return $this->set_prop( 'raw_plating_metals', $value );
	}
	
	public function set_raw_lines( $value )
	{
		return $this->set_prop( 'raw_lines', $value );
	}
	
	public function set_raw_barrels( $value )
	{
		return $this->set_prop( 'raw_barrels', $value );
	}
	
	public function set_raw_racks( $value )
	{
		return $this->set_prop( 'raw_racks', $value );
	}
	
	public function set_raw_operations( $value )
	{
		return $this->set_prop( 'raw_operations', $value );
	}

	public function set_raw_recipes( $value )
	{
		return $this->set_prop( 'raw_recipes', $value );
	}
	
	public function set_raw_fees( $value )
	{
		return $this->set_prop( 'raw_fees', $value );
	}
	
	public function set_post_ops_order( $value )
	{
		return $this->set_prop( 'post_ops_order', $value );
	}
	
	/*
	 * Savers
	 */
	
	public function save_raw_email_templates_meta( $value )
	{
		return $this->save_raw_collection_meta( 'email_templates', $value, 'refresh_Email_Templates' );
	}

	public function save_Email_Templates()
	{
		$email_templates = array_map( function ( $Email_Template ) {
			return $Email_Template->to_array();
		}, $this->get_Email_Templates() );

		$this->update_prop( 'raw_email_templates', $email_templates );
	}
	
	public function save_raw_metals_meta( $value )
	{
		return $this->save_raw_collection_meta( 'metals', $value, 'refresh_Metals' );
	}

	public function save_Metals()
	{
		$metals = array_map( function ( $Metal ) {
			return $Metal->to_array();
		}, $this->get_Metals() );

		$this->update_prop( 'raw_metals', $metals );
	}
	
	public function save_raw_plating_metals_meta( $value )
	{
		return $this->save_raw_collection_meta( 'plating_metals', $value, 'refresh_Plating_Metals' );
	}

	public function save_Plating_Metals()
	{
		$plating_metals = array_map( function ( $Plating_Metal ) {
			return $Plating_Metal->to_array();
		}, $this->get_Plating_Metals() );

		$this->update_prop( 'raw_plating_metals', $plating_metals );
	}
	
	public function save_raw_lines_meta( $value )
	{
		return $this->save_raw_collection_meta( 'lines', $value, 'refresh_Lines' );
	}

	public function save_Lines()
	{
		$lines = array_map( function ( $Line ) {
			return $Line->to_array();
		}, $this->get_Lines() );

		$this->update_prop( 'raw_lines', $lines );
	}
	
	public function save_raw_barrels_meta( $value )
	{
		return $this->save_raw_collection_meta( 'barrels', $value, 'refresh_Barrels' );
	}

	public function save_Barrels()
	{
		$barrels = array_map( function ( $Barrel ) {
			return $Barrel->to_array();
		}, $this->get_Barrels() );

		$this->update_prop( 'raw_barrels', $barrels );
	}
	
	public function save_raw_racks_meta( $value )
	{
		return $this->save_raw_collection_meta( 'racks', $value, 'refresh_Racks' );
	}

	public function save_Racks()
	{
		$racks = array_map( function ( $Rack ) {
			return $Rack->to_array();
		}, $this->get_Racks() );

		$this->update_prop( 'raw_racks', $racks );
	}
	
	public function save_raw_operations_meta( $value )
	{
		return $this->save_raw_collection_meta( 'operations', $value, 'refresh_Operations' );
	}

	public function save_raw_recipes_meta( $value )
	{
		return $this->save_raw_collection_meta( 'recipes', $value, 'refresh_raw_recipes' );
	}

	public function save_Operations()
	{
		$operations = array_map( function ( $Operation ) {
			return $Operation->to_array();
		}, $this->get_Operations() );

		$this->update_prop( 'raw_operations', $operations );
	}
	
	public function save_raw_fees_meta( $value )
	{
		return $this->save_raw_collection_meta( 'fees', $value, 'refresh_Fees' );
	}

	public function save_Fees()
	{
		$fees = array_map( function ( $Fee ) {
			return $Fee->to_array();
		}, $this->get_Fees() );

		$this->update_prop( 'raw_fees', $fees );
	}
	
	/*
	 * Helpers
	 */
	
	public function get_Email_Templates_count()
	{
		return count( $this->get_Email_Templates() );
	}

	public function add_Email_Template( $index = null, $raw_email_template = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Email_Templates_count();
		}
		$this->Email_Templates[] = new self::$Email_Template_Class( $index, $raw_email_template );

		if ( $save ) {
			$this->save_Email_Templates();
		}
	}
	
	public function delete_Email_Template( $index )
	{
		$Email_Templates = $this->get_Email_Templates();
		if ( isset( $Email_Templates[$index] ) ) {
			unset( $Email_Templates[$index] );
		}
		$this->Email_Templates = array_values( $Email_Templates );

		$this->save_Email_Templates();
	}
	
	public function refresh_Email_Templates()
	{
		$this->raw_email_templates = null;
		$this->Email_Templates = null;
		return $this->get_Email_Templates( true );
	}
	
	public function get_Metals_count()
	{
		return count( $this->get_Metals() );
	}

	public function add_Metal( $index = null, $raw_metal = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Metals_count();
		}
		$this->Metals[] = new self::$Metal_Class( $index, $raw_metal );

		if ( $save ) {
			$this->save_Metals();
		}
	}
	
	public function delete_Metal( $index )
	{
		$Metals = $this->get_Metals();
		if ( isset( $Metals[$index] ) ) {
			unset( $Metals[$index] );
		}
		$this->Metals = array_values( $Metals );

		$this->save_Metals();
	}
	
	public function refresh_Metals()
	{
		$this->raw_metals = null;
		$this->Metals = null;
		return $this->get_Metals( true );
	}
	
	public function get_Plating_Metals_count()
	{
		return count( $this->get_Plating_Metals() );
	}

	public function add_Plating_Metal( $index = null, $raw_plating_metal = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Plating_Metals_count();
		}
		$this->Plating_Metals[] = new self::$Plating_Metal_Class( $index, $raw_plating_metal );

		if ( $save ) {
			$this->save_Plating_Metals();
		}
	}
	
	public function delete_Plating_Metal( $index )
	{
		$Plating_Metals = $this->get_Plating_Metals();
		if ( isset( $Plating_Metals[$index] ) ) {
			unset( $Plating_Metals[$index] );
		}
		$this->Plating_Metals = array_values( $Plating_Metals );

		$this->save_Plating_Metals();
	}
	
	public function refresh_Plating_Metals()
	{
		$this->raw_plating_metals = null;
		$this->Plating_Metals = null;
		return $this->get_Plating_Metals( true );
	}
	
	public function get_Lines_count()
	{
		return count( $this->get_Lines() );
	}

	public function add_Line( $index = null, $raw_line = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Lines_count();
		}
		$this->Lines[] = new self::$Line_Class( $index, $raw_line );

		if ( $save ) {
			$this->save_Lines();
		}
	}
	
	public function delete_Line( $index )
	{
		$Lines = $this->get_Lines();
		if ( isset( $Lines[$index] ) ) {
			unset( $Lines[$index] );
		}
		$this->Lines = array_values( $Lines );

		$this->save_Lines();
	}
	
	public function refresh_Lines()
	{
		$this->raw_lines = null;
		$this->Lines = null;
		return $this->get_Lines( true );
	}
	
	public function get_Barrels_count()
	{
		return count( $this->get_Barrels() );
	}

	public function add_Barrel( $index = null, $raw_barrel = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Barrels_count();
		}
		$this->Barrels[] = new self::$Barrel_Class( $index, $raw_barrel );

		if ( $save ) {
			$this->save_Barrels();
		}
	}
	
	public function delete_Barrel( $index )
	{
		$Barrels = $this->get_Barrels();
		if ( isset( $Barrels[$index] ) ) {
			unset( $Barrels[$index] );
		}
		$this->Barrels = array_values( $Barrels );

		$this->save_Barrels();
	}
	
	public function refresh_Barrels()
	{
		$this->raw_barrels = null;
		$this->Barrels = null;
		return $this->get_Barrels( true );
	}
	
	public function get_Racks_count()
	{
		return count( $this->get_Racks() );
	}

	public function add_Rack( $index = null, $raw_rack = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Racks_count();
		}
		$this->Racks[] = new self::$Rack_Class( $index, $raw_rack );

		if ( $save ) {
			$this->save_Racks();
		}
	}
	
	public function delete_Rack( $index )
	{
		$Racks = $this->get_Racks();
		if ( isset( $Racks[$index] ) ) {
			unset( $Racks[$index] );
		}
		$this->Racks = array_values( $Racks );

		$this->save_Racks();
	}
	
	public function refresh_Racks()
	{
		$this->raw_racks = null;
		$this->Racks = null;
		return $this->get_Racks( true );
	}
	
	public function get_Operations_count()
	{
		return count( $this->get_Operations() );
	}

	public function add_Operation( $index = null, $raw_operation = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Operations_count();
		}
		$this->Operations[] = new self::$Operation_Class( $index, $raw_operation );

		if ( $save ) {
			$this->save_Operations();
		}
	}
	
	public function delete_Operation( $index )
	{
		$Operations = $this->get_Operations();
		if ( isset( $Operations[$index] ) ) {
			unset( $Operations[$index] );
		}
		$this->Operations = array_values( $Operations );

		$this->save_Operations();
	}
	
	public function refresh_Operations()
	{
		$this->raw_operations = null;
		$this->Operations = null;
		$this->Post_Operations = null;
		return $this->get_Operations( true );
	}
	
	public function get_Fees_count()
	{
		return count( $this->get_Fees() );
	}

	public function add_Fee( $index = null, $raw_fee = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Fees_count();
		}
		$this->Fees[] = new self::$Fee_Class( $index, $raw_fee );

		if ( $save ) {
			$this->save_Fees();
		}
	}
	
	public function delete_Fee( $index )
	{
		$Fees = $this->get_Fees();
		if ( isset( $Fees[$index] ) ) {
			unset( $Fees[$index] );
		}
		$this->Fees = array_values( $Fees );

		$this->save_Fees();
	}
	
	public function refresh_Fees()
	{
		$this->raw_fees = null;
		$this->Fees = null;
		return $this->get_Fees( true );
	}

	public function refresh_raw_recipes()
	{
		$this->raw_recipes = null;
		return $this->get_raw_recipes();
	}

	public function get_collection_column( $rows = null, $column = 'name' )
	{
		$rows = is_array( $rows ) ? $rows : array();
		return wp_list_pluck( $rows, $column );
	}

	public function filter_collection_rows( array $args, $rows = null, $operator = 'AND' )
	{
		$rows = is_array( $rows ) ? $rows : array();
		return array_values( wp_list_filter( $rows, $args, $operator ) );
	}

	public function find_collection_row( $value, $rows = null, $key = 'name' )
	{
		$rows = is_array( $rows ) ? $rows : array();
		$matches = wp_list_filter( $rows, [ $key => $value ] );
		return ! empty( $matches ) ? reset( $matches ) : null;
	}

	public function find_collection_value( $value, $property, $rows = null, $key = 'name' )
	{
		$row = $this->find_collection_row( $value, $rows, $key );
		return is_array( $row ) && array_key_exists( $property, $row ) ? $row[$property] : null;
	}

	public function find_metal_value( $metal, $property )
	{
		return $this->find_collection_value( $metal, $property, $this->get_raw_metals() );
	}

	public function find_plating_metal( $metal )
	{
		return $this->find_collection_row( $metal, $this->get_raw_plating_metals() );
	}

	public function find_plating_metal_value( $metal, $property )
	{
		return $this->find_collection_value( $metal, $property, $this->get_raw_plating_metals() );
	}

	public function find_line( $line )
	{
		return $this->find_collection_row( $line, $this->get_raw_lines() );
	}

	public function find_line_value( $line, $property )
	{
		return $this->find_collection_value( $line, $property, $this->get_raw_lines() );
	}

	public function find_barrel( $barrel )
	{
		return $this->find_collection_row( $barrel, $this->get_raw_barrels() );
	}

	public function find_rack( $rack )
	{
		return $this->find_collection_row( $rack, $this->get_raw_racks() );
	}

	public function find_operation( $type, $metal, $plating_method = null )
	{
		$matches = $this->find_operations( $type, $metal, $plating_method );
		return ! empty( $matches ) ? array_first( $matches ) : null;
	}

	public function find_operations( $type, $metal, $plating_method = null )
	{
		$matches = [];

		foreach ( $this->get_Operations() as $Operation ) {
			if ( $Operation->get_type() !== $type ) {
				continue;
			}

			$operation_metal = $Operation->get_metal();
			if ( is_array( $operation_metal ) ) {
				if ( in_array( $metal, $operation_metal, true ) ) {
					$matches[] = $Operation;
				}
				continue;
			}

			if ( $operation_metal === $metal ) {
				$matches[] = $Operation;
			}
		}

		if ( 'Plating' === $type && $plating_method ) {
			$method_matches = array_values( array_filter( $matches, function( $Operation ) use ( $plating_method ) {
				return $Operation->get_plating_method() === $plating_method;
			} ) );

			if ( ! empty( $method_matches ) ) {
				return $method_matches;
			}

			// Legacy plating operations without a method remain valid fallbacks.
			$matches = array_values( array_filter( $matches, function( $Operation ) {
				return empty( $Operation->get_plating_method() );
			} ) );
		}

		return array_values( $matches );
	}

	public function get_whitelist_entries( $prop )
	{
		$value = $this->get_string_prop( $prop );
		if ( empty( $value ) || ! is_string( $value ) ) {
			return [];
		}

		$lines = preg_split( '/\R+/', $value );
		$lines = array_map( function ( $line ) {
			return strtolower( trim( $line ) );
		}, $lines );

		return array_values( array_filter( $lines ) );
	}

	public function get_domain_whitelist_entries()
	{
		return $this->get_whitelist_entries( 'domain_whitelist' );
	}

	public function get_email_whitelist_entries()
	{
		return $this->get_whitelist_entries( 'email_whitelist' );
	}

	public function is_onboarding_complete()
	{
		return '' !== $this->get_onboarding_completed_at();
	}

	public function complete_onboarding()
	{
		$value = function_exists( 'current_time' ) ? current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' );
		$this->update_prop( 'onboarding_completed_at', $value );
		return $value;
	}

	public function reopen_onboarding()
	{
		$this->update_prop( 'onboarding_completed_at', '' );
		return '';
	}

	public function get_onboarding_checklist()
	{
		$price_complete = $this->get_hourly_rate() > 0
			&& $this->get_default_margin() > 0
			&& $this->get_default_eff() > 0
			&& $this->get_default_people() > 0
			&& $this->get_default_shift() > 0;

		$quote_complete = '' !== $this->get_starting_quote_number()
			&& $this->get_quote_expires_after() > 0
			&& $this->get_follow_up_after() > 0;

		$plating_complete = ! empty( $this->get_raw_metals() )
			&& ! empty( $this->get_raw_plating_metals() )
			&& ! empty( $this->get_raw_lines() )
			&& ( ! empty( $this->get_raw_barrels() ) || ! empty( $this->get_raw_racks() ) );

		$process_complete = ! empty( $this->get_raw_operations() );

		$template_complete = '' !== $this->get_quote_header()
			&& '' !== $this->get_quote_footer()
			&& '' !== $this->get_quote_terms();

		return array(
			array(
				'slug' => 'price',
				'title' => 'Price Settings',
				'description' => 'Set your default pricing mode and baseline labor, margin, and efficiency inputs.',
				'complete' => $price_complete,
				'url' => PC_CPQ()->Site()->get_settings_page_url( 'price' ),
			),
			array(
				'slug' => 'quotes',
				'title' => 'Quote Settings',
				'description' => 'Set quote numbering, expiration, and follow-up defaults.',
				'complete' => $quote_complete,
				'url' => PC_CPQ()->Site()->get_settings_page_url( 'quotes' ),
			),
			array(
				'slug' => 'plating',
				'title' => 'Plating Settings',
				'description' => 'Load the metals, plating metals, lines, and tooling data needed for quoting.',
				'complete' => $plating_complete,
				'url' => PC_CPQ()->Site()->get_settings_page_url( 'plating' ),
			),
			array(
				'slug' => 'processes',
				'title' => 'Process Settings',
				'description' => 'Configure the routing and operation records used to calculate work content.',
				'complete' => $process_complete,
				'url' => PC_CPQ()->Site()->get_settings_page_url( 'processes' ),
			),
			array(
				'slug' => 'templates',
				'title' => 'Templates',
				'description' => 'Review the quote header, footer, terms, and any email templates you plan to use.',
				'complete' => $template_complete,
				'url' => PC_CPQ()->Site()->get_settings_page_url( 'templates' ),
			),
		);
	}

	public function can_complete_onboarding()
	{
		return true;
	}

	public function is_whitelisted( $email )
	{
		$email = strtolower( trim( (string) $email ) );
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return 'No';
		}

		if ( in_array( $email, $this->get_email_whitelist_entries(), true ) ) {
			return 'Yes';
		}

		$email_parts = explode( '@', $email );
		$domain = array_pop( $email_parts );

		return in_array( $domain, $this->get_domain_whitelist_entries(), true ) ? 'Yes' : 'No';
	}

	protected function save_option_field( $prop, $value )
	{
		if ( function_exists( 'update_field' ) ) {
			return update_field( $prop, $value, 'option' );
		}

		return update_option( 'setting_' . $prop, $value );
	}

	protected function get_collection_meta( $prop )
	{
		$value = $this->get_meta( $prop );
		return is_array( $value ) ? $value : array();
	}

	protected function normalize_snapshot_rows( $rows )
	{
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return array();
		}

		return array_values( array_map( function ( $row ) {
			return is_array( $row ) ? $row : (array) $row;
		}, $rows ) );
	}

	protected function get_string_prop( $prop, $default = '' )
	{
		$value = $this->get_prop( $prop );
		if ( null === $value || false === $value ) {
			return $default;
		}

		return is_string( $value ) ? trim( $value ) : (string) $value;
	}

	protected function get_float_prop( $prop, $default = 0.0 )
	{
		$value = $this->get_prop( $prop );
		if ( '' === $value || null === $value || false === $value ) {
			return $default;
		}

		return floatval( $value );
	}

	protected function get_int_prop( $prop, $default = 0 )
	{
		$value = $this->get_prop( $prop );
		if ( '' === $value || null === $value || false === $value ) {
			return $default;
		}

		return absint( $value );
	}

	protected function get_digit_string_prop( $prop, $default = '' )
	{
		$value = $this->get_string_prop( $prop, $default );
		if ( '' === $value ) {
			return $default;
		}

		return preg_replace( '/\D+/', '', $value );
	}

	protected function save_raw_collection_meta( $prop, $value, $refresh_method )
	{
		$result = $this->save_option_field( $prop, $value );

		if ( is_callable( array( $this, $refresh_method ) ) ) {
			$this->{$refresh_method}();
		}

		return $result;
	}

	public function get_hidden()
	{
		return array(
		);
	}

	protected function get_meta( $prop )
	{
		if ( function_exists( 'get_field' ) ) {
			return get_field( $prop, 'option' );
		}

		return get_option( 'setting_' . $prop );
	}

	protected function set_prop( $prop, $value )
	{
		if ( $this->has_prop( $prop ) ) {
			$this->{$prop} = $value;
			return $this->{$prop};
		}
		return false;
	}

	protected function can_save_meta( $prop, $value )
	{
		$setter = $this->get_setter( $prop );
		return null !== $value && is_callable( array( $this, $setter ) );
	}

	public function save_meta( $prop, $value )
	{
		// ensures only allowable props are saved
		if ( $this->can_save_meta( $prop, $value ) ) {
			// allow extending classes to hijack per property
			$saver = "save_{$prop}_meta";
			if ( is_callable( array( $this, $saver ) ) ) {
				return $this->{$saver}( $value );
			}

			// optional ACF support
			if ( function_exists( 'update_field' ) ) {
				update_field( $prop, $value, 'option' );
			} else {
				update_option( 'setting_' . $prop, $value );
			}
		}
	}

	public function update_prop( $prop, $value )
	{
		$this->set_prop( $prop, $value );
		$this->save_meta( $prop, $value );
	}

}
