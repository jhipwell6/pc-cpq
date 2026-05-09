<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User
{
	const ROLE_WORKSPACE_OWNER = 'workspace_owner';
	const ROLE_QUOTE_MANAGER = 'quote_manager';
	const ROLE_LEGACY_MANAGER = 'manager';

	public static function get_workspace_role_labels()
	{
		return array(
			self::ROLE_WORKSPACE_OWNER => 'Workspace Owner',
			self::ROLE_QUOTE_MANAGER => 'Quote Manager',
		);
	}

	public static function is_workspace_role( $role )
	{
		return array_key_exists( $role, self::get_workspace_role_labels() );
	}

	protected $user;

	public function __construct( $user = null )
	{
		if ( null === $user ) {
			$user = wp_get_current_user();
		}

		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'id', absint( $user ) );
		}

		$this->user = $user instanceof \WP_User ? $user : new \WP_User( 0 );
	}

	public function get_wp_user()
	{
		return $this->user;
	}

	public function get_id()
	{
		return absint( $this->user->ID );
	}

	public function is_logged_in()
	{
		return $this->get_id() > 0;
	}

	public function has_role( $role )
	{
		return $this->is_logged_in() && in_array( $role, (array) $this->user->roles, true );
	}

	public function get_primary_role()
	{
		$roles = (array) $this->user->roles;
		return ! empty( $roles ) ? reset( $roles ) : '';
	}

	public function get_role_label( $role = '' )
	{
		$role = $role ? $role : $this->get_primary_role();
		$labels = self::get_workspace_role_labels();

		if ( isset( $labels[ $role ] ) ) {
			return $labels[ $role ];
		}

		if ( 'administrator' === $role ) {
			return 'Administrator';
		}

		return ucwords( str_replace( '_', ' ', (string) $role ) );
	}

	public function has_cap( $cap )
	{
		return $this->is_logged_in() && user_can( $this->user, $cap );
	}

	public function is_administrator()
	{
		return $this->has_cap( 'manage_options' ) || $this->has_role( 'administrator' );
	}

	public function is_workspace_owner()
	{
		return $this->is_administrator()
			|| $this->has_role( self::ROLE_WORKSPACE_OWNER )
			|| $this->has_role( self::ROLE_LEGACY_MANAGER )
			|| $this->has_cap( 'pc_cpq_manage_workspace' );
	}

	public function is_quote_manager()
	{
		return $this->is_workspace_owner()
			|| $this->has_role( self::ROLE_QUOTE_MANAGER )
			|| $this->has_cap( 'pc_cpq_manage_quotes' );
	}

	public function can_manage_quotes()
	{
		return $this->is_quote_manager();
	}

	public function can_manage_settings()
	{
		return $this->is_workspace_owner() || $this->has_cap( 'pc_cpq_manage_settings' );
	}

	public function can_view_reports()
	{
		return $this->can_manage_quotes();
	}

	public function can_view_support()
	{
		return $this->can_manage_quotes();
	}

	public function can_access_wp_admin()
	{
		if ( ! $this->is_logged_in() ) {
			return true;
		}

		if ( $this->is_administrator() ) {
			return true;
		}

		return ! ( $this->is_workspace_owner() || $this->has_role( self::ROLE_QUOTE_MANAGER ) );
	}

	public function can_access_manage_page()
	{
		$Site = PC_CPQ()->Site();

		if ( ! $Site->is_manage() ) {
			return true;
		}

		if ( ! $this->is_logged_in() ) {
			return false;
		}

		if ( $Site->is_manage_settings() ) {
			return $this->can_manage_settings();
		}

		if ( $Site->is_manage_reports() ) {
			return $this->can_view_reports();
		}

		if ( $Site->is_manage_support() ) {
			return $this->can_view_support();
		}

		if ( $Site->is_manage_dashboard() || $Site->is_manage_lead() || $Site->is_manage_customer() ) {
			return $this->can_manage_quotes();
		}

		return $this->can_manage_quotes();
	}

	public function get_manage_redirect_url()
	{
		$Site = PC_CPQ()->Site();

		if ( $this->can_manage_quotes() && $Site->get_leads_page_url() ) {
			return $Site->get_leads_page_url();
		}

		return home_url( '/' );
	}

	public function maybe_redirect_unauthorized_manage_access()
	{
		if ( $this->can_access_manage_page() ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'pc_cpq_notice' => 'access_denied',
			),
			$this->get_manage_redirect_url()
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	public function maybe_redirect_unauthorized_wp_admin_access()
	{
		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( $this->can_access_wp_admin() ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'pc_cpq_notice' => 'admin_redirected',
			),
			$this->get_manage_redirect_url()
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	public function assert_can_manage_quotes()
	{
		if ( $this->can_manage_quotes() ) {
			return;
		}

		wp_send_json_error(
			array(
				'message' => 'You do not have permission to manage leads or customers in this workspace.',
			),
			403
		);
	}

	public function assert_can_manage_settings()
	{
		if ( $this->can_manage_settings() ) {
			return;
		}

		wp_send_json_error(
			array(
				'message' => 'You do not have permission to manage workspace settings.',
			),
			403
		);
	}
}
