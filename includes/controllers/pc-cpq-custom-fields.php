<?php

namespace PC_CPQ\Controllers;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Custom_Fields extends MVC_Controller_Registry
{
	private $loaded_field = array(
		'field_62d6dfa8a2273' => false,
		'field_6192c98d293ba' => false,
	);

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_filter( 'acf/prepare_field/name=service', array( $this, 'allow_optgroup' ), 10, 1 );
		add_filter( 'acf/prepare_field/key=field_605dd6ab92865', array( $this, 'set_readonly_fields' ), 10, 1 );  // remove?
		add_filter( 'acf/prepare_field/key=field_6076586cfa0e5', array( $this, 'set_readonly_fields' ), 10, 1 );  // remove?

		add_filter( 'acf/prepare_field/key=field_6192c4078782c', array( $this, 'set_readonly_fields' ), 10, 1 ); // raw_specs
		add_filter( 'acf/prepare_field/key=field_6192c9b3293bb', array( $this, 'set_readonly_fields' ), 10, 1 ); // part_data['file_name']
		add_filter( 'acf/prepare_field/key=field_6192cf73293ce', array( $this, 'set_readonly_fields' ), 10, 1 ); // part_data['processes']['time']
		add_filter( 'acf/prepare_field/key=field_610ab99f02fe6', array( $this, 'set_readonly_fields' ), 10, 1 ); // quote_date
		add_filter( 'acf/prepare_field/key=field_610ab9b002fe7', array( $this, 'set_readonly_fields' ), 10, 1 ); // follow_up_date
		add_filter( 'acf/prepare_field/key=field_6238c41b168b9', array( $this, 'set_readonly_fields' ), 10, 1 ); // quote_number
		add_filter( 'acf/prepare_field/key=field_6238c64170fcd', array( $this, 'set_readonly_fields' ), 10, 1 ); // starting_quote_number
		add_filter( 'acf/prepare_field/key=field_681cf2f000002', array( $this, 'set_readonly_fields' ), 10, 1 ); // quote_form_id
		add_filter( 'acf/prepare_field/key=field_681cf2f000003', array( $this, 'set_readonly_fields' ), 10, 1 ); // quote_pdf_id
		add_filter( 'acf/prepare_field/key=field_681cf2f000004', array( $this, 'set_readonly_fields' ), 10, 1 ); // routing_pdf_id
		add_filter( 'acf/prepare_field/key=field_6192c9c5293bc', array( $this, 'set_readonly_fields' ), 10, 1 ); // area_computed
		add_filter( 'acf/prepare_field/key=field_6192c9e4293bd', array( $this, 'set_readonly_fields' ), 10, 1 ); // volume_computed
		add_filter( 'acf/prepare_field/key=field_6193eada83418', array( $this, 'set_readonly_fields' ), 10, 1 ); // d_x_computed
		add_filter( 'acf/prepare_field/key=field_6193eaf483419', array( $this, 'set_readonly_fields' ), 10, 1 ); // d_y_computed
		add_filter( 'acf/prepare_field/key=field_6193eafd8341a', array( $this, 'set_readonly_fields' ), 10, 1 ); // d_z_computed
		add_filter( 'acf/prepare_field/key=field_6660908d75ef7', array( $this, 'set_readonly_fields' ), 10, 1 ); // post_ops_order

