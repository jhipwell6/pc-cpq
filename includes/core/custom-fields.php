<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Custom_Fields
{
	protected static $instance;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 98 );
		add_action( 'acf/init', array( $this, 'add_fields' ), 10 );
		add_action( 'init', function () {
			if ( ! function_exists( 'acf_register_field_type' ) ) {
				return;
			}
			include_once PC_CPQ()->plugin_path() . '/includes/core/field-css-editor.php';
			acf_register_field_type( 'acf_field_css_editor' );
			
			include_once PC_CPQ()->plugin_path() . '/includes/core/field-html-editor.php';
			acf_register_field_type( 'acf_field_html_editor' );
		});
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

	public function add_options_page()
	{
		if ( function_exists( 'acf_add_options_page' ) ) {
			$parent = acf_add_options_page( array(
				'page_title' => 'General Options',
				'menu_title' => 'Options',
				'redirect' => false
				) );

			acf_add_options_sub_page( array(
				'page_title' => 'Plating Options',
				'menu_title' => 'Plating Options',
				'parent_slug' => $parent['menu_slug']
			) );

			acf_add_options_sub_page( array(
				'page_title' => 'Process Options',
				'menu_title' => 'Process Options',
				'parent_slug' => $parent['menu_slug']
			) );

			acf_add_options_sub_page( array(
				'page_title' => 'Quote Options',
				'menu_title' => 'Quote Options',
				'parent_slug' => $parent['menu_slug']
			) );

			acf_add_options_sub_page( array(
				'page_title' => 'Site Config',
				'menu_title' => 'Site Config',
				'parent_slug' => $parent['menu_slug']
			) );

			acf_add_options_page( [
				'page_title' => 'Site Code',
				'menu_title' => 'Site Code',
				'menu_slug' => 'site-code',
				'capability' => 'manage_options',
				'redirect' => false,
			] );
		}
	}

	public function add_fields()
	{
		$fields_path = PC_CPQ()->plugin_path() . '/includes/core/acf-fields/';
		foreach ( glob( $fields_path . '*.php' ) as $field ) {
			include_once( $field );
		}
	}

}

Custom_Fields::instance();
