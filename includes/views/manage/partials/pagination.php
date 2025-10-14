<?php

echo paginate_links( array(
	'base' => PC_CPQ()->Site()->get_current_endpoint_url() . '%_%',
	'total' => $max_pages,
	'current' => max( 1, get_query_var( 'offset' ) ),
	'format' => '?offset=%#%',
	'show_all' => false,
	'type' => 'list',
	'end_size' => 2,
	'mid_size' => 1,
	'add_args' => false,
	'add_fragment' => '',
) );
