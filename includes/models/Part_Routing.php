<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Routing extends Repeater_Model
{
	protected $type;
	protected $metal;
	protected $operation;
	protected $description;
	protected $time;
	protected $matched_operations;
	protected $matched_operation_id;

	/*
	 * Getters
	 */
	
	public function get_type()
	{
		return $this->get_prop( 'type' );
	}
	
	public function get_metal()
	{
		return $this->get_prop( 'metal' );
	}

	public function get_operation()
	{
		if ( null === $this->operation ) {
			$this->operation = $this->get_details( 'operation' );
			if ( false === $this->operation && $this->is_prep_operation() ) {
				$this->operation = $this->get_prep_fallback_label();
			}
		}
		return $this->operation;
	}

	public function get_description()
	{
		if ( null === $this->description ) {
			$this->description = $this->get_details( 'description' );
			if ( false === $this->description && $this->is_prep_operation() ) {
				$this->description = $this->get_prep_fallback_description();
			}
		}
		return $this->description;
	}

	public function get_time()
	{
		if ( null === $this->time ) {
			$time = $this->get_prop( 'time' );
			if ( false !== $time && '' !== $time ) {
				$this->time = $time;
			} else {
				$details_time = $this->get_details( 'cycle_time' );
				$this->time = false !== $details_time ? $details_time : 0;
			}
		}
		return $this->time;
	}
	
	public function get_time_unit()
	{
		if ( null === $this->time_unit ) {
			$this->time_unit =  $this->get_details( 'cycle_unit' );
		}
		return $this->time_unit;
	}
	
	private function get_details( $property )
	{
		$getter = "get_{$property}";
		return $this->get_site_Operation() ? $this->get_site_Operation()->{$getter}() : false;
	}

	private function is_prep_operation()
	{
		return 'Prep' === $this->get_type();
	}

	private function is_base_metal_operation()
	{
		return in_array( $this->get_type(), [ 'Pre', 'Prep' ], true );
	}

	private function get_matching_operations()
	{
		if ( null === $this->matched_operations ) {
			$this->matched_operations = PC_CPQ()->Settings()->find_operations(
				$this->get_type(),
				$this->get_metal(),
				'Plating' === $this->get_type() ? $this->get_part_plating_method() : null
			);
		}
		return $this->matched_operations;
	}

	private function get_matching_operation_count()
	{
		return count( $this->get_matching_operations() );
	}

	private function get_prep_fallback_label()
	{
		$metal = $this->get_prep_match_label();
		$matches = $this->get_matching_operation_count();

		if ( 0 === $matches ) {
			return sprintf( 'NO PREP STEP FOUND FOR %s', strtoupper( $metal ) );
		}

		return sprintf( 'PREP STEP FOUND FOR %s', strtoupper( $metal ) );
	}

	private function get_prep_fallback_description()
	{
		$metal = $this->get_prep_match_label();
		$matches = $this->get_matching_operation_count();

		if ( 0 === $matches ) {
			return sprintf( 'No Prep operation is configured for base metal "%s".', $metal );
		}

		return sprintf( 'Prep operations are configured for base metal "%s".', $metal );
	}

	private function get_metal_label()
	{
		$metal = $this->get_metal();
		return is_array( $metal ) ? implode( ', ', $metal ) : (string) $metal;
	}

	private function get_part_plating_method()
	{
		$Part = $this->get_post_model();
		return $Part && is_callable( [ $Part, 'get_plating_method' ] ) ? $Part->get_plating_method() : null;
	}

	private function get_prep_match_label()
	{
		$label = $this->get_metal_label();
		return $label;
	}

	private function get_site_Operation()
	{
		if ( $this->is_base_metal_operation() ) {
			$matched_operation_id = $this->get_prop( 'matched_operation_id' );
			if ( false !== $matched_operation_id && '' !== $matched_operation_id ) {
				$matched_operations = array_filter( $this->get_matching_operations(), function( $Operation ) use ( $matched_operation_id ) {
					return intval( $Operation->get_id() ) === intval( $matched_operation_id );
				} );

				if ( ! empty( $matched_operations ) ) {
					return array_first( $matched_operations );
				}
			}

			return 1 === $this->get_matching_operation_count() ? array_first( $this->get_matching_operations() ) : null;
		}

		return PC_CPQ()->Settings()->find_operation( $this->get_type(), $this->get_metal() );
	}
}
