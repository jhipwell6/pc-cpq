<?php

if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group( [
		'key' => 'group_site_code_editor',
		'title' => 'Site Code Editor',
		'fields' => [
			[
				'key' => 'field_custom_site_head',
				'label' => 'Custom HTML (head)',
				'name' => 'custom_site_head',
				'type' => 'html_editor', // our custom field type
			],
			[
				'key' => 'field_custom_site_body',
				'label' => 'Custom HTML (body)',
				'name' => 'custom_site_body',
				'type' => 'html_editor', // our custom field type
			],
			[
				'key' => 'field_custom_site_css',
				'label' => 'Custom CSS',
				'name' => 'custom_site_css',
				'type' => 'css_editor', // our custom field type
			]
		],
		'location' => [
			[
				[
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'site-code',
				]
			]
		],
	] );
}