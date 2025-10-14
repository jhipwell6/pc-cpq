<div id="edit-settings-processes">
	<form action="" method="post" class="js-edit-settings-processes-form">
		<div class="row">
			<div class="col-md-10">
				<div class="card">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Process Settings</h3>
						<ul class="nav nav-pills ml-auto p-2">
							<li class="nav-item"><a class="nav-link active" href="#process_operations" data-toggle="tab">Operations</a></li>
							<li class="nav-item"><a class="nav-link" href="#process_post_operations" data-toggle="tab">Configure Post Operations</a></li>
						</ul>
					</div>
					<div class="card-body p-0">
						<div class="tab-content">
							<div class="tab-pane active" id="process_operations">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-operations" title="Import Operations">
											<i class="fas fa-file-import"></i> Import Operations
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Operations" data-type="operations">
											<i class="fas fa-file-export"></i> Export Operations
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/operations', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-operation btn btn-primary btn-sm">Add Operation</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							<div class="tab-pane" id="process_post_operations">
								<?php echo PC_CPQ()->view( 'manage/settings/partials/post-operations', $data ); ?>
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
						<?php wp_nonce_field( 'edit_settings_processes', 'edit_settings_processes_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-processes-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
$data['type'] = 'operations';
$data['type_label'] = pc_cpq_slug_to_label( $type );
echo PC_CPQ()->view( 'manage/settings/partials/import-settings-modal', $data );
