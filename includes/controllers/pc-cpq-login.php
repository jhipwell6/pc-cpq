<?php

namespace PC_CPQ\Controllers;

use WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PC_CPQ_Login extends MVC_Controller_Registry
{
	protected function __construct()
	{
		add_action( 'login_init', array( $this, 'maybe_redirect_wp_login_access' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_password_reset' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_login' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_handle_forgot_password' ), 1 );
		add_filter( 'wp_new_user_notification_email', array( $this, 'customize_new_user_notification_email' ), 10, 3 );
		add_filter( 'retrieve_password_message', array( $this, 'customize_lost_password_message' ), 10, 4 );
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

		if ( $Site->is_forgot_password() || $Site->is_reset_password() || ! isset( $_POST['log'], $_POST['pwd'] ) ) {
			return;
		}

		$login = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
		$password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );
		$redirect_to = isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : $Site->get_manage_page_url();

		if ( '' === $login || '' === $password ) {
			$this->redirect_login( array( 'login_error' => 'empty_credentials' ) );
		}

		$user_login = $this->normalize_login_identifier( $login );
		$user = wp_signon(
			array(
				'user_login' => $user_login,
				'user_password' => $password,
				'remember' => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			$this->redirect_login( array( 'login_error' => 'invalid_credentials' ) );
		}

		$target_url = wp_validate_redirect( $redirect_to, $Site->get_manage_page_url() );
		wp_safe_redirect( $target_url ? $target_url : $Site->get_manage_page_url() );
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

	protected function normalize_login_identifier( $login )
	{
		$login = trim( $login );
		if ( is_email( $login ) ) {
			$user = get_user_by( 'email', $login );
			if ( $user instanceof \WP_User && ! empty( $user->user_login ) ) {
				return $user->user_login;
			}
		}

		return $login;
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

PC_CPQ_Login::instance();
