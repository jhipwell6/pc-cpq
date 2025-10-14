<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Input
{
	protected $acf_field;
	protected $Model;
	protected $index;
	protected $id;
	protected $name;
	protected $label;
	protected $value;
	protected $type;

	public function __construct( $field_name, $Model, $index = null, $alias = null )
	{
		$this->set_acf_field( $field_name, $alias );
		$this->set_Model( $Model );
		$this->set_index( $index );

		return $this;
	}

	public function get_acf_field( $prop = null )
	{
		if ( $prop ) {
			return isset( $this->acf_field[$prop] ) ? $this->acf_field[$prop] : false;
		} else {
			return $this->acf_field;
		}
	}

	public function get_Model()
	{
		return $this->Model;
	}

	private function get_index()
	{
		return null !== $this->index ? (array) $this->index : null;
	}
	
	private function get_index_as_string()
	{
		return $this->has_index() ? implode( '_', $this->get_index() ) : '';
	}

	public function has_index()
	{
		return null !== $this->get_index();
	}

	public function get_id()
	{
		if ( null === $this->id ) {
			$this->id = str_replace( '/', '_', $this->get_name() );
		}
		return $this->id;
	}

	public function get_name()
	{
		if ( null === $this->name ) {
			$prefix = $this->get_name_prefix();
			$this->name = $prefix . $this->get_acf_field( 'name' );
		}
		return $this->name;
	}
	
	public function get_label()
	{
		if ( null === $this->label ) {
			$this->label = $this->get_acf_field( 'label' );
		}
		return $this->label;
	}

	public function get_value()
	{
		if ( null === $this->value ) {
			$prop = $this->get_acf_field( 'name' );
			$Model = $this->get_Model();
			$getter = "get_{$prop}";
			$this->value = $Model->has_prop( $prop ) || is_callable( array( $Model, $getter ) ) ? $Model->{$getter}() : '';
		}
		return $this->value;
	}

	public function get_type()
	{
		if ( null === $this->type ) {
			$this->type = $this->get_acf_field( 'type' );
		}
		return $this->type;
	}

	public function is_readonly()
	{
		$readonly = $this->get_acf_field( 'readonly' );
		return $readonly && ! is_array( $readonly );
	}
	
	public function is_required()
	{
		$required = $this->get_acf_field( 'required' );
		return $required && ! is_array( $required );
	}
	
	public function should_show_label()
	{
		$wrapper = $this->get_acf_field( 'wrapper' );
		return isset( $wrapper['class'] ) && strpos( $wrapper['class'], 'no-label' ) !== false ? false : true;
	}
	
	public function is_override()
	{
		return strpos( $this->get_name(), '_override' ) !== false;
	}

	private function get_name_prefix()
	{
		$prefix_map = [
			'\PC_CPQ\Models\Lead_Quantity' => 'raw_quantities-',
			'\PC_CPQ\Models\Part' => 'raw_parts-',
			'\PC_CPQ\Models\Part_Process' => 'raw_parts-processes-',
			'\PC_CPQ\Models\Part_Quantity' => 'raw_parts-quantities-',
			'\PC_CPQ\Models\Part_Routing' => 'raw_parts-routing-',
			'\PC_CPQ\Models\Part_Pricing_Inputs' => 'raw_parts-pricing-',
			'\PC_CPQ\Models\Customer_Contact' => 'raw_contacts-',
			'\PC_CPQ\Models\Customer_Shipping' => 'raw_shipping-',
			'\PC_CPQ\Models\Settings\Email_Template' => 'raw_email_templates-',
			'\PC_CPQ\Models\Settings\Metal' => 'raw_metals-',
			'\PC_CPQ\Models\Settings\Plating_Metal' => 'raw_plating_metals-',
			'\PC_CPQ\Models\Settings\Line' => 'raw_lines-',
			'\PC_CPQ\Models\Settings\Barrel' => 'raw_barrels-',
			'\PC_CPQ\Models\Settings\Rack' => 'raw_racks-',
			'\PC_CPQ\Models\Settings\Operation' => 'raw_operations-',
		];
		
		$prefix = '';
		foreach ( $prefix_map as $class => $pre ) {
			if ( is_a( $this->get_Model(), $class ) ) {
				$prefix = $this->apply_index_to_prefix( $pre );
				break;
			}
		}
		return $prefix;
	}

	public function set_acf_field( $field_name, $alias )
	{
		$acf_prop = $alias ? $alias : $field_name;
		$acf_prop = strtolower( $acf_prop );
		$field = acf_get_field( $acf_prop );
		$this->acf_field = apply_filters( 'acf/prepare_field', $field );
		$this->acf_field = apply_filters( 'acf/load_field', $this->acf_field );
		return $this->acf_field;
	}

	private function set_Model( $Model )
	{
		$this->Model = $Model;
		return $this->Model;
	}

	private function set_index( $index )
	{
		$this->index = $index;
		return $this->index;
	}

	private function apply_index_to_prefix( $prefix )
	{
		if ( $this->has_index() ) {
			$i = 1;
			foreach ( $this->get_index() as $index ) {
				$prefix = $this->str_replace_n( '-', '/' . $index . '/', $prefix, 1 );
				$i++;
			}
		}
		return $prefix;
	}

	private function str_replace_n( $search, $replace, $subject, $occurrence )
	{
		$search = preg_quote( $search );
		return preg_replace( "/^((?:(?:.*?$search){" . -- $occurrence . "}.*?))$search/", "$1$replace", $subject );
	}

}
