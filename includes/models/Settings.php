<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Abstract_Model;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Settings extends Abstract_Model
{
	/*
	 * Parts
	 */
	protected $hourly_rate;
	protected $default_margin;
	protected $default_eff;
	protected $default_people;
	protected $default_eau;
	protected $default_shift;
	protected $default_break_in;
	protected $default_metal_adder;

	/*
	 * Quotes
	 */
	protected $starting_quote_number;
	protected $quote_expires_after;
	protected $follow_up_after;
	protected $domain_whitelist;
	protected $email_whitelist;
	
	/*
	 * Templates
	 */
	protected $quote_header;
	protected $quote_footer;
	protected $quote_terms;
	protected static $Email_Template_Class = 'PC_CPQ\Models\Settings\Email_Template';
	protected $raw_email_templates;
	protected $Email_Templates;
	
	
	/*
	 * Plating
	 */
	protected static $Metal_Class = 'PC_CPQ\Models\Settings\Metal';
	protected $raw_metals;
	protected $Metals;
	protected static $Plating_Metal_Class = 'PC_CPQ\Models\Settings\Plating_Metal';
	protected $raw_plating_metals;
	protected $Plating_Metals;
	protected static $Line_Class = 'PC_CPQ\Models\Settings\Line';
	protected $raw_lines;
	protected $Lines;
	protected static $Barrel_Class = 'PC_CPQ\Models\Settings\Barrel';
	protected $raw_barrels;
	protected $Barrels;
	protected static $Rack_Class = 'PC_CPQ\Models\Settings\Rack';
	protected $raw_racks;
	protected $Racks;
	protected static $Operation_Class = 'PC_CPQ\Models\Settings\Operation';
	protected $raw_operations;
	protected $Operations;
	protected $Post_Operations;
	protected $post_ops_order;
	

	/*
	 * Getters
	 */

	public function get_hourly_rate()
	{
		return $this->get_prop( 'hourly_rate' );
	}

	public function get_default_margin()
	{
		return $this->get_prop( 'default_margin' );
	}

	public function get_default_eff()
	{
		return $this->get_prop( 'default_eff' );
	}

	public function get_default_people()
	{
		return $this->get_prop( 'default_people' );
	}

	public function get_default_eau()
	{
		return $this->get_prop( 'default_eau' );
	}

	public function get_default_shift()
	{
		return $this->get_prop( 'default_shift' );
	}

	public function get_default_break_in()
	{
		return $this->get_prop( 'default_break_in' );
	}

	public function get_default_metal_adder()
	{
		return $this->get_prop( 'default_metal_adder' );
	}
	
	public function get_starting_quote_number()
	{
		return $this->get_prop( 'starting_quote_number' );
	}
	
	public function get_quote_expires_after()
	{
		return $this->get_prop( 'quote_expires_after' );
	}
	
	public function get_follow_up_after()
	{
		return $this->get_prop( 'follow_up_after' );
	}
	
	public function get_domain_whitelist()
	{
		return $this->get_prop( 'domain_whitelist' );
	}
	
	public function get_email_whitelist()
	{
		return $this->get_prop( 'email_whitelist' );
	}
	
	public function get_quote_header()
	{
		return $this->get_prop( 'quote_header' );
	}
	
	public function get_quote_footer()
	{
		return $this->get_prop( 'quote_footer' );
	}
	
	public function get_quote_terms()
	{
		return $this->get_prop( 'quote_terms' );
	}

	public function get_raw_email_templates()
	{
		if ( null === $this->raw_email_templates ) {
			$this->raw_email_templates = $this->get_meta( 'email_templates' );
		}
		return $this->raw_email_templates;
	}

	public function get_Email_Templates( $force_update = false )
	{
		if ( null === $this->Email_Templates ) {
			$this->Email_Templates = array();
			if ( ! empty( $this->get_raw_email_templates() ) ) {
				foreach ( $this->get_raw_email_templates() as $index => $raw_email_template ) {
					$this->add_Email_Template( $index, $raw_email_template, false );
				}
			}
		}
		return $this->Email_Templates;
	}
	
	public function get_raw_metals()
	{
		if ( null === $this->raw_metals ) {
			$this->raw_metals = $this->get_meta( 'metals' );
		}
		return $this->raw_metals;
	}

	public function get_Metals( $force_update = false )
	{
		if ( null === $this->Metals ) {
			$this->Metals = array();
			if ( ! empty( $this->get_raw_metals() ) ) {
				foreach ( $this->get_raw_metals() as $index => $raw_metal ) {
					$this->add_Metal( $index, $raw_metal, false );
				}
			}
		}
		return $this->Metals;
	}
	
	public function get_raw_plating_metals()
	{
		if ( null === $this->raw_plating_metals ) {
			$this->raw_plating_metals = $this->get_meta( 'plating_metals' );
		}
		return $this->raw_plating_metals;
	}

	public function get_Plating_Metals( $force_update = false )
	{
		if ( null === $this->Plating_Metals ) {
			$this->Plating_Metals = array();
			if ( ! empty( $this->get_raw_plating_metals() ) ) {
				foreach ( $this->get_raw_plating_metals() as $index => $raw_plating_metal ) {
					$this->add_Plating_Metal( $index, $raw_plating_metal, false );
				}
			}
		}
		return $this->Plating_Metals;
	}
	
	public function get_Available_Plating_Metals()
	{
		return array_filter( $this->get_Plating_Metals(), function( $Plating_Metal ) {
			return ! $Plating_Metal->is_hidden();
		} );
	}
	
	public function get_raw_lines()
	{
		if ( null === $this->raw_lines ) {
			$this->raw_lines = $this->get_meta( 'lines' );
		}
		return $this->raw_lines;
	}

	public function get_Lines( $force_update = false )
	{
		if ( null === $this->Lines ) {
			$this->Lines = array();
			if ( ! empty( $this->get_raw_lines() ) ) {
				foreach ( $this->get_raw_lines() as $index => $raw_line ) {
					$this->add_Line( $index, $raw_line, false );
				}
			}
		}
		return $this->Lines;
	}
	
	public function get_raw_barrels()
	{
		if ( null === $this->raw_barrels ) {
			$this->raw_barrels = $this->get_meta( 'barrels' );
		}
		return $this->raw_barrels;
	}

	public function get_Barrels( $force_update = false )
	{
		if ( null === $this->Barrels ) {
			$this->Barrels = array();
			if ( ! empty( $this->get_raw_barrels() ) ) {
				foreach ( $this->get_raw_barrels() as $index => $raw_barrel ) {
					$this->add_Barrel( $index, $raw_barrel, false );
				}
			}
		}
		return $this->Barrels;
	}
	
	public function get_raw_racks()
	{
		if ( null === $this->raw_racks ) {
			$this->raw_racks = $this->get_meta( 'racks' );
		}
		return $this->raw_racks;
	}

	public function get_Racks( $force_update = false )
	{
		if ( null === $this->Racks ) {
			$this->Racks = array();
			if ( ! empty( $this->get_raw_racks() ) ) {
				foreach ( $this->get_raw_racks() as $index => $raw_rack ) {
					$this->add_Rack( $index, $raw_rack, false );
				}
			}
		}
		return $this->Racks;
	}
	
	public function get_raw_operations()
	{
		if ( null === $this->raw_operations ) {
			$this->raw_operations = $this->get_meta( 'operations' );
		}
		return $this->raw_operations;
	}

	public function get_Operations( $force_update = false )
	{
		if ( null === $this->Operations ) {
			$this->Operations = array();
			if ( ! empty( $this->get_raw_operations() ) ) {
				foreach ( $this->get_raw_operations() as $index => $raw_operation ) {
					$this->add_Operation( $index, $raw_operation, false );
				}
			}
		}
		return $this->Operations;
	}
	
	public function get_Post_Operations()
	{
		if ( null === $this->Post_Operations ) {
			$post_operations = array_filter( $this->get_Operations(), function( $Operation ) {
				return $Operation->get_type() == 'Post';
			} );
			
			if ( $this->get_post_ops_order() ) {
				$order = json_decode( $this->get_post_ops_order(), true );
				foreach ( $order as $item ) {
					$this->Post_Operations[] = array_first( array_filter( $post_operations, function( $Operation ) use ( $item ) {
						return $Operation->get_operation() == $item['operation'];
					} ) );
				}
			}
		}
		return $this->Post_Operations;
	}
	
	public function get_post_ops_order()
	{
		return $this->get_prop( 'post_ops_order' );
	}
	
	/*
	 * Setters
	 */

	public function set_hourly_rate( $value )
	{
		return $this->set_prop( 'hourly_rate', $value );
	}

	public function set_default_margin( $value )
	{
		return $this->set_prop( 'default_margin', $value );
	}

	public function set_default_eff( $value )
	{
		return $this->set_prop( 'default_eff', $value );
	}

	public function set_default_people( $value )
	{
		return $this->set_prop( 'default_people', $value );
	}

	public function set_default_eau( $value )
	{
		return $this->set_prop( 'default_eau', $value );
	}

	public function set_default_shift( $value )
	{
		return $this->set_prop( 'default_shift', $value );
	}

	public function set_default_break_in( $value )
	{
		return $this->set_prop( 'default_break_in', $value );
	}

	public function set_default_metal_adder( $value )
	{
		return $this->set_prop( 'default_metal_adder', $value );
	}
	
	public function set_starting_quote_number( $value )
	{
		return $this->set_prop( 'starting_quote_number', $value );
	}
	
	public function set_quote_expires_after( $value )
	{
		return $this->set_prop( 'quote_expires_after', $value );
	}
	
	public function set_follow_up_after( $value )
	{
		return $this->set_prop( 'follow_up_after', $value );
	}
	
	public function set_domain_whitelist( $value )
	{
		return $this->set_prop( 'domain_whitelist', $value );
	}
	
	public function set_email_whitelist( $value )
	{
		return $this->set_prop( 'email_whitelist', $value );
	}
	
	public function set_quote_header( $value )
	{
		return $this->set_prop( 'quote_header', $value );
	}
	
	public function set_quote_footer( $value )
	{
		return $this->set_prop( 'quote_footer', $value );
	}
	
	public function set_quote_terms( $value )
	{
		return $this->set_prop( 'quote_terms', $value );
	}
	
	public function set_raw_email_templates( $value )
	{
		return $this->set_prop( 'raw_email_templates', $value );
	}
	
	public function set_raw_metals( $value )
	{
		return $this->set_prop( 'raw_metals', $value );
	}
	
	public function set_raw_plating_metals( $value )
	{
		return $this->set_prop( 'raw_plating_metals', $value );
	}
	
	public function set_raw_lines( $value )
	{
		return $this->set_prop( 'raw_lines', $value );
	}
	
	public function set_raw_barrels( $value )
	{
		return $this->set_prop( 'raw_barrels', $value );
	}
	
	public function set_raw_racks( $value )
	{
		return $this->set_prop( 'raw_racks', $value );
	}
	
	public function set_raw_operations( $value )
	{
		return $this->set_prop( 'raw_operations', $value );
	}
	
	public function set_post_ops_order( $value )
	{
		return $this->set_prop( 'post_ops_order', $value );
	}
	
	/*
	 * Savers
	 */
	
	public function save_raw_email_templates_meta( $value )
	{
		$result = update_field( 'email_templates', $value, 'option' );
		$this->refresh_Email_Templates();
		return $result;
	}

	public function save_Email_Templates()
	{
		$email_templates = array_map( function ( $Email_Template ) {
			return $Email_Template->to_array();
		}, $this->get_Email_Templates() );

		$this->update_prop( 'raw_email_templates', $email_templates );
	}
	
	public function save_raw_metals_meta( $value )
	{
		$result = update_field( 'metals', $value, 'option' );
		$this->refresh_Metals();
		return $result;
	}

	public function save_Metals()
	{
		$metals = array_map( function ( $Metal ) {
			return $Metal->to_array();
		}, $this->get_Metals() );

		$this->update_prop( 'raw_metals', $metals );
	}
	
	public function save_raw_plating_metals_meta( $value )
	{
		$result = update_field( 'plating_metals', $value, 'option' );
		$this->refresh_Plating_Metals();
		return $result;
	}

	public function save_Plating_Metals()
	{
		$plating_metals = array_map( function ( $Plating_Metal ) {
			return $Plating_Metal->to_array();
		}, $this->get_Plating_Metals() );

		$this->update_prop( 'raw_plating_metals', $plating_metals );
	}
	
	public function save_raw_lines_meta( $value )
	{
		$result = update_field( 'lines', $value, 'option' );
		$this->refresh_Lines();
		return $result;
	}

	public function save_Lines()
	{
		$lines = array_map( function ( $Line ) {
			return $Line->to_array();
		}, $this->get_Lines() );

		$this->update_prop( 'raw_lines', $lines );
	}
	
	public function save_raw_barrels_meta( $value )
	{
		$result = update_field( 'barrells', $value, 'option' );
		$this->refresh_Barrels();
		return $result;
	}

	public function save_Barrels()
	{
		$barrels = array_map( function ( $Barrel ) {
			return $Barrel->to_array();
		}, $this->get_Barrels() );

		$this->update_prop( 'raw_barrels', $barrels );
	}
	
	public function save_raw_racks_meta( $value )
	{
		$result = update_field( 'racks', $value, 'option' );
		$this->refresh_Racks();
		return $result;
	}

	public function save_Racks()
	{
		$racks = array_map( function ( $Rack ) {
			return $Rack->to_array();
		}, $this->get_Racks() );

		$this->update_prop( 'raw_racks', $racks );
	}
	
	public function save_raw_operations_meta( $value )
	{
		$result = update_field( 'operations', $value, 'option' );
		$this->refresh_Operations();
		return $result;
	}

	public function save_Operations()
	{
		$operations = array_map( function ( $Operation ) {
			return $Operation->to_array();
		}, $this->get_Operations() );

		$this->update_prop( 'raw_operations', $operations );
	}
	
	/*
	 * Helpers
	 */
	
	public function get_Email_Templates_count()
	{
		return count( $this->get_Email_Templates() );
	}

	public function add_Email_Template( $index = null, $raw_email_template = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Email_Templates_count();
		}
		$this->Email_Templates[] = new self::$Email_Template_Class( $index, $raw_email_template );

		if ( $save ) {
			$this->save_Email_Templates();
		}
	}
	
	public function delete_Email_Template( $index )
	{
		$Email_Templates = $this->get_Email_Templates();
		if ( isset( $Email_Templates[$index] ) ) {
			unset( $Email_Templates[$index] );
		}
		$this->Email_Templates = array_values( $Email_Templates );

		$this->save_Email_Templates();
	}
	
	public function refresh_Email_Templates()
	{
		$this->get_Email_Templates( true );
	}
	
	public function get_Metals_count()
	{
		return count( $this->get_Metals() );
	}

	public function add_Metal( $index = null, $raw_metal = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Metals_count();
		}
		$this->Metals[] = new self::$Metal_Class( $index, $raw_metal );

		if ( $save ) {
			$this->save_Metals();
		}
	}
	
	public function delete_Metal( $index )
	{
		$Metals = $this->get_Metals();
		if ( isset( $Metals[$index] ) ) {
			unset( $Metals[$index] );
		}
		$this->Metals = array_values( $Metals );

		$this->save_Metals();
	}
	
	public function refresh_Metals()
	{
		$this->get_Metals( true );
	}
	
	public function get_Plating_Metals_count()
	{
		return count( $this->get_Plating_Metals() );
	}

	public function add_Plating_Metal( $index = null, $raw_plating_metal = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Plating_Metals_count();
		}
		$this->Plating_Metals[] = new self::$Plating_Metal_Class( $index, $raw_plating_metal );

		if ( $save ) {
			$this->save_Plating_Metals();
		}
	}
	
	public function delete_Plating_Metal( $index )
	{
		$Plating_Metals = $this->get_Plating_Metals();
		if ( isset( $Plating_Metals[$index] ) ) {
			unset( $Plating_Metals[$index] );
		}
		$this->Plating_Metals = array_values( $Plating_Metals );

		$this->save_Plating_Metals();
	}
	
	public function refresh_Plating_Metals()
	{
		$this->get_Plating_Metals( true );
	}
	
	public function get_Lines_count()
	{
		return count( $this->get_Lines() );
	}

	public function add_Line( $index = null, $raw_line = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Lines_count();
		}
		$this->Lines[] = new self::$Line_Class( $index, $raw_line );

		if ( $save ) {
			$this->save_Lines();
		}
	}
	
	public function delete_Line( $index )
	{
		$Lines = $this->get_Lines();
		if ( isset( $Lines[$index] ) ) {
			unset( $Lines[$index] );
		}
		$this->Lines = array_values( $Lines );

		$this->save_Lines();
	}
	
	public function refresh_Lines()
	{
		$this->get_Lines( true );
	}
	
	public function get_Barrels_count()
	{
		return count( $this->get_Barrels() );
	}

	public function add_Barrel( $index = null, $raw_barrel = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Barrels_count();
		}
		$this->Barrels[] = new self::$Barrel_Class( $index, $raw_barrel );

		if ( $save ) {
			$this->save_Barrels();
		}
	}
	
	public function delete_Barrel( $index )
	{
		$Barrels = $this->get_Barrels();
		if ( isset( $Barrels[$index] ) ) {
			unset( $Barrels[$index] );
		}
		$this->Barrels = array_values( $Barrels );

		$this->save_Barrels();
	}
	
	public function refresh_Barrels()
	{
		$this->get_Barrels( true );
	}
	
	public function get_Racks_count()
	{
		return count( $this->get_Racks() );
	}

	public function add_Rack( $index = null, $raw_rack = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Racks_count();
		}
		$this->Racks[] = new self::$Rack_Class( $index, $raw_rack );

		if ( $save ) {
			$this->save_Racks();
		}
	}
	
	public function delete_Rack( $index )
	{
		$Racks = $this->get_Racks();
		if ( isset( $Racks[$index] ) ) {
			unset( $Racks[$index] );
		}
		$this->Racks = array_values( $Racks );

		$this->save_Racks();
	}
	
	public function refresh_Racks()
	{
		$this->get_Racks( true );
	}
	
	public function get_Operations_count()
	{
		return count( $this->get_Operations() );
	}

	public function add_Operation( $index = null, $raw_operation = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Operations_count();
		}
		$this->Operations[] = new self::$Operation_Class( $index, $raw_operation );

		if ( $save ) {
			$this->save_Operations();
		}
	}
	
	public function delete_Operation( $index )
	{
		$Operations = $this->get_Operations();
		if ( isset( $Operations[$index] ) ) {
			unset( $Operations[$index] );
		}
		$this->Operations = array_values( $Operations );

		$this->save_Operations();
	}
	
	public function refresh_Operations()
	{
		$this->get_Operations( true );
	}

	public function get_hidden()
	{
		return array(
		);
	}

	protected function get_meta( $prop )
	{
		if ( function_exists( 'get_field' ) ) {
			return get_field( $prop, 'option' );
		} else {
			return get_option( 'setting_' . $prop );
		}
	}

	protected function set_prop( $prop, $value )
	{
		if ( $this->has_prop( $prop ) ) {
			$this->{$prop} = $value;
			return $this->{$prop};
		}
		return false;
	}

	protected function can_save_meta( $prop, $value )
	{
		$setter = $this->get_setter( $prop );
		return null !== $value && is_callable( array( $this, $setter ) );
	}

	public function save_meta( $prop, $value )
	{
		// ensures only allowable props are saved
		if ( $this->can_save_meta( $prop, $value ) ) {
			// allow extending classes to hijack per property
			$saver = "save_{$prop}_meta";
			if ( is_callable( array( $this, $saver ) ) ) {
				return $this->{$saver}( $value );
			}

			// optional ACF support
			if ( function_exists( 'update_field' ) ) {
				update_field( $prop, $value, 'option' );
			} else {
				update_post_meta( $this->get_id(), 'setting_' . $prop, $value );
			}
		}
	}

	public function update_prop( $prop, $value )
	{
		$this->set_prop( $prop, $value );
		$this->save_meta( $prop, $value );
	}

}
