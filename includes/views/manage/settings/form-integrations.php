<div id="edit-settings-integrations">
	<form action="" method="post" class="js-edit-settings-integrations-form">
		<div class="row">
			<div class="col-md-7">
				<div class="card" id="integrations-overview-card">
					<div class="card-header">
						<h3 class="card-title">Integrations</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<p class="text-muted">Turn integrations on here and keep each connection's credentials with the rest of your CPQ settings.</p>
						<?php echo pc_cpq_get_input_html( 'enabled_integrations', $Settings ); ?>
					</div>
				</div>

				<div class="card js-integration-panel <?php echo $Settings->is_nutshell_enabled() ? '' : 'd-none'; ?>" id="integrations-nutshell-card" data-integration-panel="nutshell">
					<div class="card-header">
						<h3 class="card-title">Nutshell</h3>
						<div class="card-tools">
							<?php if ( $Settings->is_nutshell_configured() ) : ?>
							<span class="badge badge-success">Configured</span>
							<?php elseif ( $Settings->is_nutshell_enabled() ) : ?>
							<span class="badge badge-warning">Needs credentials</span>
							<?php else : ?>
							<span class="badge badge-secondary">Inactive</span>
							<?php endif; ?>
						</div>
					</div>
					<div class="card-body">
						<p class="text-muted">Use this when you want leads to sync into your Nutshell workspace. Credentials are stored in your workspace so you can connect your own account.</p>
						<?php echo pc_cpq_get_input_html( 'nutshell_account_name', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'nutshell_api_user', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'nutshell_api_key', $Settings ); ?>
					</div>
				</div>

				<div class="alert alert-light border js-no-integrations-message <?php echo ! empty( $Settings->get_enabled_integrations() ) ? 'd-none' : ''; ?>" id="integrations-empty-state">
					No integrations are enabled yet. Turn one on above to configure its connection settings.
				</div>
			</div>
			<div class="col-md-3 ml-auto">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Save</h3>
					</div>
					<div class="card-body">
						<?php wp_nonce_field( 'edit_settings_integrations', 'edit_settings_integrations_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-integrations-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
