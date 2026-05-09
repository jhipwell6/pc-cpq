<div id="edit-settings-onboarding">
	<form action="" method="post" class="js-edit-settings-onboarding-form">
		<div class="row">
			<?php if ( ! $Settings->is_onboarding_complete() ) : ?>
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Workspace Setup</h3>
					</div>
					<div class="card-body">
						<p class="mb-4">Use this checklist to get your workspace ready for quoting. Each step opens the real settings screen for that area.</p>
						<?php $checklist = $Settings->get_onboarding_checklist(); ?>
						<?php foreach ( $checklist as $item ) : ?>
						<div class="border rounded p-3 mb-3">
							<div class="d-flex justify-content-between align-items-start">
								<div class="pr-3">
									<h4 class="h6 mb-1"><?php echo esc_html( $item['title'] ); ?></h4>
									<p class="mb-2 text-muted"><?php echo esc_html( $item['description'] ); ?></p>
									<a href="<?php echo esc_url( $item['url'] ); ?>" class="btn btn-sm btn-outline-primary">Open <?php echo esc_html( $item['title'] ); ?></a>
								</div>
								<span class="badge <?php echo $item['complete'] ? 'badge-success' : 'badge-secondary'; ?>">
									<?php echo $item['complete'] ? 'Complete' : 'Incomplete'; ?>
								</span>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="<?php echo $Settings->is_onboarding_complete() ? 'col-md-5' : 'col-md-4'; ?>">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Readiness</h3>
					</div>
					<div class="card-body">
						<p class="mb-3">
							Status:
							<strong><?php echo $Settings->is_onboarding_complete() ? 'Complete' : 'Incomplete'; ?></strong>
						</p>
						<?php if ( $Settings->is_onboarding_complete() ) : ?>
						<p class="text-muted">Your workspace has been marked ready to use. Reopen setup if you want the reminder to show again.</p>
						<input type="hidden" name="mode" value="reopen">
						<?php else : ?>
						<p class="text-muted">Use the checklist as guidance, then mark setup complete when your workspace is ready for the way you plan to quote.</p>
						<p class="text-muted mb-3">You may not need every setting. For example, a cost-plus workflow may leave some utilization-based defaults unused.</p>
						<input type="hidden" name="mode" value="complete">
						<?php endif; ?>
						<?php wp_nonce_field( 'edit_settings_onboarding', 'edit_settings_onboarding_nonce' ); ?>
						<input
							type="submit"
							value="<?php echo $Settings->is_onboarding_complete() ? 'Reopen Setup' : 'Mark Setup Complete'; ?>"
							class="btn btn-success float-right js-edit-settings-onboarding-submit"
						>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
