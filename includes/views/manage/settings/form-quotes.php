<div id="edit-settings-quotes">
	<form action="" method="post" class="js-edit-settings-quotes-form">
		<div class="row">
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Quote Settings</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'starting_quote_number', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'quote_expires_after', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'follow_up_after', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'domain_whitelist', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'email_whitelist', $Settings ); ?>
					</div>
					<!-- /.card-body -->
				</div>
			</div>
			<div class="col-md-2 ml-auto">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Save</h3>
					</div>
					<div class="card-body">
						<?php wp_nonce_field( 'edit_settings_quotes', 'edit_settings_quotes_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-quotes-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>