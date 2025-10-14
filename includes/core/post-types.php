<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Post_Types
{
	protected static $instance;

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_action( 'init', array( $this, 'register_post_types' ) );
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

	public function register_post_types()
	{
		/**
		 * Post Type: Leads.
		 */
		$labels = array(
			"name" => __( "Leads", PC_CPQ_DOMAIN ),
			"singular_name" => __( "Lead", PC_CPQ_DOMAIN ),
			"menu_name" => __( "Leads", PC_CPQ_DOMAIN ),
			"all_items" => __( "All Leads", PC_CPQ_DOMAIN ),
			"add_new" => __( "Add new", PC_CPQ_DOMAIN ),
			"add_new_item" => __( "Add new Lead", PC_CPQ_DOMAIN ),
			"edit_item" => __( "Edit Lead", PC_CPQ_DOMAIN ),
			"new_item" => __( "New Lead", PC_CPQ_DOMAIN ),
			"view_item" => __( "View Lead", PC_CPQ_DOMAIN ),
			"view_items" => __( "View Leads", PC_CPQ_DOMAIN ),
			"search_items" => __( "Search Leads", PC_CPQ_DOMAIN ),
			"not_found" => __( "No Leads found", PC_CPQ_DOMAIN ),
			"not_found_in_trash" => __( "No Leads found in trash", PC_CPQ_DOMAIN ),
			"parent" => __( "Parent Lead:", PC_CPQ_DOMAIN ),
			"featured_image" => __( "Featured image for this Lead", PC_CPQ_DOMAIN ),
			"set_featured_image" => __( "Set featured image for this Lead", PC_CPQ_DOMAIN ),
			"remove_featured_image" => __( "Remove featured image for this Lead", PC_CPQ_DOMAIN ),
			"use_featured_image" => __( "Use as featured image for this Lead", PC_CPQ_DOMAIN ),
			"archives" => __( "Lead archives", PC_CPQ_DOMAIN ),
			"insert_into_item" => __( "Insert into Lead", PC_CPQ_DOMAIN ),
			"uploaded_to_this_item" => __( "Upload to this Lead", PC_CPQ_DOMAIN ),
			"filter_items_list" => __( "Filter Leads list", PC_CPQ_DOMAIN ),
			"items_list_navigation" => __( "Leads list navigation", PC_CPQ_DOMAIN ),
			"items_list" => __( "Leads list", PC_CPQ_DOMAIN ),
			"attributes" => __( "Leads attributes", PC_CPQ_DOMAIN ),
			"name_admin_bar" => __( "Lead", PC_CPQ_DOMAIN ),
			"item_published" => __( "Lead published", PC_CPQ_DOMAIN ),
			"item_published_privately" => __( "Lead published privately.", PC_CPQ_DOMAIN ),
			"item_reverted_to_draft" => __( "Lead reverted to draft.", PC_CPQ_DOMAIN ),
			"item_scheduled" => __( "Lead scheduled", PC_CPQ_DOMAIN ),
			"item_updated" => __( "Lead updated.", PC_CPQ_DOMAIN ),
			"parent_item_colon" => __( "Parent Lead:", PC_CPQ_DOMAIN ),
		);

		$args = array(
			"label" => __( "Leads", PC_CPQ_DOMAIN ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => false,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"can_export" => true,
			"rewrite" => array( "slug" => "lead", "with_front" => false ),
			"query_var" => true,
			"menu_icon" => "dashicons-text-page",
			"supports" => array( "title" ),
			"show_in_graphql" => false,
		);

		register_post_type( "lead", $args );

		/**
		 * Post Type: Customers.
		 */
		$labels = array(
			"name" => __( "Customers", PC_CPQ_DOMAIN ),
			"singular_name" => __( "Customer", PC_CPQ_DOMAIN ),
		);

		$args = array(
			"label" => __( "Customers", PC_CPQ_DOMAIN ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => false,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"can_export" => true,
			"rewrite" => array( "slug" => "customer", "with_front" => false ),
			"query_var" => true,
			"supports" => array( "title" ),
			"show_in_graphql" => false,
		);

		register_post_type( "customer", $args );
	}

}

Post_Types::instance();
