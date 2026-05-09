<?php

namespace PC_CPQ\Models;

use GFAPI;
use GPDFAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pdf_Config
{
	const TYPE_QUOTE = 'quote';
	const TYPE_ROUTING = 'routing';

	const LAYOUT_STANDARD = 'standard';
	const LAYOUT_COMPACT = 'compact';

	protected $form_id;
	protected $pdf_ids = array();

	public function get_quote_form_id()
	{
		if ( null === $this->form_id ) {
			$configured = PC_CPQ()->Settings()->get_quote_form_id();
			if ( $configured > 0 ) {
				$this->form_id = $configured;
			} else {
				$detected = $this->detect_quote_form_id();
				$this->form_id = $detected > 0 ? $detected : 1;
				$this->maybe_persist_setting( 'quote_form_id', $detected );
			}
		}

		return absint( $this->form_id );
	}

	public function get_quote_pdf_id()
	{
		return $this->get_pdf_id( self::TYPE_QUOTE );
	}

	public function get_routing_pdf_id()
	{
		return $this->get_pdf_id( self::TYPE_ROUTING );
	}

	public function get_quote_pdf_layout()
	{
		return $this->sanitize_layout( PC_CPQ()->Settings()->get_quote_pdf_layout() );
	}

	public function get_routing_pdf_layout()
	{
		return $this->sanitize_layout( PC_CPQ()->Settings()->get_routing_pdf_layout() );
	}

	public function get_layout( $type, $snapshot = array() )
	{
		$key = $this->get_layout_setting_key( $type );
		$value = '';

		if ( is_array( $snapshot ) && isset( $snapshot[ $key ] ) ) {
			$value = $snapshot[ $key ];
		}

		if ( '' === $value ) {
			$value = self::TYPE_ROUTING === $type ? $this->get_routing_pdf_layout() : $this->get_quote_pdf_layout();
		}

		return $this->sanitize_layout( $value );
	}

	public function get_snapshot()
	{
		return array(
			'quote_form_id' => $this->get_quote_form_id(),
			'quote_pdf_id' => $this->get_quote_pdf_id(),
			'routing_pdf_id' => $this->get_routing_pdf_id(),
			'quote_pdf_layout' => $this->get_quote_pdf_layout(),
			'routing_pdf_layout' => $this->get_routing_pdf_layout(),
		);
	}

	public function matches_pdf_config( $config, $type )
	{
		$pdf_id = (string) ( $config['settings']['id'] ?? '' );
		$pdf_name = (string) ( $config['settings']['name'] ?? '' );

		$expected_id = (string) $this->get_pdf_id( $type );
		if ( '' !== $expected_id && $pdf_id === $expected_id ) {
			return true;
		}

		return '' !== $pdf_name && 0 === strcasecmp( $pdf_name, $this->get_pdf_name( $type ) );
	}

	public function get_pdf_shortcode( $type, Lead $Lead )
	{
		if ( ! $Lead->get_form_entry_id() ) {
			return '';
		}

		$pdf = $this->find_entry_pdf( $type, $Lead->get_pdfs() );
		if ( empty( $pdf['id'] ) ) {
			return '';
		}

		return do_shortcode(
			sprintf(
				'[gravitypdf id="%s" entry="%s" type="view" raw="1"]',
				esc_attr( $pdf['id'] ),
				esc_attr( $Lead->get_form_entry_id() )
			)
		);
	}

	public function get_pdf_name( $type )
	{
		return self::TYPE_ROUTING === $type ? 'Routing PDF' : 'Quote PDF';
	}

	protected function get_pdf_id( $type )
	{
		if ( isset( $this->pdf_ids[ $type ] ) ) {
			return $this->pdf_ids[ $type ];
		}

		$key = $this->get_pdf_setting_key( $type );
		$configured = trim( (string) PC_CPQ()->Settings()->{"get_{$key}"}() );
		if ( '' !== $configured ) {
			$this->pdf_ids[ $type ] = $configured;
			return $this->pdf_ids[ $type ];
		}

		$detected = $this->detect_pdf_id( $type );
		$this->pdf_ids[ $type ] = $detected;
		$this->maybe_persist_setting( $key, $detected );

		return $this->pdf_ids[ $type ];
	}

	protected function detect_quote_form_id()
	{
		$form_id = $this->detect_form_id_from_existing_entries();
		if ( $form_id > 0 ) {
			return $form_id;
		}

		if ( ! class_exists( 'GFAPI' ) || ! class_exists( 'GPDFAPI' ) || ! method_exists( 'GFAPI', 'get_forms' ) || ! method_exists( 'GPDFAPI', 'get_form_pdfs' ) ) {
			return 0;
		}

		$forms = GFAPI::get_forms();
		if ( empty( $forms ) || is_wp_error( $forms ) ) {
			return 0;
		}

		foreach ( $forms as $form ) {
			$form_id = absint( $form['id'] ?? 0 );
			if ( $form_id <= 0 ) {
				continue;
			}

			$pdfs = GPDFAPI::get_form_pdfs( $form_id );
			if ( is_wp_error( $pdfs ) || empty( $pdfs ) ) {
				continue;
			}

			foreach ( $pdfs as $pdf ) {
				$name = (string) ( $pdf['name'] ?? '' );
				if ( 0 === strcasecmp( $name, $this->get_pdf_name( self::TYPE_QUOTE ) ) || 0 === strcasecmp( $name, $this->get_pdf_name( self::TYPE_ROUTING ) ) ) {
					return $form_id;
				}
			}
		}

		return 0;
	}

	protected function detect_form_id_from_existing_entries()
	{
		if ( ! class_exists( 'GFAPI' ) || ! method_exists( 'GFAPI', 'get_entry' ) ) {
			return 0;
		}

		$lead_ids = get_posts(
			array(
				'post_type' => 'lead',
				'posts_per_page' => 10,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => 'form_entry_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( empty( $lead_ids ) ) {
			return 0;
		}

		foreach ( $lead_ids as $lead_id ) {
			$entry_id = get_post_meta( $lead_id, 'form_entry_id', true );
			if ( ! $entry_id ) {
				continue;
			}

			$entry = GFAPI::get_entry( $entry_id );
			if ( is_wp_error( $entry ) ) {
				continue;
			}

			$form_id = absint( $entry['form_id'] ?? 0 );
			if ( $form_id > 0 ) {
				return $form_id;
			}
		}

		return 0;
	}

	protected function detect_pdf_id( $type )
	{
		if ( ! class_exists( 'GPDFAPI' ) || ! method_exists( 'GPDFAPI', 'get_form_pdfs' ) ) {
			return '';
		}

		$form_id = $this->get_quote_form_id();
		if ( $form_id <= 0 ) {
			return '';
		}

		$pdfs = GPDFAPI::get_form_pdfs( $form_id );
		if ( is_wp_error( $pdfs ) || empty( $pdfs ) ) {
			return '';
		}

		$pdf = $this->find_pdf_by_name( $type, $pdfs );
		return isset( $pdf['id'] ) ? (string) $pdf['id'] : '';
	}

	protected function find_entry_pdf( $type, $pdfs )
	{
		$expected_id = (string) $this->get_pdf_id( $type );
		if ( '' !== $expected_id ) {
			foreach ( (array) $pdfs as $pdf ) {
				if ( $expected_id === (string) ( $pdf['id'] ?? '' ) ) {
					return $pdf;
				}
			}
		}

		return $this->find_pdf_by_name( $type, $pdfs );
	}

	protected function find_pdf_by_name( $type, $pdfs )
	{
		$expected_name = $this->get_pdf_name( $type );
		foreach ( (array) $pdfs as $pdf ) {
			if ( 0 === strcasecmp( (string) ( $pdf['name'] ?? '' ), $expected_name ) ) {
				return $pdf;
			}
		}

		return array();
	}

	protected function get_pdf_setting_key( $type )
	{
		return self::TYPE_ROUTING === $type ? 'routing_pdf_id' : 'quote_pdf_id';
	}

	protected function get_layout_setting_key( $type )
	{
		return self::TYPE_ROUTING === $type ? 'routing_pdf_layout' : 'quote_pdf_layout';
	}

	protected function maybe_persist_setting( $prop, $value )
	{
		if ( '' === $value || null === $value ) {
			return;
		}

		$current = PC_CPQ()->Settings()->{"get_{$prop}"}();
		if ( (string) $current === (string) $value ) {
			return;
		}

		PC_CPQ()->Settings()->update_prop( $prop, $value );
	}

	protected function sanitize_layout( $layout )
	{
		$layout = sanitize_key( (string) $layout );
		return in_array( $layout, array( self::LAYOUT_STANDARD, self::LAYOUT_COMPACT ), true ) ? $layout : self::LAYOUT_STANDARD;
	}
}
