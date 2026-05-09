<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
		</li>
		<li class="nav-item d-none d-sm-inline-block">
			<a href="<?php echo $Site->get_manage_page_url(); ?>" class="nav-link">Home</a>
		</li>
		<li class="nav-item d-none d-sm-inline-block">
			<a href="<?php echo $Site->get_support_page_url(); ?>" class="nav-link">Support</a>
		</li>
	</ul>

	<!-- Right navbar links -->
	<ul class="navbar-nav ml-auto">
		<?php if ( PC_CPQ()->User()->can_manage_settings() && ! PC_CPQ()->Settings()->is_onboarding_complete() ) : ?>
		<li class="nav-item">
			<a class="nav-link" href="<?php echo esc_url( $Site->get_manage_page_url() ); ?>#onboarding-checklist" role="button">
				Workspace setup
			</a>
		</li>
		<?php endif; ?>
		<?php if ( PC_CPQ()->User()->can_manage_settings() && PC_CPQ()->Settings()->is_onboarding_complete() ) : ?>
		<li class="nav-item">
			<form action="" method="post" class="js-edit-settings-onboarding-form mb-0">
				<input type="hidden" name="mode" value="reopen">
				<?php wp_nonce_field( 'edit_settings_onboarding', 'edit_settings_onboarding_nonce' ); ?>
				<a class="nav-link js-submit-parent-form" href="#" role="button">
					Workspace setup
				</a>
			</form>
		</li>
		<?php endif; ?>
		<li class="nav-item">
			<a class="nav-link js-restart-tour" href="#" role="button">
				Page guide
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link js-start-full-tour" href="#" role="button">
				Full tour
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" data-widget="fullscreen" href="#" role="button">
				<i class="fas fa-expand-arrows-alt"></i>
			</a>
		</li>
	</ul>
</nav>
<!-- /.navbar -->
