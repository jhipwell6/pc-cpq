<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Dashboard
{
	const CACHE_TTL = 300;

	public function get_data()
	{
		$cache_key = $this->get_cache_key();
		$cached = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			$cached['Settings'] = PC_CPQ()->Settings();
			return $cached;
		}

		$Settings = PC_CPQ()->Settings();
		$workspace_checklist = $Settings->get_onboarding_checklist();
		$data = array(
			'Settings' => $Settings,
			'status_cards' => $this->get_status_cards(),
			'recent_leads' => $this->get_recent_leads(),
			'follow_up_leads' => $this->get_follow_up_leads(),
			'expiring_quotes' => $this->get_expiring_quotes(),
			'workspace_checklist' => $workspace_checklist,
			'workspace_completed_count' => count( array_filter( $workspace_checklist, function ( $item ) {
				return ! empty( $item['complete'] );
			} ) ),
			'workspace_total_count' => count( $workspace_checklist ),
		);

		set_transient( $cache_key, $data, self::CACHE_TTL );

		return $data;
	}

	public function clear_cache()
	{
		delete_transient( $this->get_cache_key() );
	}

	public function maybe_clear_cache_for_post( $post_id )
	{
		if ( 'lead' === get_post_type( $post_id ) ) {
			$this->clear_cache();
		}
	}

	public function format_datetime( $value, $format = 'm/d/Y g:i A' )
	{
		if ( empty( $value ) ) {
			return '';
		}

		$timestamp = is_numeric( $value ) ? intval( $value ) : strtotime( $value );

		if ( ! $timestamp ) {
			return '';
		}

		return wp_date( $format, $timestamp );
	}

	private function get_status_cards()
	{
		global $wpdb;

		$counts = array(
			'New' => 0,
			'Pending' => 0,
			'Quoted' => 0,
			'No Quote' => 0,
			'Canceled' => 0,
		);

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT COALESCE( NULLIF( pm.meta_value, '' ), 'New' ) AS lead_status, COUNT(*) AS total
				FROM {$wpdb->posts} posts
				LEFT JOIN {$wpdb->postmeta} pm
					ON pm.post_id = posts.ID
					AND pm.meta_key = %s
				WHERE posts.post_type = %s
					AND posts.post_status = %s
				GROUP BY lead_status
				",
				'status',
				'lead',
				'publish'
			),
			ARRAY_A
		);

		foreach ( $results as $row ) {
			$status = $row['lead_status'];
			$counts[ $status ] = intval( $row['total'] );
		}

		return array(
			array(
				'label' => 'New Leads',
				'value' => $counts['New'] ?? 0,
				'icon' => 'fas fa-inbox',
				'color' => 'primary',
			),
			array(
				'label' => 'Pending',
				'value' => $counts['Pending'] ?? 0,
				'icon' => 'fas fa-hourglass-half',
				'color' => 'warning',
			),
			array(
				'label' => 'Quoted',
				'value' => $counts['Quoted'] ?? 0,
				'icon' => 'fas fa-file-signature',
				'color' => 'success',
			),
			array(
				'label' => 'No Quote',
				'value' => $counts['No Quote'] ?? 0,
				'icon' => 'fas fa-ban',
				'color' => 'secondary',
			),
		);
	}

	private function get_recent_leads( $limit = 6 )
	{
		return $this->get_lead_rows( array(
			'posts_per_page' => $limit,
			'orderby' => 'date',
			'order' => 'DESC',
		) );
	}

	private function get_follow_up_leads( $limit = 5 )
	{
		$today = current_time( 'timestamp' );
		$next_week = strtotime( '+7 days', $today );

		return $this->get_lead_rows( array(
			'posts_per_page' => $limit,
			'meta_key' => 'follow_up_date',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_type' => 'NUMERIC',
			'meta_query' => array(
				array(
					'key' => 'follow_up_date',
					'value' => array( $today, $next_week ),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC',
				),
			),
		) );
	}

	private function get_expiring_quotes( $limit = 5 )
	{
		$today = current_time( 'timestamp' );
		$next_week = strtotime( '+7 days', $today );

		return $this->get_lead_rows( array(
			'posts_per_page' => $limit,
			'meta_key' => 'expiration_date',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_type' => 'NUMERIC',
			'meta_query' => array(
				array(
					'key' => 'expiration_date',
					'value' => array( $today, $next_week ),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC',
				),
			),
		) );
	}

	private function get_lead_rows( $args = array() )
	{
		$query = new \WP_Query( array_merge( array(
			'post_type' => 'lead',
			'post_status' => 'publish',
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		), $args ) );

		if ( ! $query->have_posts() ) {
			return array();
		}

		$customer_ids = array_filter( array_map( function ( $lead_id ) {
			return intval( get_post_meta( $lead_id, 'customer', true ) );
		}, $query->posts ) );

		if ( ! empty( $customer_ids ) ) {
			get_posts( array(
				'post_type' => 'customer',
				'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
				'post__in' => array_unique( $customer_ids ),
				'posts_per_page' => -1,
			) );
		}

		return array_map( function ( $lead_id ) {
			return $this->build_lead_row( $lead_id );
		}, $query->posts );
	}

	private function build_lead_row( $lead_id )
	{
		$quote_number = get_post_meta( $lead_id, 'quote_number', true );
		$status = get_post_meta( $lead_id, 'status', true );
		$company = get_post_meta( $lead_id, 'company', true );
		$customer_id = intval( get_post_meta( $lead_id, 'customer', true ) );
		$quote_date = get_post_meta( $lead_id, 'quote_date', true );
		$follow_up_date = get_post_meta( $lead_id, 'follow_up_date', true );
		$expiration_date = get_post_meta( $lead_id, 'expiration_date', true );

		if ( '' === $company && $customer_id > 0 ) {
			$company = get_the_title( $customer_id );
		}

		return array(
			'id' => $lead_id,
			'quote_number' => $quote_number ? $quote_number : 0,
			'title' => get_the_title( $lead_id ),
			'company' => $company ? $company : 'N/A',
			'status' => $status ? $status : 'New',
			'date' => get_the_date( 'm/d/Y', $lead_id ),
			'manage_url' => PC_CPQ()->Site()->get_leads_page_url() . $lead_id,
			'quote_date' => $this->format_datetime( $quote_date ),
			'follow_up_date' => $this->format_datetime( $follow_up_date ),
			'expiration_date' => $this->format_datetime( $expiration_date ),
		);
	}

	private function get_cache_key()
	{
		return 'pc_cpq_dashboard_' . get_current_blog_id();
	}
}
