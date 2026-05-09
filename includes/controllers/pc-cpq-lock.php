<?php

namespace PC_CPQ\Controllers;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PC_CPQ_Lock extends MVC_Controller_Registry
{
	protected function __construct()
	{
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_dependencies' ), 10000 );
		add_filter( 'heartbeat_received', array( $this, 'handle_heartbeat' ), 10, 2 );
		add_action( 'wp_ajax_pc_cpq_release_post_lock', array( $this, 'handle_release_post_lock' ) );
	}

	public function enqueue_editor_dependencies()
	{
		if ( ! is_user_logged_in() || ! PC_CPQ()->Post_Lock()->is_editor_request() ) {
			return;
		}

		wp_enqueue_script( 'heartbeat' );
	}

	public function handle_heartbeat( $response, $data )
	{
		if ( empty( $data['pcCpqPostLock'] ) || ! is_array( $data['pcCpqPostLock'] ) ) {
			return $response;
		}

		$post_id = absint( $data['pcCpqPostLock']['postId'] ?? 0 );
		$nonce = sanitize_text_field( $data['pcCpqPostLock']['nonce'] ?? '' );
		$label = sanitize_text_field( $data['pcCpqPostLock']['label'] ?? 'record' );

		$payload = PC_CPQ()->Post_Lock()->maybe_refresh_lock( $post_id, $nonce, $label );
		if ( null === $payload ) {
			return $response;
		}

		if ( empty( $response['pcCpqPostLock'] ) || ! is_array( $response['pcCpqPostLock'] ) ) {
			$response['pcCpqPostLock'] = array();
		}

		$response['pcCpqPostLock'][ $post_id ] = $payload;

		return $response;
	}

	public function handle_release_post_lock()
	{
		if ( ! is_user_logged_in() ) {
			wp_die( 0 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );

		wp_die( PC_CPQ()->Post_Lock()->release_current_lock( $post_id, $nonce ) ? 1 : 0 );
	}
}

PC_CPQ_Lock::instance();
