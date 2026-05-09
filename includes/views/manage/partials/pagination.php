<?php

$pagination_args = array_filter(
	array(
		'q' => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
		'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
	),
	function ( $value ) {
		return $value !== '';
	}
);

echo paginate_links( array(
	'base' => PC_CPQ()->Site()->get_current_endpoint_url() . '%_%',
	'total' => $max_pages,
	'current' => max( 1, get_query_var( 'offset' ) ),
	'format' => '?offset=%#%',
	'show_all' => false,
	'type' => 'list',
	'end_size' => 2,
	'mid_size' => 1,
	'add_args' => $pagination_args,
	'add_fragment' => '',
) );
