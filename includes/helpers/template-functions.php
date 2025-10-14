<?php

use \PC_CPQ\Models\Input;

if ( ! function_exists( 'pc_cpq_get_input_html' ) ) {

	function pc_cpq_get_input_html( $field_name, $Model, $i = null, $alias = null )
	{
		$Input = new Input( $field_name, $Model, $i, $alias );
		$file = PC_CPQ()->plugin_path() . '/includes/views/manage/fields/' . $Input->get_type() . '.php';
		
		if ( file_exists( $file ) ) {
			ob_start();
			include $file;
			return ob_get_clean();
		}
		return '';
	}
}

if ( ! function_exists( 'pc_cpq_slug_to_label' ) ) {

	function pc_cpq_slug_to_label( $slug )
	{
		return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
	}
}