		add_filter( 'acf/prepare_field/name=base_metal', array( $this, 'load_metal_choices' ), 10, 1 );
		add_filter( 'acf/prepare_field/key=field_641079dc61f6a', array( $this, 'load_metal_choices' ), 10, 1 );
		add_filter( 'acf/prepare_field/key=field_6192cf5d293cc', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/prepare_field/key=field_63e25e6fe9fbe', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=plating_line', array( $this, 'load_line_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=plating_tool_barrel', array( $this, 'load_barrel_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=plating_tool_rack', array( $this, 'load_rack_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=plating_tool', array( $this, 'load_tool_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=process_1', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=process_2', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=process_3', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=process_4', array( $this, 'load_plating_metal_choices' ), 10, 1 );
		add_filter( 'acf/load_field/key=field_610aba9238ae5', array( $this, 'load_operation_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=load_template', array( $this, 'load_email_template_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=add_recipient', array( $this, 'load_add_recipient_choices' ), 10, 1 );
		add_filter( 'acf/load_value/name=recipient', array( $this, 'load_recipient' ), 10, 3 );
		add_filter( 'acf/prepare_field/key=field_quote_fees_fee', array( $this, 'load_fee_choices' ), 10, 3 );

		add_filter( 'acf/load_value/key=field_6192ceff293c8', array( $this, 'set_part_plating_data' ), 10, 3 ); // part_data['plating_line']
		add_filter( 'acf/load_value/key=field_6192cf0d293c9', array( $this, 'set_part_plating_data' ), 10, 3 ); // part_data['plating_method']
		add_filter( 'acf/load_value/key=field_6192cf16293ca', array( $this, 'set_part_plating_data' ), 10, 3 ); // part_data['plating_tool_rack']
		add_filter( 'acf/load_value/key=field_6192d2e1b5cbe', array( $this, 'set_part_plating_data' ), 10, 3 ); // part_data['plating_tool_barrel']
		add_filter( 'acf/format_value/key=field_6040e9f2f48c0', array( $this, 'uc_names' ), 10, 3 );
		add_filter( 'acf/format_value/key=field_6040e9faf48c1', array( $this, 'uc_names' ), 10, 3 );
		add_filter( 'acf/format_value/key=field_610ab269c1806', array( $this, 'uc_names' ), 10, 3 );

		add_action( 'acf/save_post', array( $this, 'update_quote_number' ), 10, 1 );

		add_filter( 'posts_where', [ $this, 'posts_where_contacts_email' ], 10, 1 );
	}

	public function lookup_parts( $value, $post_id, $field )
	{
		if ( $this->loaded_field[$field['key']] ) {
			return $value;
		}
		$this->loaded_field[$field['key']] = true;

		if ( isset( $_GET['debug_parts'] ) ) {
			$part_data = get_field( 'part_data', $post_id );
			if ( ! empty( $part_data ) ) {
				foreach ( $part_data as $part ) {
					$args = array(
						$part['drawing_number'],
						$part['revision_number'],
						$part['part_number'],
					);
					$hash = md5( implode( '', $args ) );
					$results = PC_CPQ()->part_lookup->search_parts( $hash );
				}
			}
		}

		return $value;
	}

	public function allow_optgroup( $field )
	{
		// Abort if it's native option
		if ( $field['ID'] === 0 ) {
			return $field;
		}

		$raw_choices = $field['choices'];
		$choices = array();
		$current_group = '';

		foreach ( $raw_choices as $value => $label ) {
			// if first letter is hashtag, turn it into group label
			if ( preg_match( '/^#(.+)/', $label, $matches ) ) {
				$current_group = str_replace( '#', '', $label );
				$choices[$current_group] = array();
			}
			// If group label already defined before this line
			elseif ( ! empty( $current_group ) ) {
				$choices[$current_group][$value] = $label;
			} else {
				$choices[$value] = $label;
			}
		}

		$field['choices'] = $choices;

		return $field;
	}

	public function set_readonly_fields( $field )
	{
		switch ( $field['_name'] ) {
			case 'raw_specs':
			case 'quote_date':
			case 'follow_up_date':
			case 'quote_number':
			case 'area_computed':
			case 'volume_computed':
			case 'd_x_computed':
			case 'd_y_computed':
			case 'd_z_computed':
			case 'post_ops_order':
				$field['readonly'] = true;
				break;
			case 'starting_quote_number':
				if ( '' !== PC_CPQ()->Settings()->get_starting_quote_number() ) {
					$field['readonly'] = true;
				}
				break;
			case 'quote_form_id':
				if ( 0 < PC_CPQ()->Settings()->get_quote_form_id() ) {
					$field['readonly'] = true;
				}
				break;
			case 'quote_pdf_id':
				if ( '' !== PC_CPQ()->Settings()->get_quote_pdf_id() ) {
					$field['readonly'] = true;
				}
				break;
			case 'routing_pdf_id':
				if ( '' !== PC_CPQ()->Settings()->get_routing_pdf_id() ) {
					$field['readonly'] = true;
				}
				break;
			default:
				if ( $field['value'] != '' && $field['value'] != 0 ) {
					$field['readonly'] = true;
				}
		}

		return $field;
	}

	public function load_metal_choices( $field )
	{
		$choices = array();
		$metals = PC_CPQ()->Settings()->get_Metals();
		if ( ! empty( $metals ) ) {
			$choices = array_map( function ( $Metal ) {
				return $Metal->get_name();
			}, $metals );
		}

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_plating_metal_choices( $field )
	{
		$default_choices = array( 'None' );
		$plating_metals = is_admin() ? PC_CPQ()->Settings()->get_Plating_Metals() : PC_CPQ()->Settings()->get_Available_Plating_Metals();
		if ( $plating_metals ) {
			$choices = array_map( function ( $Plating_Metal ) {
				return $Plating_Metal->get_name();
			}, $plating_metals );
		}
		$all_choices = array_merge( $default_choices, $choices, );

		$field['choices'] = array_combine( $all_choices, $all_choices );

		return $field;
	}

	public function load_line_choices( $field )
	{
		$choices = PC_CPQ()->Settings()->get_line_names();

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_barrel_choices( $field )
	{
		$choices = PC_CPQ()->Settings()->get_barrel_names();

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_rack_choices( $field )
	{
		$choices = PC_CPQ()->Settings()->get_rack_names();

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_tool_choices( $field )
	{
		$choices = array();
		$barrel_choices = PC_CPQ()->Settings()->get_barrel_names();
		$rack_choices = PC_CPQ()->Settings()->get_rack_names();
		$choices = array_merge( $barrel_choices, $rack_choices );

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_operation_choices( $field )
	{
		$choices = PC_CPQ()->Settings()->get_operation_names();

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_email_template_choices( $field )
	{
		$choices = array();
		$email_templates = PC_CPQ()->Settings()->get_email_template_names();
		if ( $email_templates ) {
			$default_choices = array( 'Select a template' );
			$choices = array_merge( $default_choices, $email_templates );
		}

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_fee_choices( $field )
	{
		$choices = array();
		$fees = PC_CPQ()->Settings()->get_Fees();
		if ( ! empty( $fees ) ) {
			$default_choices = [ 'Select a fee' ];
			$raw_choices = array_map( function ( $Fee ) {
				return $Fee->get_name();
			}, $fees );
			$choices = array_merge( $default_choices, $raw_choices );
		}

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_add_recipient_choices( $field )
	{
		if ( ! isset( $_GET['post'] ) )
			return $field;

		$choices = array();
		$Lead = PC_CPQ()->lead( $_GET['post'] );
		if ( $Lead->get_customer() && $Lead->get_customer() instanceof \WP_Post ) {
			$default_choices = array( 'Select a recipient' );
			$contacts = get_field( 'contacts', $Lead->get_customer()->ID );
			if ( ! empty( $contacts ) ) {
				$emails = wp_list_pluck( $contacts, 'email' );
				$choices = array_merge( $default_choices, $emails );
			}
		}

		$field['choices'] = array_combine( $choices, $choices );

		return $field;
	}

	public function load_recipient( $value, $post_id, $field )
	{
		if ( $value == '' || $value == null ) {
			$value = get_field( 'email', $post_id );
		}

		return $value;
	}

	public function uc_names( $value, $post_id, $field )
	{
		return ucwords( $value );
	}

	public function set_part_plating_data( $value, $post_id, $field )
	{
		$field_key = $field['key'];
		remove_filter( 'acf/load_value/key=' . $field_key, array( $this, 'set_part_plating_data' ) );
		if ( ! $value || $value == '' ) {
			$value = $this->get_part_plating_data_value( $value, $post_id, $field );
		}
		add_filter( 'acf/load_value/key=' . $field_key, array( $this, 'set_part_plating_data' ), 10, 3 );

		return $value;
	}

	private function get_part_plating_data_value( $value, $post_id, $field )
	{
		$value = '';
		$matching_key = false;
		$field_key = $field['key'];
		$field_name = $field['_name'];
		$part_data = get_field( 'part_data', $post_id );
		$key = str_replace( array( 'part_data_', '_' . $field_name ), '', $field['name'] );
		$part = $part_data[$key];

		if ( ! empty( $part['processes'] ) ) {
			$recipes = PC_CPQ()->Settings()->get_recipes_by_base_metal( $part['base_metal'] );
			$metals = wp_list_pluck( $part['processes'], 'metal' );

			if ( ! empty( $recipes ) ) {
				if ( ! empty( $recipes ) ) {
					foreach ( $recipes as $key => $recipe ) {
						$arr = array_values( array_filter( $recipe, function ( $v, $k ) {
								return strpos( $k, 'process_' ) === 0 && $v != '';
							}, ARRAY_FILTER_USE_BOTH ) );

						if ( $arr === $metals ) {
							$matching_key = $key;
							break;
						}
					}

					if ( $matching_key !== false ) {
						if ( $field_key == 'field_6192cf16293ca' || $field_key == 'field_6192d2e1b5cbe' ) {
							$field_name = 'plating_tool';
						}
						$value = $recipes[$matching_key][$field_name];
					}
				}
			}
		}

		return $value;
	}

	public function update_quote_number( $post_id )
	{
		$Lead = PC_CPQ()->lead( $post_id );
		if ( ! $Lead->get_quote_number() ) {
			// next quote number will be generated
			$Lead->update_prop( 'quote_number', false );
		}
	}

	static public function parts_config()
	{
		$config = array(
			'dimensions' => null,
			'metals' => null,
			'operations' => null,
			'pricing_units' => null,
		);

		$config['metals'] = PC_CPQ()->Settings()->get_metal_names();

		$plating_metals = PC_CPQ()->Settings()->get_plating_metal_names( false );
		if ( $plating_metals ) {
			$config['plating_metals'] = $plating_metals;
		}

		$config['pricing_units'] = \PC_CPQ\Models\Part_Pricing_Inputs::get_price_units();

		return $config;
	}

	public function posts_where_contacts_email( $where )
	{
		$where = str_replace( "meta_key = 'contacts_$", "meta_key LIKE 'contacts_%", $where );
		return $where;
	}
}

PC_CPQ_Custom_Fields::instance();
