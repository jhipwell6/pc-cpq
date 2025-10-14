<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class User_Roles
{
	protected static $instance;

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
		if ( get_option( 'spc_roles_version' ) < 2 ) {
			global $wp_roles;
			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			$manager = $wp_roles->get_role( 'manager' );
			if ( ! $manager ) {
				$editor = $wp_roles->get_role( 'editor' );
				$manager = add_role( 'manager', 'Manager', $editor->capabilities );
			}
			$manager->add_cap( 'gravityforms_view_entries' );

			update_option( 'spc_roles_version', 2 );
		}
	}

}

User_Roles::instance();
