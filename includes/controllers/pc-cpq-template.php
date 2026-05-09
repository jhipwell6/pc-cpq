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
		add_action( 'login_init', array( $this, 'maybe_redirect_wp_login_access' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_password_reset' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_login' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_forgot_password' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_manage_assets' ), 100 );
//		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 100 );
		add_filter( 'script_loader_tag', array( $this, 'load_js_as_module' ), 10, 3 );
		add_filter( 'wp_new_user_notification_email', array( $this, 'customize_new_user_notification_email' ), 10, 3 );
		add_filter( 'retrieve_password_message', array( $this, 'customize_lost_password_message' ), 10, 4 );
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

	public function maybe_redirect_wp_login_access()
	{
		if ( wp_doing_ajax() ) {
			return;
		}

		$manage_url = PC_CPQ()->Site()->get_manage_page_url();
		if ( ! $manage_url ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

		switch ( $action ) {
			case 'lostpassword':
			case 'retrievepassword':
				$redirect_url = PC_CPQ()->Site()->get_login_page_url( array( 'forgot_password' => 1 ) );
				break;
			case 'rp':
			case 'resetpass':
				$redirect_url = PC_CPQ()->Site()->get_login_page_url(
					array(
						'reset_password' => 1,
						'login' => isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : '',
						'key' => isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '',
					)
				);
				break;
			default:
				$redirect_url = $manage_url;
				break;
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	public function maybe_handle_login()
	{
		$Site = PC_CPQ()->Site();
		if ( ! $Site->is_manage_dashboard() || is_user_logged_in() || 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			return;
		}

		if ( $Site->is_forgot_password() || $Site->is_reset_password() ) {
			return;
		}

		if ( ! isset( $_POST['pc_cpq_login_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['pc_cpq_login_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'pc_cpq_login' ) ) {
			$this->redirect_login( array( 'login_error' => 'invalid_credentials' ) );
		}

		$login = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
		$password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );
		$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : $Site->get_manage_page_url();

		if ( '' === $login || '' === $password ) {
			$this->redirect_login( array( 'login_error' => 'empty_credentials' ) );
		}

		$user = wp_signon(
			array(
				'user_login' => $login,
				'user_password' => $password,
				'remember' => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			$this->redirect_login( array( 'login_error' => 'invalid_credentials' ) );
		}

		wp_safe_redirect( $redirect_to ? $redirect_to : $Site->get_manage_page_url() );
		exit;
	}

	public function maybe_handle_forgot_password()
	{
		$Site = PC_CPQ()->Site();
		if ( ! $Site->is_manage_dashboard() || ! $Site->is_forgot_password() || 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			return;
		}

		$nonce = isset( $_POST['pc_cpq_forgot_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pc_cpq_forgot_password_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'pc_cpq_forgot_password' ) ) {
			$this->redirect_forgot_password( array( 'forgot_error' => 'invalid_user' ) );
		}

		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';
		if ( '' === $user_login ) {
			$this->redirect_forgot_password( array( 'forgot_error' => 'empty_user' ) );
		}

		$result = retrieve_password( $user_login );
		if ( is_wp_error( $result ) ) {
			$this->redirect_forgot_password( array( 'forgot_error' => 'invalid_user' ) );
		}

		$this->redirect_forgot_password( array( 'forgot_sent' => 1 ) );
	}

	public function maybe_handle_password_reset()
	{
		$Site = PC_CPQ()->Site();
		if ( ! $Site->is_manage_dashboard() || ! $Site->is_reset_password() ) {
			return;
		}

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			return;
		}

		$nonce = isset( $_POST['pc_cpq_reset_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pc_cpq_reset_password_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'pc_cpq_reset_password' ) ) {
			$this->redirect_reset_password( array( 'reset_error' => 'invalid_key' ) );
		}

		$login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$pass1 = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : '';
		$pass2 = isset( $_POST['pass2'] ) ? (string) wp_unslash( $_POST['pass2'] ) : '';

		if ( '' === $pass1 || '' === $pass2 ) {
			$this->redirect_reset_password(
				array(
					'login' => $login,
					'key' => $key,
					'reset_error' => 'empty_password',
				)
			);
		}

		if ( $pass1 !== $pass2 ) {
			$this->redirect_reset_password(
				array(
					'login' => $login,
					'key' => $key,
					'reset_error' => 'password_mismatch',
				)
			);
		}

		$user = check_password_reset_key( $key, $login );
		if ( is_wp_error( $user ) || ! $user instanceof \WP_User ) {
			$this->redirect_reset_password( array( 'reset_error' => 'invalid_key' ) );
		}

		reset_password( $user, $pass1 );

		wp_safe_redirect(
			add_query_arg(
				array(
					'password_reset' => 'success',
				),
				$Site->get_login_page_url()
			)
		);
		exit;
	}

	public function customize_new_user_notification_email( $email, $user, $blogname )
	{
		if ( empty( $email['message'] ) || ! $user instanceof \WP_User ) {
			return $email;
		}

		if ( ! preg_match_all( '#https?://\S+#', $email['message'], $matches ) ) {
			return $email;
		}

		$reset_url = '';
		$login = '';
		$key = '';

		foreach ( $matches[0] as $candidate_url ) {
			$parsed_url = wp_parse_url( $candidate_url );
			if ( empty( $parsed_url['query'] ) ) {
				continue;
			}

			parse_str( $parsed_url['query'], $query_args );
			if ( ( $query_args['action'] ?? '' ) !== 'rp' ) {
				continue;
			}

			$login = isset( $query_args['login'] ) ? (string) $query_args['login'] : '';
			$key = isset( $query_args['key'] ) ? (string) $query_args['key'] : '';
			break;
		}

		if ( '' === $login || '' === $key ) {
			return $email;
		}

		$reset_url = PC_CPQ()->Site()->get_login_page_url(
			array(
				'reset_password' => 1,
				'login' => $login,
				'key' => $key,
			)
		);

		$email['subject'] = sprintf( '[%s] Set up your workspace password', $blogname );
		$email['message'] = sprintf(
			"Username: %s\r\n\r\nYou're ready to join your workspace.\r\n\r\nSet your password here:\r\n\r\n%s\r\n\r\nAfter that, sign in here:\r\n\r\n%s",
			$user->user_login,
			$reset_url,
			PC_CPQ()->Site()->get_login_page_url()
		);

		return $email;
	}

	public function customize_lost_password_message( $message, $key, $user_login, $user_data )
	{
		$reset_url = PC_CPQ()->Site()->get_login_page_url(
			array(
				'reset_password' => 1,
				'login' => $user_login,
				'key' => $key,
			)
		);

		return sprintf(
			"Someone requested a password reset for your workspace account.\r\n\r\nUsername: %s\r\n\r\nSet a new password here:\r\n\r\n%s",
			$user_login,
			$reset_url
		);
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
			wp_enqueue_script( 'bootstrap-bundle', PC_CPQ()->plugin_url() . '/assets/vendor/js/bootstrap.bundle.js', [ 'jquery' ], '', true );
			wp_register_script( PC_CPQ_DOMAIN . '-scripts', PC_CPQ()->plugin_url() . '/assets/js/pc-cpq-frontend.js', [ 'jquery' ], $script_version, true );

			$config = array();
			$config['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$config['parts'] = PC_CPQ_Custom_Fields::parts_config();
			$config['scriptVersion'] = $script_version;

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

	protected function redirect_reset_password( $args = array() )
	{
		$url = PC_CPQ()->Site()->get_login_page_url(
			array_merge(
				array(
					'reset_password' => 1,
				),
				$args
			)
		);

		wp_safe_redirect( $url );
		exit;
	}

	protected function redirect_login( $args = array() )
	{
		wp_safe_redirect( PC_CPQ()->Site()->get_login_page_url( $args ) );
		exit;
	}

	protected function redirect_forgot_password( $args = array() )
	{
		wp_safe_redirect(
			PC_CPQ()->Site()->get_login_page_url(
				array_merge(
					array(
						'forgot_password' => 1,
					),
					$args
				)
			)
		);
		exit;
	}
}

PC_CPQ_Template::instance();
