<?php

namespace PC_CPQ\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Workspace_Users
{
	const DEFAULT_SEAT_LIMIT = 3;

	public function get_seat_limit()
	{
		return self::DEFAULT_SEAT_LIMIT;
	}

	public function get_workspace_users()
	{
		$users = get_users(
			array(
				'blog_id' => get_current_blog_id(),
				'orderby' => 'display_name',
				'order' => 'ASC',
			)
		);

		$rows = array();

		foreach ( $users as $user ) {
			if ( ! $this->is_workspace_member( $user ) ) {
				continue;
			}

			$rows[] = $this->build_user_row( $user );
		}

		usort( $rows, function ( $left, $right ) {
			return strcmp( $left['name'], $right['name'] );
		} );

		return $rows;
	}

	public function get_workspace_user_count()
	{
		return count( $this->get_workspace_users() );
	}

	public function get_remaining_seats()
	{
		return max( 0, $this->get_seat_limit() - $this->get_workspace_user_count() );
	}

	public function has_available_seat()
	{
		return $this->get_workspace_user_count() < $this->get_seat_limit();
	}

	public function get_role_options()
	{
		return User::get_workspace_role_labels();
	}

	public function add_user( array $data )
	{
		$email = sanitize_email( $data['email'] ?? '' );
		$first_name = sanitize_text_field( $data['first_name'] ?? '' );
		$last_name = sanitize_text_field( $data['last_name'] ?? '' );
		$role = sanitize_key( $data['role'] ?? '' );

		$this->assert_valid_role( $role );

		if ( ! is_email( $email ) ) {
			throw new \Exception( 'Enter a valid email address.' );
		}

		$user = get_user_by( 'email', $email );

		if ( $user && $this->is_workspace_member( $user ) ) {
			throw new \Exception( 'That user already has access to this workspace.' );
		}

		if ( ! $user && ! $this->has_available_seat() ) {
			throw new \Exception( 'All 3 seats are already in use. Remove a user before adding another.' );
		}

		if ( $user && ! $this->is_workspace_member( $user ) && ! $this->has_available_seat() ) {
			throw new \Exception( 'All 3 seats are already in use. Remove a user before adding another.' );
		}

		if ( ! $user ) {
			$user = $this->create_user( $email, $first_name, $last_name );
		}

		$this->assign_role( $user, $role );

		return $this->build_user_row( $user );
	}

	public function update_user_role( $user_id, $role )
	{
		$user = $this->get_workspace_user( $user_id );
		$current_role = $this->get_workspace_role( $user );
		$role = sanitize_key( $role );

		$this->assert_valid_role( $role );
		$this->assert_manageable_user( $user );
		$this->assert_not_current_user( $user );

		if ( $current_role === User::ROLE_WORKSPACE_OWNER && User::ROLE_QUOTE_MANAGER === $role ) {
			$this->assert_multiple_workspace_owners();
		}

		$this->assign_role( $user, $role );

		return $this->build_user_row( $user );
	}

	public function remove_user( $user_id )
	{
		$user = $this->get_workspace_user( $user_id );
		$role = $this->get_workspace_role( $user );

		$this->assert_manageable_user( $user );
		$this->assert_not_current_user( $user );

		if ( User::ROLE_WORKSPACE_OWNER === $role ) {
			$this->assert_multiple_workspace_owners();
		}

		if ( is_multisite() ) {
			remove_user_from_blog( $user->ID, get_current_blog_id() );
		} else {
			$user->remove_role( $role );
		}

		return true;
	}

	protected function build_user_row( \WP_User $user )
	{
		$role = $this->get_workspace_role( $user );
		$current_user_id = get_current_user_id();

		return array(
			'id' => $user->ID,
			'name' => trim( $user->display_name ) ? $user->display_name : trim( $user->first_name . ' ' . $user->last_name ),
			'email' => $user->user_email,
			'role' => $role,
			'role_label' => PC_CPQ()->User( $user )->get_role_label( $role ),
			'is_current_user' => intval( $current_user_id ) === intval( $user->ID ),
			'can_edit' => intval( $current_user_id ) !== intval( $user->ID ),
			'can_remove' => intval( $current_user_id ) !== intval( $user->ID ),
		);
	}

