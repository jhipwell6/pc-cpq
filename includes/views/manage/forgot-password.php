<div class="login-page">
	<div class="login-box">
		<div class="login-logo">
			<span><?php echo PC_CPQ_NAME; ?></span>
		</div>
		<!-- /.login-logo -->
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>
				<?php
				$forgot_error = isset( $_GET['forgot_error'] ) ? sanitize_key( wp_unslash( $_GET['forgot_error'] ) ) : '';
				$forgot_messages = array(
					'invalid_user' => 'We could not find a user with that username or email.',
					'empty_user' => 'Enter your username or email to request a password reset.',
				);
				?>
				<?php if ( isset( $_GET['forgot_sent'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['forgot_sent'] ) ) ) : ?>
				<div class="alert alert-success">
					If we found a matching account, we sent a password reset link to that email address.
				</div>
				<?php endif; ?>
				<?php if ( $forgot_error && isset( $forgot_messages[ $forgot_error ] ) ) : ?>
				<div class="alert alert-danger">
					<?php echo esc_html( $forgot_messages[ $forgot_error ] ); ?>
				</div>
				<?php endif; ?>
				<form id="lostpasswordform" action="<?php echo esc_url( $Site->get_login_page_url( array( 'forgot_password' => 1 ) ) ); ?>" method="post">
					<div class="input-group mb-3">
						<input type="text" class="form-control" placeholder="Email" name="user_login" id="user_login">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'pc_cpq_forgot_password', 'pc_cpq_forgot_password_nonce' ); ?>
					<div class="row">
						<div class="col-12">
							<input type="submit" name="submit" class="btn btn-primary btn-block" value="Reset password" />
						</div>
						<!-- /.col -->
					</div>
				</form>
				<p class="mt-3 mb-1">
					<a href="<?php echo remove_query_arg( 'forgot_password' ); ?>">Login</a>
				</p>
			</div>
			<!-- /.login-card-body -->
		</div>
	</div>
	<!-- /.login-box -->
</div>
<!-- /.login-page -->
