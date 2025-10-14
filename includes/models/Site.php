<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Abstract_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Site extends Abstract_Model
{
	protected $url;
	protected $company;
	protected $website;
	protected $logo;
	protected $phone;
	protected $manage_page;
	protected $support_page;
	protected $settings_page;
	protected $leads_page;
	protected $customers_page;
	
	// computed
	protected $page_heading;
	protected $manage_page_url;
	protected $manage_subpages;
	protected $support_page_url;
	protected $settings_page_url;
	protected $leads_page_url;
	protected $customers_page_url;

	public function __construct( $site_id )
	{
		$this->set_id( $site_id );
		return $this;
	}

	public function get_url()
	{
		if ( null === $this->url ) {
			$this->url = get_site_url();
		}
		return $this->url;
	}
	
	public function get_company()
	{
		return $this->get_prop( 'company' );
	}
	
	public function get_website()
	{
		return $this->get_prop( 'website' );
	}

	public function get_logo( $prop = null )
	{
		if ( null === $this->logo ) {
			$this->logo = $this->get_meta( 'logo' );
		}
		return ( null !== $prop && isset( $this->logo[ $prop ] ) ) ? $this->logo[ $prop ] : $this->logo;
	}

	public function get_phone()
	{
		return $this->get_prop( 'phone' );
	}

	public function get_manage_page()
	{
		return $this->get_prop( 'manage_page' );
	}
	
	public function get_support_page()
	{
		return $this->get_prop( 'support_page' );
	}
	
	public function get_settings_page()
	{
		return $this->get_prop( 'settings_page' );
	}
	
	public function get_leads_page()
	{
		return $this->get_prop( 'leads_page' );
	}
	
	public function get_customers_page()
	{
		return $this->get_prop( 'customers_page' );
	}
	
	/* 
	 * Computed
	 */
	
	public function get_page_heading()
	{
		if ( null === $this->page_heading ) {
			switch ( true ) {
				case $this->is_manage_lead():
					$this->page_heading = 'Edit Lead';
					break;
				case $this->is_manage_customer():
					$this->page_heading = 'Edit Customer';
					break;
				default:
					$this->page_heading = get_the_title();
			}
		}
		return $this->page_heading;
	}

	public function get_manage_subpages()
	{
		if ( null === $this->manage_subpages ) {
			$offspring = $this->get_manage_page() ? get_pages( array( 'child_of' => $this->get_manage_page() ) ) : array();
			$this->manage_subpages = ! empty( $offspring ) ? wp_list_pluck( $offspring, 'ID' ) : array();
		}
		return $this->manage_subpages;
	}
	
	public function get_manage_page_url()
	{
		if ( null === $this->manage_page_url ) {
			$this->manage_page_url = get_permalink( $this->get_manage_page() );
		}
		return $this->manage_page_url;
	}
	
	public function get_support_page_url()
	{
		if ( null === $this->support_page_url ) {
			$this->support_page_url = get_permalink( $this->get_support_page() );
		}
		return $this->support_page_url;
	}
	
	public function get_settings_page_url( $subpage = null )
	{
		if ( null === $this->settings_page_url ) {
			$this->settings_page_url = get_permalink( $this->get_settings_page() );
		}
		return null !== $subpage ? trailingslashit( $this->settings_page_url ) . $subpage : $this->settings_page_url;
	}
	
	public function get_leads_page_url()
	{
		if ( null === $this->leads_page_url ) {
			$this->leads_page_url = get_permalink( $this->get_leads_page() );
		}
		return $this->leads_page_url;
	}
	
	public function get_customers_page_url()
	{
		if ( null === $this->customers_page_url ) {
			$this->customers_page_url = get_permalink( $this->get_customers_page() );
		}
		return $this->customers_page_url;
	}

	/*
	 * Public helper methods
	 */
	
	public function is_manage()
	{
		return ! empty( $this->get_manage_pages() ) && is_page( $this->get_manage_pages() );
	}
	
	public function is_forgot_password()
	{
		return isset( $_GET['forgot_password'] );
	}
	
	public function get_manage_pages()
	{
		return array_filter( array_unique( array_merge(
			array( $this->get_manage_page() ), $this->get_manage_subpages()
		) ) );
	}
	
	public function has_logo()
	{
		return (bool) $this->get_logo();
	}
	
	public function is_manage_lead()
	{
		return is_page( $this->get_leads_page() ) && ( get_query_var('lead_id') !== false && get_query_var('lead_id') !== '' );
	}
	
	public function is_manage_customer()
	{
		return is_page( $this->get_customers_page() ) && ( get_query_var('customer_id') !== false && get_query_var('customer_id') !== '' );
	}
	
	public function is_manage_endpoint()
	{
		return $this->is_manage_lead() || $this->is_manage_customer();
	}

	public function get_current_endpoint_url()
	{
		switch ( true ) {
			case $this->is_manage_lead():
				return $this->get_leads_page_url();
				break;
			case $this->is_manage_customer():
				return $this->get_customers_page_url();
				break;
			default:
				return get_permalink();
		}
	}
	
	public function get_current_endpoint_var()
	{
		switch ( true ) {
			case $this->is_manage_lead():
				return get_query_var('lead_id');
				break;
			case $this->is_manage_customer():
				return get_query_var('customer_id');
				break;
			default:
				return false;
		}
	}
	
	/*
	 * Helpers
	 */

	public function get_hidden()
	{
		return array(
			'manage_page',
			'manage_subpages',
		);
	}

	protected function get_meta( $prop )
	{
		if ( function_exists( 'get_field' ) ) {
			return get_field( 'site_' . $prop, 'option' );
		} else {
			return get_option( 'site_' . $prop );
		}
	}

}
