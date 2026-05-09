<?php if ( is_user_logged_in() ) : ?>
	<!-- Wrapper -->
	<div class="wrapper">
		<?php echo PC_CPQ()->view( 'manage/partials/navigation', $data ); ?>
		<?php echo PC_CPQ()->view( 'manage/partials/sidebar', $data ); ?>
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<?php echo PC_CPQ()->view( 'manage/partials/heading', $data ); ?>
			<!-- Main content -->
			<section class="content">
				<div class="container-fluid">
					<?php if ( isset( $_GET['pc_cpq_notice'] ) && 'access_denied' === sanitize_text_field( wp_unslash( $_GET['pc_cpq_notice'] ) ) ) : ?>
					<div class="alert alert-warning">
						You do not have permission to access that page in this workspace.
					</div>
					<?php endif; ?>
					<?php the_content(); ?>
				</div>
			</section>
			<!-- /.content -->
		</div>
		<!-- /.content-wrapper -->
		<?php echo PC_CPQ()->view( 'manage/partials/footer', $data ); ?>
	</div>
	<!-- ./wrapper -->
	<?php
	;
else :
	if ( $Site->is_reset_password() ) {
		echo PC_CPQ()->view( 'manage/reset-password', $data );
	} elseif ( $Site->is_forgot_password() ) {
		echo PC_CPQ()->view( 'manage/forgot-password', $data );
	} else {
		echo PC_CPQ()->view( 'manage/login', $data );
	}
endif;
