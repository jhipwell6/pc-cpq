<?php
$login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$reset_error = isset( $_GET['reset_error'] ) ? sanitize_key( wp_unslash( $_GET['reset_error'] ) ) : '';
$messages = array(
	'invalid_key' => 'That password setup link is no longer valid. Request a new password reset link and try again.',
	'password_mismatch' => 'The passwords did not match. Enter the same password in both fields and try again.',
	'empty_password' => 'Enter a new password to finish setting up your account.',
	'reset_failed' => 'We could not update your password. Please try again.',
);
?>
<div class="login-page">
	<div class="login-box">
		<div class="login-logo">
			<span><?php echo PC_CPQ_NAME; ?></span>
		</div>
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">Set your password to start using your workspace.</p>
				<?php if ( $reset_error && isset( $messages[ $reset_error ] ) ) : ?>
				<div class="alert alert-danger">
					<?php echo esc_html( $messages[ $reset_error ] ); ?>
				</div>
				<?php endif; ?>
				<form action="<?php echo esc_url( $Site->get_login_page_url( array( 'reset_password' => 1 ) ) ); ?>" method="post">
					<div class="input-group mb-3">
						<input type="password" class="form-control" placeholder="New Password" name="pass1" autocomplete="new-password">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-lock"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="password" class="form-control" placeholder="Confirm Password" name="pass2" autocomplete="new-password">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-lock"></span>
							</div>
						</div>
					</div>
					<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
					<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
					<?php wp_nonce_field( 'pc_cpq_reset_password', 'pc_cpq_reset_password_nonce' ); ?>
					<div class="row">
						<div class="col-12">
							<input type="submit" name="submit" class="btn btn-primary btn-block" value="Set Password">
						</div>
					</div>
				</form>
				<p class="mt-3 mb-1">
					<a href="<?php echo esc_url( $Site->get_login_page_url() ); ?>">Back to login</a>
				</p>
			</div>
		</div>
	</div>
</div>
