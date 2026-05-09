<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Lock
{
	public function is_editor_request()
	{
		return PC_CPQ()->Site()->is_manage_lead() || PC_CPQ()->Site()->is_manage_customer();
	}

	public function get_editor_lock_data( $post_id, $label = 'record' )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		$post = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			return array();
		}

		$current_user_id = get_current_user_id();
		$locked_by = (int) wp_check_post_lock( $post_id );
		$data = array(
			'enabled' => true,
			'postId' => $post_id,
			'postType' => (string) $post->post_type,
			'label' => sanitize_text_field( $label ),
			'nonce' => wp_create_nonce( 'pc_cpq_post_lock_' . $post_id ),
			'locked' => false,
			'lockUserId' => 0,
			'lockUserName' => '',
			'message' => '',
		);

		if ( $locked_by && $locked_by !== $current_user_id ) {
			return $this->build_locked_payload( $data, $locked_by );
		}

		wp_set_post_lock( $post_id );

		return $data;
	}

	public function get_post_lock_status( $post_id, $label = 'record' )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		$post = $post_id ? get_post( $post_id ) : null;

		if ( ! $post ) {
			return array(
				'locked' => false,
				'lockUserId' => 0,
				'lockUserName' => '',
				'message' => '',
				'label' => sanitize_text_field( $label ),
			);
		}

		$locked_by = (int) wp_check_post_lock( $post_id );
		$payload = array(
			'locked' => false,
			'lockUserId' => 0,
			'lockUserName' => '',
			'message' => '',
			'label' => sanitize_text_field( $label ),
		);

		if ( $locked_by && $locked_by !== get_current_user_id() ) {
			$payload = $this->build_locked_payload( $payload, $locked_by );
		}

		return $payload;
	}

	public function redirect_if_locked( $post_id, $redirect_url, $label = 'record' )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		$locked_by = (int) wp_check_post_lock( $post_id );

		if ( ! $locked_by || $locked_by === get_current_user_id() ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'editor_lock_notice' => 1,
				'editor_lock_label' => sanitize_text_field( $label ),
				'editor_lock_user' => $this->get_user_display_name( $locked_by ),
			),
			$redirect_url
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	public function maybe_refresh_lock( $post_id, $nonce, $label = 'record' )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		$nonce = sanitize_text_field( (string) $nonce );

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'pc_cpq_post_lock_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return null;
		}

		$payload = array(
			'locked' => false,
			'lockUserId' => 0,
			'lockUserName' => '',
			'message' => '',
			'label' => sanitize_text_field( $label ),
		);

		$locked_by = (int) wp_check_post_lock( $post_id );
		if ( $locked_by && $locked_by !== get_current_user_id() ) {
			return $this->build_locked_payload( $payload, $locked_by );
		}

		wp_set_post_lock( $post_id );

		return $payload;
	}

	public function release_current_lock( $post_id, $nonce )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		$nonce = sanitize_text_field( (string) $nonce );

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'pc_cpq_post_lock_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		$current_lock = (string) get_post_meta( $post_id, '_edit_lock', true );
		$current_user_id = get_current_user_id();

		if ( $current_lock ) {
			$parts = explode( ':', $current_lock );
			$lock_user_id = isset( $parts[1] ) ? absint( $parts[1] ) : 0;

			if ( $lock_user_id === $current_user_id ) {
				delete_post_meta( $post_id, '_edit_lock' );
				return true;
			}
		}

		return false;
	}

	public function assert_editable( $post_id, $label = 'record' )
	{
		$this->ensure_post_lock_functions();

		$post_id = absint( $post_id );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array(
				'message' => 'You do not have permission to edit this ' . sanitize_text_field( $label ) . '.',
			), 403 );
		}

		$locked_by = (int) wp_check_post_lock( $post_id );
		if ( $locked_by && $locked_by !== get_current_user_id() ) {
			$payload = $this->build_locked_payload( array(
				'label' => sanitize_text_field( $label ),
			), $locked_by );

			wp_send_json_error( array(
				'message' => $payload['message'],
				'locked' => true,
				'lockUserId' => $payload['lockUserId'],
				'lockUserName' => $payload['lockUserName'],
			), 423 );
		}

		wp_set_post_lock( $post_id );
	}

	public function ensure_post_lock_functions()
	{
		if ( function_exists( 'wp_check_post_lock' ) && function_exists( 'wp_set_post_lock' ) ) {
			return;
		}

		$post_functions = ABSPATH . 'wp-admin/includes/post.php';
		if ( file_exists( $post_functions ) ) {
			require_once $post_functions;
		}
	}

	private function build_locked_payload( $payload, $locked_by )
	{
		$payload['locked'] = true;
		$payload['lockUserId'] = absint( $locked_by );
		$payload['lockUserName'] = $this->get_user_display_name( $locked_by );
		$payload['message'] = sprintf(
			'%s is currently editing this %s. Editing is locked until they leave or the lock expires.',
			$payload['lockUserName'],
			$payload['label']
		);

		return $payload;
	}

	private function get_user_display_name( $user_id )
	{
		$user = get_user_by( 'id', absint( $user_id ) );
		return $user ? (string) $user->display_name : 'Another user';
	}
}