	protected function create_user( $email, $first_name, $last_name )
	{
		$username = $this->generate_username( $email, $first_name, $last_name );
		$password = wp_generate_password( 20, true, true );

		if ( is_multisite() ) {
			$user_id = wpmu_create_user( $username, $password, $email );
		} else {
			$user_id = wp_create_user( $username, $password, $email );
		}

		if ( is_wp_error( $user_id ) || ! $user_id ) {
			$message = is_wp_error( $user_id ) ? $user_id->get_error_message() : 'Unable to create that user.';
			throw new \Exception( $message );
		}

		wp_update_user(
			array(
				'ID' => $user_id,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'display_name' => trim( $first_name . ' ' . $last_name ) ? trim( $first_name . ' ' . $last_name ) : $username,
			)
		);

		if ( function_exists( 'wp_send_new_user_notifications' ) ) {
			wp_send_new_user_notifications( $user_id, 'user' );
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			throw new \Exception( 'The new user was created, but could not be loaded.' );
		}

		return $user;
	}

	protected function generate_username( $email, $first_name, $last_name )
	{
		$base = sanitize_user( current( explode( '@', $email ) ), true );
		if ( '' === $base ) {
			$base = sanitize_user( strtolower( trim( $first_name . $last_name ) ), true );
		}
		if ( '' === $base ) {
			$base = 'workspace-user';
		}

		$username = $base;
		$suffix = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $suffix;
			$suffix++;
		}

		return $username;
	}

	protected function assign_role( \WP_User $user, $role )
	{
		if ( is_multisite() ) {
			if ( ! is_user_member_of_blog( $user->ID, get_current_blog_id() ) ) {
				add_user_to_blog( get_current_blog_id(), $user->ID, $role );
				$user = get_user_by( 'id', $user->ID );
			} else {
				$user->set_role( $role );
			}
		} else {
			$user->set_role( $role );
		}

		return $user;
	}

	protected function get_workspace_user( $user_id )
	{
		$user = get_user_by( 'id', absint( $user_id ) );
		if ( ! $user || ! is_user_member_of_blog( $user->ID, get_current_blog_id() ) ) {
			throw new \Exception( 'That user is not part of this workspace.' );
		}

		return $user;
	}

	protected function get_workspace_role( \WP_User $user )
	{
		foreach ( array_keys( User::get_workspace_role_labels() ) as $role ) {
			if ( in_array( $role, (array) $user->roles, true ) ) {
				return $role;
			}
		}

		return '';
	}

	protected function is_workspace_member( \WP_User $user )
	{
		return '' !== $this->get_workspace_role( $user );
	}

	protected function assert_valid_role( $role )
	{
		if ( ! User::is_workspace_role( $role ) ) {
			throw new \Exception( 'Choose a valid workspace role.' );
		}
	}

	protected function assert_manageable_user( \WP_User $user )
	{
		if ( ! $this->is_workspace_member( $user ) ) {
			throw new \Exception( 'Only workspace users can be managed here.' );
		}
	}

	protected function assert_not_current_user( \WP_User $user )
	{
		if ( intval( $user->ID ) === intval( get_current_user_id() ) ) {
			throw new \Exception( 'Use a different Workspace Owner account to change your own access.' );
		}
	}

	protected function assert_multiple_workspace_owners()
	{
		$owner_count = 0;

		foreach ( $this->get_workspace_users() as $user ) {
			if ( User::ROLE_WORKSPACE_OWNER === $user['role'] ) {
				$owner_count++;
			}
		}

		if ( $owner_count <= 1 ) {
			throw new \Exception( 'Your workspace must keep at least one Workspace Owner.' );
		}
	}
}
