<?php

namespace PC_CPQ\Controllers;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Controllers\PC_CPQ_Custom_Fields;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Template extends MVC_Controller_Registry
{

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_action( 'send_headers', function () {
			header( 'Access-Control-Allow-Credentials: true' );
		} );

		add_action( 'admin_init', array( $this, 'maybe_redirect_wp_admin_access' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_manage_assets' ), 100 );
		add_filter( 'script_loader_tag', array( $this, 'load_js_as_module' ), 10, 3 );
		add_action( 'pc_cpq_body_open', array( $this, 'render' ), 10 );
		add_action( 'pc_cpq_body_close', array( $this, 'load_js_templates' ), 10 );
		add_filter( 'show_admin_bar', '__return_false' );
		add_filter( 'body_class', array( $this, 'add_pace_class' ), 10, 1 );
		add_action( 'wp_head', array( $this, 'add_pace_fetch_polyfill' ), 10 );
		add_action( 'wp_head', array( $this, 'set_custom_site_head' ), 9 );
		add_action( 'wp_head', array( $this, 'set_custom_site_css' ), 10 );
		add_action( 'pc_cpq_body_open', array( $this, 'set_custom_site_body' ), 9 );
		add_filter( 'upload_mimes', [ $this, 'add_mime_types' ], 10, 1 );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_file_type_by_ext' ], 10, 4 );
	}

	public function maybe_redirect_wp_admin_access()
	{
		PC_CPQ()->User()->maybe_redirect_unauthorized_wp_admin_access();
	}

	public function render()
	{
		$Site = PC_CPQ()->Site();
		if ( $Site->is_manage() && is_user_logged_in() ) {
			PC_CPQ()->User()->maybe_redirect_unauthorized_manage_access();
		}

		$data = array(
			'Site' => $Site,
		);
		echo PC_CPQ()->Site()->is_manage() ? PC_CPQ()->view( 'manage/index', $data ) : PC_CPQ()->view( 'index', $data );
	}

	/**
	 * Enqueue frontend assets
	 * @return void
	 */
	public function enqueue_assets()
	{
		if ( ! PC_CPQ()->Site()->is_manage() ) {
			$intl_tel_input_version = '29.2.2';
			$frontend_asset_paths = [
				'/assets/js/pc-cpq-frontend.js',
				'/assets/js/pc-cpq-partmodel.js',
				'/assets/js/pc-cpq-helpers.js',
			];
			$script_version = implode( '-', array_map( function ( $path ) {
					return filemtime( PC_CPQ()->plugin_path() . $path );
				}, $frontend_asset_paths ) );
			wp_enqueue_style( 'bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/css/bootstrap.min.css' );
			wp_enqueue_style( PC_CPQ_DOMAIN . '-styles', PC_CPQ()->plugin_url() . '/assets/css/pc-cpq-frontend.css' );
			wp_enqueue_style(
				'intl-tel-input',
				'https://cdn.jsdelivr.net/npm/intl-tel-input@' . $intl_tel_input_version . '/dist/css/intlTelInput.min.css',
				[],
				$intl_tel_input_version
			);
			wp_enqueue_script( 'bootstrap-bundle', PC_CPQ()->plugin_url() . '/assets/vendor/js/bootstrap.bundle.js', [ 'jquery' ], '', true );
			wp_enqueue_script(
				'intl-tel-input',
				'https://cdn.jsdelivr.net/npm/intl-tel-input@' . $intl_tel_input_version . '/dist/js/intlTelInputWithUtils.min.js',
				[],
				$intl_tel_input_version,
				true
			);
			wp_register_script( PC_CPQ_DOMAIN . '-scripts', PC_CPQ()->plugin_url() . '/assets/js/pc-cpq-frontend.js', [ 'jquery', 'intl-tel-input' ], $script_version, true );

			$config = array();
			$config['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$config['parts'] = PC_CPQ_Custom_Fields::parts_config();
			$config['scriptVersion'] = $script_version;
			$config['stpProcessingNonce'] = wp_create_nonce( 'pc_cpq_process_step_upload' );
			$config['intlTelInput'] = array(
				'scriptUrl' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@' . $intl_tel_input_version . '/dist/js/intlTelInputWithUtils.min.js',
			);

			wp_localize_script( PC_CPQ_DOMAIN . '-scripts', 'PC_CPQ_Config', $config );
			wp_enqueue_script( PC_CPQ_DOMAIN . '-scripts' );
		}
	}

	/**
	 * Enqueue frontend manage assets
	 * @return void
	 */
	public function enqueue_manage_assets()
	{
		if ( PC_CPQ()->Site()->is_manage() ) {
			wp_enqueue_style( 'bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/css/bootstrap.min.css' );
			wp_enqueue_style( 'fontawesome', PC_CPQ()->plugin_url() . '/assets/vendor/css/all.min.css' );
			wp_enqueue_style( 'select2', PC_CPQ()->plugin_url() . '/assets/vendor/css/select2.min.css' );
			wp_enqueue_style( 'select2-bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/css/select2-bootstrap4.min.css' );
			wp_enqueue_style( 'tempusdominus-bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/css/tempusdominus-bootstrap-4.min.css' );
			wp_enqueue_style( 'shepherd', '//cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css' );
			wp_enqueue_style( PC_CPQ_DOMAIN . '-manage', PC_CPQ()->plugin_url() . '/assets/vendor/css/app.min.css' );
			wp_enqueue_script( 'bootstrap-bundle', PC_CPQ()->plugin_url() . '/assets/vendor/js/bootstrap.bundle.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'bootstrap-custom-file-input', PC_CPQ()->plugin_url() . '/assets/vendor/js/bs-custom-file-input.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'select2-full', PC_CPQ()->plugin_url() . '/assets/vendor/js/select2.full.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'moment', PC_CPQ()->plugin_url() . '/assets/vendor/js/moment.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'tempusdominus-bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/js/tempusdominus-bootstrap-4.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'shepherd', '//cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'sortable', '//cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'chartjs', '//cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', array(), '', true );
			wp_enqueue_script( 'fetch-polyfill', '//unpkg.com/whatwg-fetch@latest/dist/fetch.umd.js', array(), '', true );
			wp_enqueue_script( 'pace', PC_CPQ()->plugin_url() . '/assets/vendor/js/pace.min.js', array(), '', true );
			wp_enqueue_script( PC_CPQ_DOMAIN . '-manage', PC_CPQ()->plugin_url() . '/assets/vendor/js/app.min.js', [ 'jquery' ], '', true );
			wp_enqueue_script( 'three-js', '//cdn.jsdelivr.net/npm/three@0.158.0/build/three.min.js', array(), '', true );
			wp_enqueue_script( 'occt-import-js', PC_CPQ()->plugin_url() . '/assets/vendor/js/occt-import-js.js', array(), '', true );
			wp_enqueue_style( PC_CPQ_DOMAIN . '-manage-styles', PC_CPQ()->plugin_url() . '/assets/css/pc-cpq-manage.css' );
			wp_register_script( PC_CPQ_DOMAIN . '-manage-scripts', PC_CPQ()->plugin_url() . '/assets/js/pc-cpq-manage.js', [ 'jquery', 'chartjs', 'three-js', 'occt-import-js' ], '', true );

			$config = array();
			$config['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$config['operations'] = PC_CPQ()->Settings()->get_operations_config();
			$config['templates'] = PC_CPQ()->Settings()->get_email_templates_config();
			$config['occtWasmUrl'] = PC_CPQ()->plugin_url() . '/assets/vendor/js/occt-import-js.wasm';

			wp_localize_script( PC_CPQ_DOMAIN . '-manage-scripts', 'PC_CPQ_ManageConfig', $config );
			wp_enqueue_script( PC_CPQ_DOMAIN . '-manage-scripts' );
		}
	}

	public function enqueue_admin_assets()
	{
		wp_enqueue_style( PC_CPQ_DOMAIN . '-admin-styles', PC_CPQ()->plugin_url() . '/assets/css/pc-cpq-admin.css' );
		wp_enqueue_script( 'bootstrap', PC_CPQ()->plugin_url() . '/assets/vendor/js/bootstrap.min.js', array(), '', true );
		wp_register_script( PC_CPQ_DOMAIN . '-admin-scripts', PC_CPQ()->plugin_url() . '/assets/js/pc-cpq-admin.js', array(), '', true );

		$config = array();
		$config['ajaxurl'] = admin_url( 'admin-ajax.php' );
		$config['ID'] = isset( $_GET['post'] ) ? $_GET['post'] : null;
		$config['operations'] = PC_CPQ()->Settings()->get_operations_config();
		$config['templates'] = PC_CPQ()->Settings()->get_email_templates_config();

		wp_localize_script( PC_CPQ_DOMAIN . '-admin-scripts', 'PC_CPQ_AdminConfig', $config );
		wp_enqueue_script( PC_CPQ_DOMAIN . '-admin-scripts' );
	}

	public function load_js_templates()
	{
		if ( ! PC_CPQ()->Site()->is_manage() ) {
			echo PC_CPQ()->view( 'partials/js-templates' );
		}
	}

	public function load_js_as_module( $tag, $handle, $src )
	{
		if ( strpos( $handle, PC_CPQ_DOMAIN ) === false ) {
			return $tag;
		}

		$tag = '<script type="module" src="' . esc_url( $src ) . '" id="' . $handle . '"></script>';
		return $tag;
	}

	public function add_pace_class( $classes )
	{
		if ( PC_CPQ()->Site()->is_manage() ) {
			$classes[] = 'pace-loading-bar-primary';
		}
		return $classes;
	}

	public function add_pace_fetch_polyfill()
	{
		if ( PC_CPQ()->Site()->is_manage() ) {
			echo '<script>window.fetch = undefined;</script>';
		}
	}

	public function set_custom_site_head()
	{
		$head = PC_CPQ()->Settings()->get_custom_site_head();
		if ( $head ) {
			echo $head;
		}
	}

	public function set_custom_site_css()
	{
		$css = PC_CPQ()->Settings()->get_custom_site_css();
		if ( $css ) {
			echo "<style id='custom-site-css'>\n" . $css . "\n</style>";
		}
	}

	public function set_custom_site_body()
	{
		$body = PC_CPQ()->Settings()->get_custom_site_body();
		if ( $body ) {
			echo $body;
		}
	}

	public function add_mime_types( $mimes )
	{
		$mimes['stp'] = 'application/step';
		$mimes['step'] = 'application/step';
		return $mimes;
	}

	public function check_file_type_by_ext( $data, $file, $filename, $mimes )
	{
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( in_array( $ext, [ 'stp', 'step' ] ) ) {
			$data['ext'] = $ext;
			$data['type'] = 'application/step';
		}

		return $data;
	}

}

PC_CPQ_Template::instance();
