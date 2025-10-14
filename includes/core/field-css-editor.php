<?php

if ( ! class_exists( 'acf_field_css_editor' ) ):

	class acf_field_css_editor extends acf_field
	{

		function __construct()
		{
			$this->name = 'css_editor';
			$this->label = __( 'CSS Editor', 'acf' );
			$this->category = 'content';
			$this->defaults = [];

			parent::__construct();
		}

		function render_field( $field )
		{
			$id = esc_attr( $field['id'] );
			$name = esc_attr( $field['name'] );
			$value = esc_textarea( $field['value'] );

			echo "<textarea id='{$id}' name='{$name}' style='width:100%;height:auto'>{$value}</textarea>";
			echo "<style>.CodeMirror{height:auto;}</style>";
			echo "
        <script>
        jQuery(function($) {
            var editor = CodeMirror.fromTextArea(document.getElementById('{$id}'), {
                mode: 'css',
                lineNumbers: true,
				indentUnit: 4,
				viewportMargin: Infinity,
                theme: 'monokai'
            });
        });
        </script>";
		}

		function input_admin_enqueue_scripts()
		{
			wp_enqueue_script( 'codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js', [], null, true );
			wp_enqueue_script( 'codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/css/css.min.js', [ 'codemirror' ], null, true );
			wp_enqueue_style( 'codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css' );
			wp_enqueue_style( 'codemirror-theme', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/monokai.min.css' );
		}

	}

	

endif;