<div id="edit-settings-fees">
	<form action="" method="post" class="js-edit-settings-fees-form">
		<div class="row">
			<div class="col-md-10">
				<div class="card">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Fee Settings</h3>
					</div>
					<div class="card-body p-0">
						<div class="tab-content">
							<div class="tab-pane active" id="fees-tab">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-fees" title="Import Fees">
											<i class="fas fa-file-import"></i> Import Fees
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Fees" data-type="fees">
											<i class="fas fa-file-export"></i> Export Fees
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/fees', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-fee btn btn-primary btn-sm">Add Fee</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							
						</div>
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
						<?php wp_nonce_field( 'edit_settings_fees', 'edit_settings_fees_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-fees-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
$settings_modals = array(
	'fees',
);
foreach ( $settings_modals as $type ) {
	$data['type'] = $type;
	$data['type_label'] = pc_cpq_slug_to_label( $type );
	echo PC_CPQ()->view( 'manage/settings/partials/import-settings-modal', $data );
}

unset( $data['type'] );