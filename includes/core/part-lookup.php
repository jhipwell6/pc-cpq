<?php

namespace PC_CPQ\Core;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part_Lookup
{
	protected static $instance;
	private static $db_table_name = 'spc_part_index';
	private $parts;
	private $wpdb;
	private $db_table;

	public function __construct()
	{
		global $wpdb;
		$this->set_wpdb( $wpdb );
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

	private function set_wpdb( $wpdb )
	{
		$this->wpdb = $wpdb;
		return $this->wpdb;
	}

	public function get_db_table()
	{
		if ( null === $this->db_table ) {
			$this->db_table = $this->wpdb->prefix . self::$db_table_name;
		}
		return $this->db_table;
	}

	public function get_all()
	{
		if ( null === $this->parts ) {
			$db_table = $this->wpdb->prefix . self::$db_table_name;
			$sql = "SELECT * FROM {$db_table} WHERE 1 = 1";

			// query parts
			$this->parts = $this->query( $sql );
		}

		return $this->parts;
	}

	public function is_hash( $value )
	{
		return strlen( $value ) == 32 && ctype_xdigit( $value );
	}

	/*
	 * $args (array)
	 * 'id' INT
	 * 'post_id' INT
	 * 'part_name' STRING
	 */

	public function insert_part( $args )
	{
		$defaults = array(
			'post_id' => '',
			'part_name' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		if ( isset( $args['part_name'] ) ) {
			if ( is_array( $args['part_name'] ) ) {
				$args['part_name'] = md5( implode( '', $args['part_name'] ) );
			} else if ( ! $this->is_hash( $args['part_name'] ) ) {
				$args['part_name'] = md5( $args['part_name'] );
			}
		}

		$this->wpdb->insert( $this->get_db_table(), $args, array( '%d', '%s' ) );
	}

	public function delete_part( $id )
	{
		$this->wpdb->delete( $this->get_db_table(), array( 'id' => $id ), array( '%d' ) );
	}

	public function search_parts( $name, $id = null )
	{
		if ( ! $this->is_hash( $name ) ) {
			$name = md5( $name );
		}
		$db_table = $this->get_db_table();
		if ( $id ) {
			$sql = $this->wpdb->prepare( "SELECT * FROM {$db_table} WHERE part_name = '%s' AND post_id != %d", $name, $id );
		} else {
			$sql = $this->wpdb->prepare( "SELECT * FROM {$db_table} WHERE part_name = '%s'", $name );
		}
		return $this->query( $sql );
	}

	public function get_part( $id )
	{
		$db_table = $this->get_db_table();
		$sql = $this->wpdb->prepare( "SELECT * FROM {$db_table} WHERE id = %s", $id );
		return $this->query( $sql );
	}

	private function query( $sql )
	{
		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	public static function install()
	{
		$mu_installed = get_option( 'spc_part_index_installed' );

		if ( ! $mu_installed ) {
			self::create_db_table();
			update_option( 'spc_part_index_installed', true );
		}
	}

	private static function create_db_table()
	{
		global $wpdb;
		$db_table = $wpdb->prefix . self::$db_table_name;

		$sql = "CREATE TABLE `$db_table` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`post_id` INT DEFAULT NULL,
			`part_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
