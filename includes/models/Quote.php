<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

use PC_CPQ\Core\Pricing\Pricing_Validator;
use PC_CPQ\Core\Approval\Override_Gate;
use \GFAPI;

class Quote
{
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
		$form = GFAPI::get_form( PC_CPQ()->Pdf_Config()->get_quote_form_id() );
		$entry = GFAPI::get_entry( $this->Lead->get_form_entry_id() );
		GFAPI::update_entry_field( $this->Lead->get_form_entry_id(), 3, $recipients );
		GFAPI::send_notifications( $form, $entry, 'quote_created' );

		// update the lead
		$this->update_lead();
	}

	public function assert_can_send(): void
	{
		$validator = new Pricing_Validator();
		$gate = new Override_Gate();

		$all_issues = [];

		foreach ( $this->get_parts() as $Part ) {

			$breaks = $Part->get_price_breaks();

			if ( empty( $breaks ) ) {
				continue;
			}

			$part_issues = $validator->validate_breaks( $breaks );

			if ( ! empty( $part_issues ) ) {
				$all_issues[$Part->get_id()] = $part_issues;
			}
		}

		$flat = [];

		foreach ( $all_issues as $issues ) {
			foreach ( $issues as $issue ) {
				$flat[] = $issue;
			}
		}

		if ( empty( $flat ) ) {
			return; // no issues — safe
		}

		if ( $gate->requires_override( $flat ) ) {

			$this->Lead->set_override_required( $all_issues );

			if ( $this->Lead->get_override_status() !== 'approved' ) {
				throw new \Exception( 'Manager override required before sending this quote.' );
			}
		}
	}

	public function get_preview_quote_url()
	{
		return $this->Lead->get_quote_pdf();
	}

	public function send_message( $recipients, $message )
	{
		// trigger the notification
		$form = GFAPI::get_form( PC_CPQ()->Pdf_Config()->get_quote_form_id() );
		$entry = GFAPI::get_entry( $this->Lead->get_form_entry_id() );
		GFAPI::update_entry_field( $this->Lead->get_form_entry_id(), 3, $recipients );
		GFAPI::update_entry_field( $this->Lead->get_form_entry_id(), 34, $message );
		GFAPI::send_notifications( $form, $entry, 'message' );
	}

	public function update_lead()
	{
		$settings_snapshot = $this->build_quote_settings_snapshot();
		$pricing_snapshot = $this->build_quote_pricing_snapshot();

		$this->Lead->update_prop( 'sent', 1 );
		$this->Lead->update_prop( 'status', 'Quoted' );
		$this->Lead->update_prop( 'quote_date', strtotime( 'now' ) );
		$this->Lead->update_prop( 'follow_up_date', strtotime( '+ ' . $settings_snapshot['quote_defaults']['follow_up_after'] . ' days' ) );
		$this->Lead->update_prop( 'expiration_date', strtotime( '+ ' . $settings_snapshot['quote_defaults']['quote_expires_after'] . ' days' ) );
		$this->Lead->persist_quote_snapshots( $settings_snapshot, $pricing_snapshot );
	}

	public function requote()
	{
		$this->update_lead();
		return $this->Lead;
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

	private function get_parts()
	{
		return $this->Lead->get_Parts();
	}

	private function build_quote_settings_snapshot()
	{
		$snapshot = PC_CPQ()->Settings()->get_quote_snapshot_export();
		$snapshot['quote_pricing_type'] = $this->Lead->get_quote_pricing_type();
		$snapshot['pricing_mode'] = $this->Lead->get_pricing_mode();
		$snapshot['selected_fees'] = $this->build_selected_fees_snapshot( $snapshot );
		return $snapshot;
	}

	private function build_selected_fees_snapshot( array $settings_snapshot )
	{
		$selected_fee_names = wp_list_pluck( (array) $this->Lead->get_fees(), 'fee' );
		$reference_fees = $settings_snapshot['pricing_reference']['fees'] ?? array();
		$selected_fees = array_filter( $reference_fees, function ( $fee ) use ( $selected_fee_names ) {
			return in_array( $fee['name'] ?? '', $selected_fee_names, true );
		} );

		return array_values( array_map( function ( $fee ) {
			$amount = isset( $fee['amount'] ) ? floatval( $fee['amount'] ) : 0;
			$unit = $fee['unit'] ?? 'dollars';

			return array(
				'name' => $fee['name'] ?? '',
				'unit' => $unit,
				'amount' => $amount,
				'formatted_amount' => 'percent' === $unit ? $amount . '%' : to_currency( $amount ),
			);
		}, $selected_fees ) );
	}

	private function build_quote_pricing_snapshot()
	{
		$pricing_type = $this->Lead->get_quote_pricing_type();

		return array(
			'pricing_type' => $pricing_type,
			'pricing_mode' => $this->Lead->get_pricing_mode(),
			'post_operations' => $this->build_post_operations_snapshot(),
			'parts' => array_values( array_map( function ( $Part ) use ( $pricing_type ) {
				return $this->build_part_pricing_snapshot( $Part, $pricing_type );
			}, $this->get_parts() ) ),
		);
	}

	private function build_part_pricing_snapshot( $Part, $pricing_type )
	{
		$special_rows = array();
		$commodity_rows = array();

		foreach ( $Part->get_Pricing_Model() as $Pricing ) {
			$special_rows[] = $this->build_pricing_row_snapshot( $Pricing, '' );
			$commodity_rows[] = $this->build_pricing_row_snapshot( $Pricing, 'base_' );
		}

		$metal_factors = array();
		foreach ( $Part->get_metal_factors() as $metal => $factor ) {
			$metal_factors[] = array(
				'metal' => $metal,
				'factor' => round( floatval( $factor ), 4 ),
			);
		}

		return array(
			'file_name' => $Part->get_file_name(),
			'part_number' => $Part->get_part_number(),
			'drawing_number' => $Part->get_drawing_number(),
			'revision_number' => $Part->get_revision_number(),
			'base_metal' => $Part->get_base_metal(),
			'plating_line' => $Part->get_plating_line(),
			'thruput_capacity_view' => $Part->get_thruput_capacity( 'view' ),
			'plating_method' => $Part->get_plating_method(),
			'tool' => $Part->get_tool(),
			'pieces_per_load_view' => $Part->get_pieces_per_load( 'view' ),
			'area_view' => $Part->get_area( 'view' ),
			'total_area_view' => $Part->get_total_area( 'view' ),
			'volume_view' => $Part->get_volume( 'view' ),
			'weight_view' => $Part->get_weight( 'view' ),
			'load_weight_view' => $Part->get_load_weight( 'view' ),
			'metal_consumption_view' => $Part->get_metal_consumption( 'view' ),
			'process_time_view' => $Part->get_process_time( 'view' ),
			'material_cost_formatted' => $Part->get_material_cost( 'view' ),
			'min_lot_charge' => $Part->get_min_lot_charge(),
			'min_lot_charge_formatted' => to_currency( $Part->get_min_lot_charge() ),
			'processes_view' => $Part->get_Processes( 'view' ),
			'operations' => $this->build_routing_operations_snapshot( $Part ),
			'processes' => $this->build_routing_processes_snapshot( $Part ),
			'show_metal_factor' => $this->Lead->include_metal_factor(),
			'metal_factors' => $metal_factors,
			'quantity_unit_label' => $Part->get_Pricing()->get_quantity_unit_label(),
			'price_unit_label' => $Part->get_Pricing()->get_price_unit_label(),
			'special_rows' => $special_rows,
			'commodity_rows' => $commodity_rows,
			'active_rows' => 'commodity' === $pricing_type ? $commodity_rows : $special_rows,
		);
	}

	private function build_pricing_row_snapshot( $Pricing, $prefix )
	{
		$cost_getter = "get_{$prefix}cost_per_unit";
		$price_getter = "get_{$prefix}price_per_unit";
		$final_price_getter = "get_{$prefix}final_price_per_unit";

		return array(
			'quantity_range' => $Pricing->get_quantity_range(),
			'cost_per_unit' => $Pricing->{$cost_getter}( 'view' ),
			'price_per_unit' => $Pricing->{$price_getter}( 'view' ),
			'final_price_per_unit' => $Pricing->{$final_price_getter}( 'view' ),
			'total_time' => $Pricing->get_total_time( 'view' ),
		);
	}

	private function build_routing_operations_snapshot( $Part )
	{
		return array_values( array_map( function ( $Operation ) {
			return array(
				'metal' => $Operation->get_metal(),
				'operation' => $Operation->get_operation(),
				'description' => $Operation->get_description(),
				'time' => $Operation->get_time(),
			);
		}, $Part->get_Operations() ) );
	}

	private function build_routing_processes_snapshot( $Part )
	{
		return array_values( array_map( function ( $Process ) {
			return array(
				'metal' => $Process->get_metal(),
				'description' => $Process->get_description(),
				'time' => $Process->get_time(),
			);
		}, $Part->get_Processes() ) );
	}

	private function build_post_operations_snapshot()
	{
		return array_values( array_map( function ( $Post_Operation ) {
			return array(
				'operation' => $Post_Operation->get_operation(),
				'description' => $Post_Operation->get_description(),
				'cycle_time' => $Post_Operation->get_cycle_time(),
			);
		}, PC_CPQ()->Settings()->get_Post_Operations() ) );
	}

	static public function create_entry( $Lead )
	{
		$date = date( 'Y-m-d H:i:s' );
		$entry_data = array(
			'form_id' => PC_CPQ()->Pdf_Config()->get_quote_form_id(),
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
