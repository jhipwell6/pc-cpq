<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class User_Roles
{
	protected static $instance;
	const ROLES_VERSION = 3;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_action( 'admin_init', [ $this, 'register_user_roles' ] );
	}

	/**
	 * Static Singleton Factory Method
	 * @return self
	 */
	public static function instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register_user_roles()
	{
		if ( (int) get_option( 'spc_roles_version' ) < self::ROLES_VERSION ) {
			global $wp_roles;
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}

			$editor = $wp_roles->get_role( 'editor' );
			if ( ! $editor ) {
				return;
			}

			$workspace_owner_caps = $editor->capabilities;
			$workspace_owner_caps['gravityforms_view_entries'] = true;
			$workspace_owner_caps['pc_cpq_manage_workspace'] = true;
			$workspace_owner_caps['pc_cpq_manage_quotes'] = true;
			$workspace_owner_caps['pc_cpq_manage_settings'] = true;

			$quote_manager_caps = $editor->capabilities;
			$quote_manager_caps['gravityforms_view_entries'] = true;
			$quote_manager_caps['pc_cpq_manage_quotes'] = true;

			$this->sync_role( 'workspace_owner', 'Workspace Owner', $workspace_owner_caps );
			$this->sync_role( 'quote_manager', 'Quote Manager', $quote_manager_caps );
			$this->migrate_legacy_manager_role();

			update_option( 'spc_roles_version', self::ROLES_VERSION );
		}
	}

	private function sync_role( $slug, $label, $capabilities )
	{
		$role = get_role( $slug );

		if ( ! $role ) {
			$role = add_role( $slug, $label, $capabilities );
		}

		if ( ! $role ) {
			return;
		}

		foreach ( array_keys( (array) $role->capabilities ) as $capability ) {
			$role->remove_cap( $capability );
		}

		foreach ( $capabilities as $capability => $grant ) {
			if ( $grant ) {
				$role->add_cap( $capability );
			}
		}

		global $wp_roles;
		if ( isset( $wp_roles->roles[ $slug ] ) ) {
			$wp_roles->roles[ $slug ]['name'] = $label;
		}
		if ( isset( $wp_roles->role_names[ $slug ] ) ) {
			$wp_roles->role_names[ $slug ] = $label;
		}
	}

	private function migrate_legacy_manager_role()
	{
		$legacy_users = get_users(
			array(
				'role' => 'manager',
				'fields' => 'ids',
			)
		);

		foreach ( $legacy_users as $user_id ) {
			$user = new \WP_User( $user_id );
			$user->add_role( 'workspace_owner' );
			$user->remove_role( 'manager' );
		}

		remove_role( 'manager' );
	}

}

User_Roles::instance();
