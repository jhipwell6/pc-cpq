<div id="edit-settings-parts">
	<form action="" method="post" class="js-edit-settings-parts-form">
		<div class="row">
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Price Default Settings</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'hourly_rate', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_margin', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_eff', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_people', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_eau', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_shift', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_break_in', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_metal_adder', $Settings ); ?>
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
						<?php wp_nonce_field( 'edit_settings_parts', 'edit_settings_parts_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-parts-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>