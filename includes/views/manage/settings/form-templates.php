<div id="edit-settings-templates">
	<form action="" method="post" class="js-edit-settings-templates-form">
		<div class="row">
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Quote Template Settings</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'quote_header', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'quote_footer', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'quote_terms', $Settings ); ?>
					</div>
					<!-- /.card-body -->
				</div>
			</div>
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Email Template Settings</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/settings/partials/email-templates', $data ); ?>
					</div>
					<div class="card-footer">
						<button type="button" class="js-add-email-template btn btn-primary btn-sm">Add Template</button>
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
						<?php wp_nonce_field( 'edit_settings_templates', 'edit_settings_templates_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-templates-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>