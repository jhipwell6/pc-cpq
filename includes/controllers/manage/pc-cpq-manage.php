<?php

namespace PC_CPQ\Controllers\Manage;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Manage extends MVC_Controller_Registry
{
	protected $query;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1 );
		add_filter( 'paginate_links_output', [ $this, 'format_pagination_output' ], 10, 1 );
		add_shortcode( 'pc_cpq_manage_dashboard', array( $this, 'pc_cpq_view_manage_dashboard' ) );
		add_shortcode( 'pc_cpq_manage_lead_list', array( $this, 'pc_cpq_view_manage_lead_list' ) );
		add_shortcode( 'pc_cpq_manage_customer_list', array( $this, 'pc_cpq_view_manage_customer_list' ) );
		add_shortcode( 'pc_cpq_manage_support', array( $this, 'pc_cpq_view_manage_support' ) );
		add_shortcode( 'pc_cpq_manage_settings', array( $this, 'pc_cpq_view_manage_settings' ) );

//		add_filter( 'posts_where', [ $this, 'custom_acf_search_where' ] );
//		add_filter( 'posts_distinct', [ $this, 'custom_acf_search_distinct' ] );
//		add_filter( 'posts_join', [ $this, 'custom_acf_search_join' ] );
	}

	public function add_rewrite_rules()
	{
		$manage_leads_page = get_post( PC_CPQ()->Site()->get_leads_page() );
		if ( $manage_leads_page ) {
			$uri = get_page_uri( $manage_leads_page );
			add_rewrite_rule( '^' . $uri . '/?([^/]*)/?', 'index.php?page_id=' . $manage_leads_page->ID . '&lead_id=$matches[1]', 'top' );
		}

		$manage_customers_page = get_post( PC_CPQ()->Site()->get_customers_page() );
		if ( $manage_customers_page ) {
			$uri = get_page_uri( $manage_customers_page );
			add_rewrite_rule( '^' . $uri . '/?([^/]*)/?', 'index.php?page_id=' . $manage_customers_page->ID . '&customer_id=$matches[1]', 'top' );
		}
	}

	public function add_query_vars( $query_vars )
	{
		$query_vars[] = 'lead_id';
		$query_vars[] = 'customer_id';
		$query_vars[] = 'offset';
		return $query_vars;
	}

	public function pc_cpq_view_manage_dashboard()
	{
		return PC_CPQ()->view( 'manage/dashboard' );
	}

	public function pc_cpq_view_manage_lead_list()
	{
		if ( get_query_var( 'lead_id' ) == false || get_query_var( 'lead_id' ) == '' ) {
			$data = array(
				'leads' => $this->get_leads(),
				'max_pages' => $this->get_query()->max_num_pages,
			);
			return PC_CPQ()->view( 'manage/lead-list', $data );
		} else {
			$Lead = PC_CPQ()->lead( get_query_var( 'lead_id' ) );
			$data = array(
				'Lead' => $Lead,
			);
			return PC_CPQ()->view( 'manage/form-edit-lead', $data );
		}
	}

	public function pc_cpq_view_manage_customer_list()
	{
		if ( get_query_var( 'customer_id' ) == false || get_query_var( 'customer_id' ) == '' ) {
			$data = array(
				'customers' => $this->get_customers(),
				'max_pages' => $this->get_query()->max_num_pages,
			);
			return PC_CPQ()->view( 'manage/customer-list', $data );
		} else {
			$data = array(
				'Customer' => PC_CPQ()->customer( get_query_var( 'customer_id' ) ),
			);
			return PC_CPQ()->view( 'manage/form-edit-customer', $data );
		}
	}

	public function pc_cpq_view_manage_support()
	{
		return PC_CPQ()->view( 'manage/support' );
	}

	public function pc_cpq_view_manage_settings( $atts )
	{
		$data = array(
			'Settings' => PC_CPQ()->Settings(),
		);
		$path = isset( $atts['page'] ) ? '/form-' . $atts['page'] : '';
		return PC_CPQ()->view( 'manage/settings' . $path, $data );
	}

	public function format_pagination_output( $output )
	{
		$output_formatted_ul = str_replace( "<ul class='page-numbers'>", "<ul class='pagination pagination-sm m-0'>", $output );
		$output_formatted_links = str_replace( 'page-numbers', 'page-link', $output_formatted_ul );
		$formatted_output = str_replace( '<li>', '<li class="page-item">', $output_formatted_links );
		return $formatted_output;
	}

	public function my_custom_search_query_var( $query )
	{
		if ( ! is_admin() && $query->is_main_query() && isset( $_GET['q'] ) ) {
			$query->set( 's', sanitize_text_field( $_GET['q'] ) );
		}
	}

	public function custom_acf_search_join( $join )
	{
		global $wpdb;

		if ( is_search() ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} acf_meta ON {$wpdb->posts}.ID = acf_meta.post_id ";
		}

		return $join;
	}

	public function custom_acf_search_where( $where )
	{
		global $wpdb;

		if ( is_search() && ! is_admin() && isset( $_GET['q'] ) ) {
			$like = '%' . esc_sql( sanitize_text_field( $_GET['q'] ) ) . '%';

			$where .= " AND (
				{$wpdb->posts}.post_title LIKE '{$like}' 
				OR (
					acf_meta.meta_key IN ('company', 'first_name', 'last_name', 'email', 'raw_specs')
					AND acf_meta.meta_value LIKE '{$like}'
				)
			)";
		}

		return $where;
	}

	public function custom_acf_search_distinct( $distinct )
	{
		if ( is_search() ) {
			return 'DISTINCT';
		}

		return $distinct;
	}

	/*
	 * Private methods
	 */

	private function get_leads()
	{
		$offset = ( get_query_var( 'offset' ) ) ? get_query_var( 'offset' ) : 1;
		$args = [
			'post_type' => 'lead',
			'posts_per_page' => 20,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $offset,
		];

		if ( isset( $_GET['q'] ) ) {
			$search_term = sanitize_text_field( $_GET['q'] );
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key' => 'company',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'first_name',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'last_name',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'email',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'raw_specs',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'quote_number',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
				[
					'key' => 'nutshell_id',
					'value' => $search_term,
					'compare' => 'LIKE',
				],
			];
		}

		$leads = new \WP_Query( $args );

		if ( ! $leads->have_posts() ) {
			return [];
		}

		$this->set_query( $leads );

		return array_map( function ( $lead ) {
			return PC_CPQ()->lead( $lead->ID );
		}, $leads->posts );
	}

	private function get_customers()
	{
		$offset = ( get_query_var( 'offset' ) ) ? get_query_var( 'offset' ) : 1;
		$args = [
			'post_type' => 'customer',
			'posts_per_page' => 20,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $offset,
		];

		if ( isset( $_GET['q'] ) ) {
			$search_term = sanitize_text_field( $_GET['q'] );
			$args['s'] = $search_term;
//			$args['meta_query'] = [
//				'relation' => 'OR',
//				[
//					'key' => 'customer_code',
//					'value' => $search_term,
//					'compare' => 'LIKE',
//				],
//				[
//					'key' => 'first_name',
//					'value' => $search_term,
//					'compare' => 'LIKE',
//				],
//				[
//					'key' => 'last_name',
//					'value' => $search_term,
//					'compare' => 'LIKE',
//				],
//				[
//					'key' => 'email',
//					'value' => $search_term,
//					'compare' => 'LIKE',
//				],
//			];
		}
		
		$customers = new \WP_Query( $args );

		if ( ! $customers->have_posts() ) {
			return [];
		}

		$this->set_query( $customers );

		return array_map( function ( $customer ) {
			return PC_CPQ()->customer( $customer->ID );
		}, $customers->posts );
	}

	private function get_query()
	{
		return $this->query;
	}

	private function set_query( $query )
	{
		$this->query = $query;
		return $query;
	}

}

PC_CPQ_Manage::instance();
