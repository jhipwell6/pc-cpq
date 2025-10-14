<?php

namespace PC_CPQ\Models;

use \WP_MVC\Models\Abstracts\Repeater_Model;
use \NumberFormatter;
use \PC_CPQ\Helpers\Geometry;
use \PC_CPQ\Helpers\Constants;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Part extends Repeater_Model
{
	protected $hidden = array(
		'Process_Class',
		'raw_processes',
		'Routing_Class',
		'raw_routing',
		'raw_quantities',
		'Quantity_Class',
		'Pricing_Class',
		'Plating_Tool_Class',
		'Pricing_Model',
		'Plating_Tool',
		'raw_pricing',
		'Part_Pricing_Inputs',
	);
	protected $file_name;
	private $file;
	private $area; // computed
	protected $area_computed;
	protected $area_override;
	private $volume;
	protected $volume_computed;
	protected $volume_override;
	private $d_x;
	protected $d_x_computed;
	protected $d_x_override;
	private $d_y;
	protected $d_y_computed;
	protected $d_y_override;
	private $d_z;
	protected $d_z_computed;
	protected $d_z_override;
	protected $base_metal;
	protected $drawing_number;
	protected $revision_number;
	protected $part_number;
	protected $plating_line;
	protected $plating_method;
	protected $tool;
	protected $plating_tool_barrel;
	protected $plating_tool_rack;
	protected $total_operation_time;
	protected static $Pricing_Input_Class = 'PC_CPQ\Models\Part_Pricing_Inputs';
	private $raw_pricing;
	protected $Pricing;
	protected static $Process_Class = 'PC_CPQ\Models\Part_Process';
	private $raw_processes;
	protected $Processes;
	protected static $Routing_Class = 'PC_CPQ\Models\Part_Routing';
	private $raw_routing;
	protected $Routing;
	protected $Operations;
	protected static $Pricing_Class = 'PC_CPQ\Models\Part_Pricing';
	protected $Pricing_Model;
	protected static $Quantity_Class = 'PC_CPQ\Models\Part_Quantity';
	private $raw_quantities;
	protected $Quantities;
	// computed
	protected $metal_density;
	protected $prep_cycle;
	protected $load_weight;
	private $weight;
	private $weight_computed;
	protected $weight_override;
	private $pieces_per_load;
	private $pieces_per_load_computed;
	protected $pieces_per_load_override;
	private $thruput_capacity;
	private $thruput_capacity_computed;
	protected $thruput_capacity_override;
	protected $process_time;
	protected $metal_consumption;
	protected $metal_factors;
	protected $material_cost;
	protected $pieces_per_hour;
	protected $total_area;
	protected static $Plating_Tool_Class = 'PC_CPQ\Models\Part_Plating_Tool';
	protected $Plating_Tool;
	private $min_lot_charge;

	/*
	 * Getters
	 */

	public function get_Lead()
	{
		return $this->get_post_model();
	}

	public function get_file_name()
	{
		return $this->get_prop( 'file_name' );
	}

	public function get_file()
	{
		if ( null === $this->file ) {
			if ( $this->has_file() && ! empty( $this->get_Lead()->get_files() ) ) {
				$this->file = false;
				foreach ( $this->get_Lead()->get_files() as $path ) {
					if ( str_ends_with( sanitize_title( $path ), sanitize_title( $this->get_file_name() ) ) ) {
						$this->file = $path;
						break;
					}
				}
			}
		}
		return $this->file;
	}

	public function get_area( $context = 'raw' )
	{
		if ( null === $this->area ) {
			$raw_area = $this->get_area_override() ? $this->get_area_override() : $this->get_area_computed();
			if ( $context != 'raw' ) {
				$this->area = $this->round( $raw_area ) . ' ft&sup2;';
			} else {
				$this->area = floatval( $raw_area );
			}
		}
		return ! $this->area || $this->area == floatval( 0 ) ? floatval( 1 ) : $this->area;
	}

	public function get_area_computed()
	{
		return $this->get_prop( 'area_computed' );
	}

	public function get_area_override()
	{
		return $this->get_prop( 'area_override' );
	}

	public function get_total_area( $context = 'raw' )
	{
		if ( null === $this->total_area ) {
			$this->total_area = floor( $this->get_pieces_per_load() ) * $this->get_area();
		}
		return $context != 'raw' ? $this->round( $this->total_area ) . ' ft&sup2;' : $this->total_area;
	}

	public function get_volume( $context = 'raw' )
	{
		if ( null === $this->volume ) {
			$raw_volume = $this->get_volume_override() ? $this->get_volume_override() : $this->get_volume_computed();
			if ( $context != 'raw' ) {
				$this->volume = $this->round( $raw_volume ) . ' in&sup3;';
			} else {
				$this->volume = floatval( $raw_volume );
			}
		}
		return ! $this->volume || $this->volume == floatval( 0 ) ? floatval( 1 ) : $this->volume;
	}

	public function get_volume_computed()
	{
		return $this->get_prop( 'volume_computed' );
	}

	public function get_volume_override()
	{
		return $this->get_prop( 'volume_override' );
	}

	public function get_d_x( $context = 'raw' )
	{
		if ( null === $this->d_x ) {
			$raw_d_x = $this->get_d_x_override() ? $this->get_d_x_override() : $this->get_d_x_computed();
			$this->d_x = $context != 'raw' ? floatval( $this->round( $raw_d_x ) ) : floatval( $raw_d_x );
		}
		return $this->d_x;
	}

	public function get_d_x_computed()
	{
		return $this->get_prop( 'd_x_computed' );
	}

	public function get_d_x_override()
	{
		return $this->get_prop( 'd_x_override' );
	}

	public function get_d_y( $context = 'raw' )
	{
		if ( null === $this->d_y ) {
			$raw_d_y = $this->get_d_y_override() ? $this->get_d_y_override() : $this->get_d_y_computed();
			$this->d_y = $context != 'raw' ? floatval( $this->round( $raw_d_y ) ) : floatval( $raw_d_y );
		}
		return $this->d_y;
	}

	public function get_d_y_computed()
	{
		return $this->get_prop( 'd_y_computed' );
	}

	public function get_d_y_override()
	{
		return $this->get_prop( 'd_y_override' );
	}

	public function get_d_z( $context = 'raw' )
	{
		if ( null === $this->d_z ) {
			$raw_d_z = $this->get_d_z_override() ? $this->get_d_z_override() : $this->get_d_z_computed();
			$this->d_z = $context != 'raw' ? floatval( $this->round( $raw_d_z ) ) : floatval( $raw_d_z );
		}
		return $this->d_z;
	}

	public function get_d_z_computed()
	{
		return $this->get_prop( 'd_z_computed' );
	}

	public function get_d_z_override()
	{
		return $this->get_prop( 'd_z_override' );
	}

	public function get_base_metal()
	{
		return $this->get_prop( 'base_metal' );
	}

	public function get_drawing_number()
	{
		return $this->get_prop( 'drawing_number' );
	}

	public function get_revision_number()
	{
		return $this->get_prop( 'revision_number' );
	}

	public function get_part_number()
	{
		return $this->get_prop( 'part_number' );
	}

	public function get_plating_line()
	{
		return $this->get_prop( 'plating_line' );
	}

	public function get_plating_method()
	{
		return $this->get_prop( 'plating_method' );
	}

	public function get_tool()
	{
		if ( null === $this->tool ) {
			$plating_method = strtolower( $this->get_plating_method() );
			$getter = "get_plating_tool_{$plating_method}";
			if ( is_callable( array( $this, $getter ) ) ) {
				$this->tool = $this->{$getter}();
			}
		}
		return $this->tool;
	}

	public function get_plating_tool_barrel()
	{
		return $this->get_prop( 'plating_tool_barrel' );
	}

	public function get_plating_tool_rack()
	{
		return $this->get_prop( 'plating_tool_rack' );
	}

	public function get_total_operation_time()
	{
		return $this->get_prop( 'total_operation_time' );
	}

	public function get_raw_pricing()
	{
		if ( null === $this->raw_pricing ) {
			$pricing = $this->get_meta( 'pricing' );
			$this->raw_pricing = isset( $pricing[0] ) ? array_merge( ...array_values( $pricing ) ) : $pricing;
		}
		return $this->raw_pricing;
	}

	public function get_Pricing( $force_update = false )
	{
		if ( null === $this->Pricing || $force_update ) {
			$this->Pricing = new self::$Pricing_Input_Class( $this->get_raw_pricing() );
		}
		return $this->Pricing;
	}

	public function get_raw_processes()
	{
		if ( null === $this->raw_processes ) {
			$this->raw_processes = $this->get_meta( 'processes' );
		}
		return $this->raw_processes;
	}

	public function get_Processes( $context = 'raw', $force_update = false )
	{
		if ( null === $this->Processes || $force_update ) {
			$this->Processes = [];
			if ( ! empty( $this->get_raw_processes() ) ) {
				foreach ( $this->get_raw_processes() as $index => $raw_process ) {
					$this->add_Process( $index, $raw_process, false );
				}
			}
		}
		return $context != 'raw' ? $this->to_formatted_processes( $this->Processes ) : $this->Processes;
	}

	public function get_raw_routing()
	{
		if ( null === $this->raw_routing ) {
			$this->raw_routing = $this->get_meta( 'routing' );
		}
		return $this->raw_routing;
	}

	public function get_Routing( $force_update = false )
	{
		if ( null === $this->Routing || $force_update ) {
			$this->Routing = array();
			if ( ! empty( $this->get_raw_routing() ) ) {
				foreach ( $this->get_raw_routing() as $index => $raw_operation ) {
					$this->add_Operation( $index, $raw_operation, false );
				}
			}
		}
		return $this->Routing;
	}

	public function get_Operations()
	{
		// uncached
		$this->Operations = [];
		if ( $this->get_base_metal() ) {
			$this->Operations[] = new self::$Routing_Class( 0, [ 'type' => 'Prep', 'metal' => $this->get_base_metal() ] );
		}

		if ( ! empty( $this->get_Processes() ) ) {
			$i = 1;
			foreach ( $this->get_Processes() as $Process ) {
				$this->Operations[] = new self::$Routing_Class( $i, [ 'type' => 'Plating', 'metal' => $Process->get_metal() ] );
				$i ++;
			}
		}

		return $this->Operations;
	}

	public function get_Pricing_Model()
	{
		if ( null === $this->Pricing_Model ) {
			$Pricing = array();
			if ( $this->get_Quantities() ) {
				foreach ( $this->get_Quantities() as $Quantity ) {
					$Pricing[] = new self::$Pricing_Class( $Quantity->get_break_point(), $this, $this->get_Lead() );
				}
			}
			$this->Pricing_Model = $Pricing;
		}
		return $this->Pricing_Model;
	}

	public function get_raw_quantities()
	{
		if ( null === $this->raw_quantities ) {
			$this->raw_quantities = $this->get_meta( 'quantities' );
		}
		return $this->raw_quantities;
	}

	public function get_Quantities( $force_update = false )
	{
		if ( null === $this->Quantities || $force_update ) {
			$this->Quantities = array();
			if ( ! empty( $this->get_raw_quantities() ) ) {
				foreach ( $this->get_raw_quantities() as $index => $raw_quantity ) {
					$this->add_Quantity( $index, $raw_quantity, false );
				}
			}
		}
		return $this->Quantities;
	}

	public function get_min_lot_charge()
	{
		if ( null === $this->min_lot_charge ) {
			$min_lot_charge = 0;
			if ( ! empty( $this->get_Processes() ) ) {
				$charges = array_map( function ( $Process ) {
					return $Process->get_min_lot_charge();
				}, $this->get_Processes() );
				$min_lot_charge = max( $charges );
			}
			$this->min_lot_charge = $min_lot_charge;
		}
		return $this->min_lot_charge;
	}

	/*
	 * Getters (computed)
	 */

	public function to_array( $exclude = array() )
	{
		$exclusions = wp_parse_args( $exclude, $this->get_hidden() );
		$vars = get_object_vars( $this );
		$vars = array_diff_key( $vars, array_flip( $exclusions ) );
		$this->fill_raw_properties( $vars );
		array_walk( $vars, array( $this, 'deep_objects_to_array' ) );
		return $vars;
	}

	public function fill_raw_properties( &$vars )
	{
		$quantities = array_map( function ( $Quantity ) {
			return $Quantity->to_array();
		}, $this->get_Quantities() );

		$vars['quantities'] = $quantities;

		$processes = array_map( function ( $Processes ) {
			return $Processes->to_array();
		}, $this->get_Processes() );

		$vars['processes'] = $processes;

		$routing = array_map( function ( $Route ) {
			return $Route->to_array();
		}, $this->get_Routing() );

		$vars['routing'] = $routing;

		$vars['pricing'] = $this->get_Pricing()->to_array();
	}

	public function get_clone_data()
	{
		return $this->get_raw_data();
	}

	public function has_file()
	{
		return (bool) $this->get_file_name();
	}

	public function get_metal_density( $context = 'raw' )
	{
		if ( null === $this->metal_density ) {
			$this->metal_density = Constants::get_metal_value( $this->get_base_metal(), 'density' );
		}
		return $context != 'raw' ? $this->round( $this->metal_density ) . ' #/in&sup3;' : $this->metal_density;
	}

	public function get_prep_cycle()
	{
		if ( null === $this->prep_cycle ) {
			$this->prep_cycle = Constants::get_metal_value( $this->get_base_metal(), 'prep_cycle' );
		}
		return $this->prep_cycle;
	}

	public function get_weight( $context = 'raw' )
	{
		if ( null === $this->weight ) {
			$this->weight = $this->get_weight_override() ? $this->get_weight_override() : $this->get_weight_computed();
		}
		return $context != 'raw' ? $this->round( $this->weight ) . ' #' : $this->weight;
	}

	public function get_weight_computed()
	{
		if ( null === $this->weight_computed ) {
			$this->weight_computed = Geometry::calculate_weight( $this->get_volume(), $this->get_metal_density() );
			if ( $this->weight_computed == 0 ) {
				$this->weight_computed = 1;
			}
		}
		return $this->weight_computed;
	}

	public function get_weight_override()
	{
		return $this->get_prop( 'weight_override' );
	}

	public function get_load_weight( $context = 'raw' )
	{
		if ( null === $this->load_weight ) {
			$this->load_weight = floor( $this->get_pieces_per_load() ) * $this->get_weight();
		}
		return $context != 'raw' ? $this->round( $this->load_weight ) . ' #' : $this->load_weight;
	}

	public function get_pieces_per_load( $context = 'raw' )
	{
		if ( null === $this->pieces_per_load ) {
			$this->pieces_per_load = $this->get_pieces_per_load_override() ? $this->get_pieces_per_load_override() : $this->get_pieces_per_load_computed();
		}
		return $context != 'raw' ? $this->round( $this->pieces_per_load ) : $this->pieces_per_load;
	}

	public function get_pieces_per_load_computed( $context = 'raw' )
	{
		if ( null === $this->pieces_per_load_computed ) {
			$pieces = $this->get_Plating_Tool()->get_actual_piece_count();
			$this->pieces_per_load_computed = $pieces > 0 ? $pieces : 1;
		}
		return $context != 'raw' ? $this->round( $this->pieces_per_load_computed ) : $this->pieces_per_load_computed;
	}

	public function get_pieces_per_load_override()
	{
		return $this->get_prop( 'pieces_per_load_override' );
	}

	public function get_thruput_capacity( $context = 'raw' )
	{
		if ( null === $this->thruput_capacity ) {
			$this->thruput_capacity = $this->get_thruput_capacity_override() ? $this->get_thruput_capacity_override() : $this->get_thruput_capacity_computed();
		}
		return $context != 'raw' ? $this->round( $this->thruput_capacity ) : $this->thruput_capacity;
	}

	public function get_thruput_capacity_computed()
	{
		if ( null === $this->thruput_capacity_computed ) {
			$this->thruput_capacity_computed = Constants::get_line_value( $this->get_plating_line(), 'plate_cells' );
			if ( $this->thruput_capacity_computed == 0 ) {
				$this->thruput_capacity_computed = 1;
			}
		}
		return $this->thruput_capacity_computed;
	}

	public function get_thruput_capacity_override()
	{
		return $this->get_prop( 'thruput_capacity_override' );
	}

	public function get_process_time( $context = 'raw' )
	{
		if ( null === $this->process_time ) {
			$process_time = 0;
			if ( $this->get_Processes() ) {
				foreach ( $this->get_Processes() as $Process ) {
					$process_time = $process_time + $Process->get_time();
				}
				$process_time = $process_time + $this->get_prep_cycle();
				$process_time = ceil( $process_time / 60 );
			}
			$this->process_time = $process_time > 0 ? $process_time : 0.1;
		}
		return $context != 'raw' ? $this->round( $this->process_time ) . ' hrs' : $this->process_time;
	}

	public function get_metal_consumption( $context = 'raw' )
	{
		// metal consumed per unit
		if ( null === $this->metal_consumption ) {
			$metal_consumption = 0;
			if ( $this->get_Processes() ) {
				foreach ( $this->get_Processes() as $Process ) {
					$metal_consumption += ( $this->get_area() * 144 ) * ( $Process->get_average_thickness() / 1000000 ) * $Process->get_metal_density();
				}
			}
			$this->metal_consumption = $metal_consumption;
		}
		return $context != 'raw' ? $this->round( $this->metal_consumption ) : $this->metal_consumption;
	}

	public function get_metal_factors()
	{
		if ( null === $this->metal_factors ) {
			$metal_factors = [];
			if ( $this->get_Processes() ) {
				foreach ( $this->get_Processes() as $Process ) {
					if ( $Process->is_precious_metal() ) {
						$metal_factors[$Process->get_metal()] += ( $this->get_area() * 144 ) * ( $Process->get_average_thickness() / 1000000 ) * $Process->get_metal_density() * $this->get_Pricing()->get_metal_adder();
					}
				}
			}
			$this->metal_factors = $metal_factors;
		}
		return $this->metal_factors;
	}

	public function has_metal_factors()
	{
		return ! empty( $this->get_metal_factors() );
	}

	public function get_material_cost( $context = 'raw' )
	{
		if ( null === $this->material_cost ) {
			$material_cost = 0;
			if ( $this->get_Processes() ) {
				foreach ( $this->get_Processes() as $Process ) {
					$material_cost += ( $this->get_area() * 144 ) * ( $Process->get_average_thickness() / 1000000 ) * $Process->get_metal_density() * $Process->get_metal_cost() * $this->get_Pricing()->get_metal_adder();
				}
			}
			$this->material_cost = $material_cost;
		}
		return $context != 'raw' ? $this->to_currency( $this->material_cost, 4 ) : $this->material_cost;
	}

	public function get_pieces_per_hour( $context = 'raw' )
	{
		if ( null === $this->pieces_per_hour ) {
			$this->pieces_per_hour = ( $this->get_pieces_per_load() / $this->get_process_time() ) * $this->get_thruput_capacity();
		}
		return $context != 'raw' ? $this->round( $this->pieces_per_hour ) : $this->pieces_per_hour;
	}

	public function get_Plating_Tool()
	{
		if ( null === $this->Plating_Tool ) {
			$tool_type = stripos( $this->get_plating_method(), 'barrel' ) !== false ? Constants::$barrels : Constants::$racks;
			$tool = Constants::get_row( $this->get_tool(), $tool_type );
			$this->Plating_Tool = new self::$Plating_Tool_Class( $this->get_plating_method(), $tool, $this );
		}
		return $this->Plating_Tool;
	}

	/*
	 * Savers
	 */

	public function set_raw_processes( $value )
	{
		$this->raw_processes = $value;
		return $this->raw_processes;
	}

	public function save_raw_processes_meta( $value )
	{
		$index = $this->get_id() + 1;
		$result = update_sub_field( [ 'part_data', $index, 'processes' ], $value, $this->get_Lead()->get_id() );
		$this->refresh_Processes();
		return $result;
	}

	public function save_Processes()
	{
		$processes = array_map( function ( $Process ) {
			return $Process->to_array();
		}, $this->get_Processes() );

		$this->set_raw_processes( $processes );
		$this->save_raw_processes_meta( $processes );
	}

	public function set_raw_routing( $value )
	{
		$this->raw_routing = $value;
		return $this->raw_routing;
	}

	public function save_raw_routing_meta( $value )
	{
		$index = $this->get_id() + 1;
		$result = update_sub_field( [ 'part_data', $index, 'routing' ], $value, $this->get_Lead()->get_id() );
		$this->refresh_Operations();
		return $result;
	}

	public function save_Operations()
	{
		$operations = array_map( function ( $Operation ) {
			return $Operation->to_array();
		}, $this->get_Routing() );

		$this->set_raw_routing( $operations );
		$this->save_raw_routing_meta( $operations );
	}

	public function set_raw_quantities( $value )
	{
		$this->raw_quantities = $value;
		return $this->raw_quantities;
	}

	public function save_raw_quantities_meta( $value )
	{
		$index = $this->get_id() + 1;
		$result = update_sub_field( [ 'part_data', $index, 'quantities' ], $value, $this->get_Lead()->get_id() );
		$this->refresh_Quantities();
		return $result;
	}

	public function save_Quantities()
	{
		$quantities = array_map( function ( $Quantity ) {
			return $Quantity->to_array();
		}, $this->get_Quantities() );

		$this->set_raw_quantities( $quantities );
		$this->save_raw_quantities_meta( $quantities );
	}

	public function set_raw_pricing( $value )
	{
		$this->raw_pricing = isset( $value[0] ) ? array_first( $value ) : $value;
		return $this->raw_pricing;
	}

	public function save_raw_pricing_meta( $value )
	{
		$index = $this->get_id() + 1;
		$result = update_sub_field( [ 'part_data', $index, 'pricing' ], $value, $this->get_Lead()->get_id() );
		$this->refresh_Pricing();
		return $result;
	}

	public function save_Pricing()
	{
		$pricing = $this->get_Pricing()->to_array();
		$this->set_raw_pricing( $pricing );
		$this->save_raw_pricing_meta( $pricing );
	}

	/*
	 * Helpers
	 */

	public function get_Processes_count()
	{
		return count( $this->get_Processes() );
	}

	public function add_Process( $index = null, $raw_process = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Processes_count();
		}
		$this->Processes[] = new self::$Process_Class( $index, $raw_process, $this->get_Lead() );

		if ( $save ) {
			$this->save_Processes();
		}
	}

	public function delete_Process( $index )
	{
		$Processes = $this->get_Processes();
		if ( isset( $Processes[$index] ) ) {
			unset( $Processes[$index] );
		}
		$this->Processes = array_values( $Processes );

		$this->save_Processes();
	}

	public function refresh_Processes()
	{
		$this->get_Processes( 'raw', true );
	}

	public function get_Operations_count()
	{
		return count( $this->get_Routing() );
	}

	public function add_Operation( $index = null, $raw_operation = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Operations_count();
		}
		$this->Routing[] = new self::$Routing_Class( $index, $raw_operation, $this->get_Lead() );

		if ( $save ) {
			$this->save_Operations();
		}
	}

	public function delete_Operation( $index )
	{
		$Operations = $this->get_Routing();
		if ( isset( $Operations[$index] ) ) {
			unset( $Operations[$index] );
		}
		$this->Routing = array_values( $Operations );

		$this->save_Operations();
	}

	public function refresh_Operations()
	{
		$this->get_Routing( true );
	}

	public function get_Quantities_count()
	{
		return count( $this->get_Quantities() );
	}

	public function add_Quantity( $index = null, $raw_quantity = array(), $save = true )
	{
		if ( null === $index ) {
			$index = $this->get_Quantities_count();
		}
		$this->Quantities[] = new self::$Quantity_Class( $index, $raw_quantity, $this );

		if ( $save ) {
			$this->save_Quantities();
		}
	}

	public function delete_Quantity( $index )
	{
		$Quantities = $this->get_Quantities();
		if ( isset( $Quantities[$index] ) ) {
			unset( $Quantities[$index] );
		}
		$this->Quantities = array_values( $Quantities );

		$this->save_Quantities();
	}

	public function refresh_Quantities()
	{
		$this->get_Quantities( true );
	}

	public function refresh_Pricing()
	{
		$this->get_Pricing( true );
	}

	private function round( $number )
	{
		return round( floatval( $number ), 4 );
	}

	private function to_currency( $value, $digits = 2 )
	{
		$formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
		$formatter->setAttribute( NumberFormatter::FRACTION_DIGITS, $digits );
		return $formatter->formatCurrency( $value, 'USD' );
	}

	private function to_formatted_processes( $Processes )
	{
		$output = '';
		if ( null != $Processes && ! empty( $Processes ) ) {
			$i = 1;
			foreach ( $Processes as $Process ) {
				$output .= sprintf( "%s) %s", $i, $Process->get_description() );
				$i ++;
			}
		}
		return $output;
	}

	public function set_default_pricing()
	{
		$this->raw_pricing = [
			'margin' => PC_CPQ()->Settings()->get_default_margin(),
			'eff' => PC_CPQ()->Settings()->get_default_eff(),
			'people' => PC_CPQ()->Settings()->get_default_people(),
			'eau' => PC_CPQ()->Settings()->get_default_eau(),
			'shift' => PC_CPQ()->Settings()->get_default_shift(),
			'break_in' => PC_CPQ()->Settings()->get_default_break_in(),
			'metal_adder' => PC_CPQ()->Settings()->get_default_metal_adder(),
			'price_unit' => '',
		];
		$this->get_Pricing( true );
	}

}
