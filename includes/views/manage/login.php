<div class="login-page">
	<div class="login-box">
		<div class="login-logo">
			<span><?php echo PC_CPQ_NAME; ?></span>
		</div>
		<!-- /.login-logo -->
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">Sign in to manage your quotes</p>
				<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" name="loginform" id="loginform">
					<div class="input-group mb-3">
						<input type="text" class="form-control" placeholder="Username or Email" name="log" id="user_login" autocomplete="username">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="password" class="form-control" placeholder="Password" name="pwd" id="user_pass" autocomplete="current-password">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-lock"></span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-8">
							<div class="icheck-primary">
								<input type="checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
								<label for="rememberme">
									Remember Me
								</label>
							</div>
						</div>
						<!-- /.col -->
						<div class="col-4">
							<input type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-block" value="Sign In">
							<input type="hidden" name="redirect_to" value="<?php echo ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
						</div>
						<!-- /.col -->
					</div>
				</form>

				<p class="mb-1">
					<a href="<?php echo add_query_arg( 'forgot_password', 1 ); ?>">I forgot my password</a>
				</p>
			</div>
			<!-- /.login-card-body -->
		</div>
	</div>
	<!-- /.login-box -->
</div>
<!-- /.login-page -->