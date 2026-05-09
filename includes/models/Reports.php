<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Reports
{
	public function maybe_export_csv()
	{
		$type = $this->get_export_type();
		if ( ! $type ) {
			return false;
		}

		$filters = $this->get_filters();
		$bounds = $this->get_date_bounds( $filters );
		$export = $this->get_export_payload( $type, $bounds['from_datetime'], $bounds['to_datetime'] );

		if ( empty( $export ) ) {
			return false;
		}

		$this->stream_csv( $export['filename'], $export['headers'], $export['rows'] );
		exit;
	}

	public function get_data()
	{
		$filters = $this->get_filters();
		$bounds = $this->get_date_bounds( $filters );

		return array(
			'filters' => $filters,
			'lead_status_summary' => $this->get_lead_status_summary( $bounds['from_datetime'], $bounds['to_datetime'] ),
			'activity_trend_chart' => $this->get_activity_trend_chart( $bounds['from_datetime'], $bounds['to_datetime'] ),
			'lead_status_trend_chart' => $this->get_lead_status_trend_chart( $bounds['from_datetime'], $bounds['to_datetime'] ),
			'quotes_sent_summary' => $this->get_meta_count( 'quote_date', $bounds['from_datetime'], $bounds['to_datetime'] ),
			'quotes_expiring_summary' => $this->get_meta_count( 'expiration_date', $bounds['from_datetime'], $bounds['to_datetime'] ),
			'quotes_sent_rows' => $this->get_lead_rows_by_meta( 'quote_date', $bounds['from_datetime'], $bounds['to_datetime'], 10, 'DESC' ),
			'expiring_quote_rows' => $this->get_lead_rows_by_meta( 'expiration_date', $bounds['from_datetime'], $bounds['to_datetime'], 10, 'ASC' ),
			'follow_up_overdue_count' => $this->get_overdue_follow_up_count(),
			'follow_up_range_count' => $this->get_meta_count( 'follow_up_date', $bounds['from_datetime'], $bounds['to_datetime'] ),
			'follow_up_rows' => $this->get_lead_rows_by_meta( 'follow_up_date', $bounds['from_datetime'], $bounds['to_datetime'], 15, 'ASC' ),
		);
	}

	public function get_export_url( $type, $filters = null )
	{
		$filters = is_array( $filters ) ? $filters : $this->get_filters();

		return add_query_arg( array(
			'report_from' => $filters['from'],
			'report_to' => $filters['to'],
			'report_export' => sanitize_key( $type ),
		) );
	}

	public function get_filters()
	{
		$today = current_time( 'Y-m-d' );
		$default_from = gmdate( 'Y-m-d', strtotime( '-30 days', strtotime( $today ) ) );

		$from = isset( $_GET['report_from'] ) ? sanitize_text_field( wp_unslash( $_GET['report_from'] ) ) : $default_from;
		$to = isset( $_GET['report_to'] ) ? sanitize_text_field( wp_unslash( $_GET['report_to'] ) ) : $today;

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$from = $default_from;
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$to = $today;
		}

		if ( strtotime( $from ) > strtotime( $to ) ) {
			$temp = $from;
			$from = $to;
			$to = $temp;
		}

		return array(
			'from' => $from,
			'to' => $to,
		);
	}

	public function get_date_bounds( $filters )
	{
		return array(
			'from_datetime' => $filters['from'] . ' 00:00:00',
			'to_datetime' => $filters['to'] . ' 23:59:59',
		);
	}

	public function get_lead_status_summary( $from_datetime, $to_datetime )
	{
		global $wpdb;

		$defaults = array(
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
					AND posts.post_date BETWEEN %s AND %s
				GROUP BY lead_status
				",
				'status',
				'lead',
				'publish',
				$from_datetime,
				$to_datetime
			),
			ARRAY_A
		);

		foreach ( $results as $row ) {
			$defaults[ $row['lead_status'] ] = intval( $row['total'] );
		}

		return $defaults;
	}

	public function get_meta_count( $meta_key, $from_datetime, $to_datetime )
	{
		global $wpdb;
		$bounds = $this->get_timestamp_bounds( $from_datetime, $to_datetime );

		return intval(
			$wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT COUNT(*)
					FROM {$wpdb->posts} posts
					INNER JOIN {$wpdb->postmeta} pm
						ON pm.post_id = posts.ID
						AND pm.meta_key = %s
					WHERE posts.post_type = %s
						AND posts.post_status = %s
						AND CAST( pm.meta_value AS UNSIGNED ) BETWEEN %d AND %d
					",
					$meta_key,
					'lead',
					'publish',
					$bounds['from_timestamp'],
					$bounds['to_timestamp']
				)
			)
		);
	}

	public function get_overdue_follow_up_count()
	{
		global $wpdb;
		$current_timestamp = current_time( 'timestamp' );

		return intval(
			$wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT COUNT(*)
					FROM {$wpdb->posts} posts
					INNER JOIN {$wpdb->postmeta} follow_up
						ON follow_up.post_id = posts.ID
						AND follow_up.meta_key = %s
					LEFT JOIN {$wpdb->postmeta} status
						ON status.post_id = posts.ID
						AND status.meta_key = %s
					WHERE posts.post_type = %s
						AND posts.post_status = %s
						AND CAST( follow_up.meta_value AS UNSIGNED ) < %d
						AND COALESCE( NULLIF( status.meta_value, '' ), 'New' ) NOT IN ( 'Quoted', 'Canceled', 'No Quote' )
					",
					'follow_up_date',
					'status',
					'lead',
					'publish',
					$current_timestamp
				)
			)
		);
	}

	public function get_lead_rows_by_meta( $meta_key, $from_datetime, $to_datetime, $limit = 10, $order = 'ASC' )
	{
		$order = 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
		$bounds = $this->get_timestamp_bounds( $from_datetime, $to_datetime );

		$query = new \WP_Query( array(
			'post_type' => 'lead',
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'fields' => 'ids',
			'meta_key' => $meta_key,
			'orderby' => 'meta_value',
			'order' => $order,
			'meta_type' => 'NUMERIC',
			'no_found_rows' => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query' => array(
				array(
					'key' => $meta_key,
					'value' => array( $bounds['from_timestamp'], $bounds['to_timestamp'] ),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC',
				),
			),
		) );

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

	public function build_lead_row( $lead_id )
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
			'title' => $this->normalize_export_text( get_the_title( $lead_id ) ),
			'company' => $this->normalize_export_text( $company ? $company : 'N/A' ),
			'status' => $this->normalize_export_text( $status ? $status : 'New' ),
			'date' => get_the_date( 'm/d/Y', $lead_id ),
			'manage_url' => PC_CPQ()->Site()->get_leads_page_url() . $lead_id,
			'quote_date' => $this->format_datetime( $quote_date ),
			'follow_up_date' => $this->format_datetime( $follow_up_date ),
			'expiration_date' => $this->format_datetime( $expiration_date ),
		);
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

	public function get_activity_trend_chart( $from_datetime, $to_datetime )
	{
		$buckets = $this->get_date_buckets( $from_datetime, $to_datetime );

		return array(
			'labels' => array_values( $buckets['labels'] ),
			'datasets' => array(
				array(
					'label' => 'Leads Created',
					'data' => array_values( $this->merge_bucket_counts( $buckets['keys'], $this->get_post_date_counts( $from_datetime, $to_datetime ) ) ),
					'borderColor' => '#007bff',
					'backgroundColor' => 'rgba(0, 123, 255, 0.15)',
				),
				array(
					'label' => 'Quotes Sent',
					'data' => array_values( $this->merge_bucket_counts( $buckets['keys'], $this->get_meta_date_counts( 'quote_date', $from_datetime, $to_datetime ) ) ),
					'borderColor' => '#28a745',
					'backgroundColor' => 'rgba(40, 167, 69, 0.15)',
				),
				array(
					'label' => 'Follow-Ups Scheduled',
					'data' => array_values( $this->merge_bucket_counts( $buckets['keys'], $this->get_meta_date_counts( 'follow_up_date', $from_datetime, $to_datetime ) ) ),
					'borderColor' => '#17a2b8',
					'backgroundColor' => 'rgba(23, 162, 184, 0.15)',
				),
				array(
					'label' => 'Quotes Expiring',
					'data' => array_values( $this->merge_bucket_counts( $buckets['keys'], $this->get_meta_date_counts( 'expiration_date', $from_datetime, $to_datetime ) ) ),
					'borderColor' => '#ffc107',
					'backgroundColor' => 'rgba(255, 193, 7, 0.15)',
				),
			),
		);
	}

	public function get_lead_status_trend_chart( $from_datetime, $to_datetime )
	{
		$buckets = $this->get_date_buckets( $from_datetime, $to_datetime );
		$status_counts = $this->get_post_date_status_counts( $from_datetime, $to_datetime );
		$statuses = array(
			'New' => '#007bff',
			'Pending' => '#ffc107',
			'Quoted' => '#28a745',
			'No Quote' => '#6c757d',
			'Canceled' => '#dc3545',
		);
		$datasets = array();

		foreach ( $statuses as $status => $color ) {
			$datasets[] = array(
				'label' => $status,
				'data' => array_values( $this->merge_bucket_counts( $buckets['keys'], isset( $status_counts[ $status ] ) ? $status_counts[ $status ] : array() ) ),
				'backgroundColor' => $color,
				'stack' => 'lead-statuses',
			);
		}

		return array(
			'labels' => array_values( $buckets['labels'] ),
			'datasets' => $datasets,
		);
	}

	public function get_date_buckets( $from_datetime, $to_datetime )
	{
		$from_timestamp = strtotime( $from_datetime );
		$to_timestamp = strtotime( $to_datetime );
		$keys = array();
		$labels = array();

		if ( ! $from_timestamp || ! $to_timestamp || $from_timestamp > $to_timestamp ) {
			return array(
				'keys' => $keys,
				'labels' => $labels,
			);
		}

		for ( $timestamp = $from_timestamp; $timestamp <= $to_timestamp; $timestamp = strtotime( '+1 day', $timestamp ) ) {
			$key = gmdate( 'Y-m-d', $timestamp );
			$keys[] = $key;
			$labels[] = wp_date( 'M j', $timestamp );
		}

		return array(
			'keys' => $keys,
			'labels' => $labels,
		);
	}

	public function merge_bucket_counts( $keys, $counts )
	{
		$data = array();

		foreach ( $keys as $key ) {
			$data[ $key ] = isset( $counts[ $key ] ) ? intval( $counts[ $key ] ) : 0;
		}

		return $data;
	}

	public function get_post_date_counts( $from_datetime, $to_datetime )
	{
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DATE( posts.post_date ) AS bucket_date, COUNT(*) AS total
				FROM {$wpdb->posts} posts
				WHERE posts.post_type = %s
					AND posts.post_status = %s
					AND posts.post_date BETWEEN %s AND %s
				GROUP BY DATE( posts.post_date )
				ORDER BY bucket_date ASC
				",
				'lead',
				'publish',
				$from_datetime,
				$to_datetime
			),
			ARRAY_A
		);

		return $this->map_bucket_results( $results );
	}

	public function get_meta_date_counts( $meta_key, $from_datetime, $to_datetime )
	{
		global $wpdb;
		$bounds = $this->get_timestamp_bounds( $from_datetime, $to_datetime );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DATE( FROM_UNIXTIME( CAST( pm.meta_value AS UNSIGNED ) ) ) AS bucket_date, COUNT(*) AS total
				FROM {$wpdb->posts} posts
				INNER JOIN {$wpdb->postmeta} pm
					ON pm.post_id = posts.ID
					AND pm.meta_key = %s
				WHERE posts.post_type = %s
					AND posts.post_status = %s
					AND CAST( pm.meta_value AS UNSIGNED ) BETWEEN %d AND %d
				GROUP BY DATE( FROM_UNIXTIME( CAST( pm.meta_value AS UNSIGNED ) ) )
				ORDER BY bucket_date ASC
				",
				$meta_key,
				'lead',
				'publish',
				$bounds['from_timestamp'],
				$bounds['to_timestamp']
			),
			ARRAY_A
		);

		return $this->map_bucket_results( $results );
	}

	public function get_post_date_status_counts( $from_datetime, $to_datetime )
	{
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DATE( posts.post_date ) AS bucket_date,
					COALESCE( NULLIF( pm.meta_value, '' ), 'New' ) AS lead_status,
					COUNT(*) AS total
				FROM {$wpdb->posts} posts
				LEFT JOIN {$wpdb->postmeta} pm
					ON pm.post_id = posts.ID
					AND pm.meta_key = %s
				WHERE posts.post_type = %s
					AND posts.post_status = %s
					AND posts.post_date BETWEEN %s AND %s
				GROUP BY DATE( posts.post_date ), lead_status
				ORDER BY bucket_date ASC
				",
				'status',
				'lead',
				'publish',
				$from_datetime,
				$to_datetime
			),
			ARRAY_A
		);

		$counts = array();

		foreach ( $results as $row ) {
			$status = $row['lead_status'];
			if ( ! isset( $counts[ $status ] ) ) {
				$counts[ $status ] = array();
			}
			$counts[ $status ][ $row['bucket_date'] ] = intval( $row['total'] );
		}

		return $counts;
	}

	public function map_bucket_results( $results )
	{
		$counts = array();

		foreach ( $results as $row ) {
			$counts[ $row['bucket_date'] ] = intval( $row['total'] );
		}

		return $counts;
	}

	public function get_timestamp_bounds( $from_datetime, $to_datetime )
	{
		return array(
			'from_timestamp' => strtotime( $from_datetime ),
			'to_timestamp' => strtotime( $to_datetime ),
		);
	}

	public function get_export_type()
	{
		if ( ! isset( $_GET['report_export'] ) ) {
			return '';
		}

		return sanitize_key( wp_unslash( $_GET['report_export'] ) );
	}

	public function get_export_payload( $type, $from_datetime, $to_datetime )
	{
		$filters = $this->get_filters();
		$date_suffix = $filters['from'] . '_to_' . $filters['to'];

		switch ( $type ) {
			case 'lead_status_summary':
				$rows = array();
				foreach ( $this->get_lead_status_summary( $from_datetime, $to_datetime ) as $status => $count ) {
					$rows[] = array( $status, $count );
				}

				return array(
					'filename' => 'lead-status-summary-' . $date_suffix . '.csv',
					'headers' => array( 'Status', 'Count' ),
					'rows' => $rows,
				);

			case 'quotes_sent':
				return array(
					'filename' => 'quotes-sent-' . $date_suffix . '.csv',
					'headers' => $this->get_lead_export_headers( 'quote_date' ),
					'rows' => $this->get_lead_export_rows( $this->get_lead_rows_by_meta( 'quote_date', $from_datetime, $to_datetime, -1, 'DESC' ), 'quote_date' ),
				);

			case 'quotes_expiring':
				return array(
					'filename' => 'quotes-expiring-' . $date_suffix . '.csv',
					'headers' => $this->get_lead_export_headers( 'expiration_date' ),
					'rows' => $this->get_lead_export_rows( $this->get_lead_rows_by_meta( 'expiration_date', $from_datetime, $to_datetime, -1, 'ASC' ), 'expiration_date' ),
				);

			case 'follow_up_queue':
				return array(
					'filename' => 'follow-up-queue-' . $date_suffix . '.csv',
					'headers' => $this->get_lead_export_headers( 'follow_up_date' ),
					'rows' => $this->get_lead_export_rows( $this->get_lead_rows_by_meta( 'follow_up_date', $from_datetime, $to_datetime, -1, 'ASC' ), 'follow_up_date' ),
				);
		}

		return array();
	}

	public function get_lead_export_headers( $date_column )
	{
		$headers = array(
			'Quote Number',
			'Lead',
			'Company',
			'Status',
			'Created Date',
		);

		switch ( $date_column ) {
			case 'quote_date':
				$headers[] = 'Quote Date';
				break;
			case 'expiration_date':
				$headers[] = 'Expiration Date';
				break;
			case 'follow_up_date':
				$headers[] = 'Follow-Up Date';
				break;
		}

		$headers[] = 'Manage URL';

		return $headers;
	}

	public function get_lead_export_rows( $rows, $date_column )
	{
		return array_map( function ( $lead ) use ( $date_column ) {
			return array(
				$lead['quote_number'],
				$lead['title'],
				$lead['company'],
				$lead['status'],
				$lead['date'],
				isset( $lead[ $date_column ] ) ? $lead[ $date_column ] : '',
				$lead['manage_url'],
			);
		}, $rows );
	}

	public function stream_csv( $filename, $headers, $rows )
	{
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $filename ) );

		$output = fopen( 'php://output', 'w' );
		fwrite( $output, "\xEF\xBB\xBF" );
		fputcsv( $output, array_map( array( $this, 'normalize_export_text' ), $headers ) );

		foreach ( $rows as $row ) {
			fputcsv( $output, array_map( array( $this, 'normalize_export_text' ), $row ) );
		}

		fclose( $output );
	}

	protected function normalize_export_text( $value )
	{
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}

		$value = (string) $value;
		$value = wp_specialchars_decode( $value, ENT_QUOTES );
		$value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return trim( $value );
	}
}
