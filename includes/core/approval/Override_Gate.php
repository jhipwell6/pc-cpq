<?php

namespace PC_CPQ\Core\Approval;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Override_Gate
{

	/**
	 * Determine if quote must require override based on issues.
	 */
	public function requires_override( array $issues ): bool
	{
		foreach ( $issues as $issue ) {
			if ( ($issue['severity'] ?? '') === 'block' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Enforce at "finalize/submit" time.
	 */
	public function can_finalize_quote( array $quote_meta ): bool
	{
		$status = $quote_meta['override_status'] ?? 'none';
		return $status !== 'required';
	}

	/**
	 * Capability check for approval.
	 */
	public function current_user_can_approve(): bool
	{
		return current_user_can( 'manage_options' );
	}
}
