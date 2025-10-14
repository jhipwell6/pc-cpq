<div id="edit-settings-plating">
	<form action="" method="post" class="js-edit-settings-plating-form">
		<div class="row">
			<div class="col-md-10">
				<div class="card">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Plating Settings</h3>
						<ul class="nav nav-pills ml-auto p-2">
							<li class="nav-item"><a class="nav-link active" href="#plating_metals" data-toggle="tab">Base Metals</a></li>
							<li class="nav-item"><a class="nav-link" href="#plating_plating_metals" data-toggle="tab">Plating Metals</a></li>
							<li class="nav-item"><a class="nav-link" href="#plating_lines" data-toggle="tab">Lines</a></li>
							<li class="nav-item"><a class="nav-link" href="#plating_barrels" data-toggle="tab">Barrels</a></li>
							<li class="nav-item"><a class="nav-link" href="#plating_racks" data-toggle="tab">Racks</a></li>
						</ul>
					</div>
					<div class="card-body p-0">
						<div class="tab-content">
							<div class="tab-pane active" id="plating_metals">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-metals" title="Import Base Metals">
											<i class="fas fa-file-import"></i> Import Base Metals
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Base Metals" data-type="metals">
											<i class="fas fa-file-export"></i> Export Base Metals
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/metals', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-metal btn btn-primary btn-sm">Add Base Metal</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							<div class="tab-pane" id="plating_plating_metals">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-plating_metals" title="Import Plating Metals">
											<i class="fas fa-file-import"></i> Import Plating Metals
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Plating Metals" data-type="plating_metals">
											<i class="fas fa-file-export"></i> Export Plating Metals
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/plating-metals', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-plating-metal btn btn-primary btn-sm">Add Plating Metal</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							<div class="tab-pane" id="plating_lines">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-lines" title="Import Lines">
											<i class="fas fa-file-import"></i> Import Lines
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Lines" data-type="lines">
											<i class="fas fa-file-export"></i> Export Lines
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/lines', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-line btn btn-primary btn-sm">Add Line</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							<div class="tab-pane" id="plating_barrels">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-barrels" title="Import Barrels">
											<i class="fas fa-file-import"></i> Import Barrels
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Barrels" data-type="barrels">
											<i class="fas fa-file-export"></i> Export Barrels
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/barrels', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-barrel btn btn-primary btn-sm">Add Barrel</button>
								</div>
							</div>
							<!-- /.tab-pane -->
							<div class="tab-pane" id="plating_racks">
								<div class="tab-header">
									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-racks" title="Import Racks">
											<i class="fas fa-file-import"></i> Import Racks
										</button>
										<button type="button" class="js-export-settings btn btn-tool" title="Export Racks" data-type="racks">
											<i class="fas fa-file-export"></i> Export Racks
										</button>
									</div>
								</div>
								<?php echo PC_CPQ()->view( 'manage/settings/partials/racks', $data ); ?>
								<div class="tab-footer card-footer">
									<button type="button" class="js-add-rack btn btn-primary btn-sm">Add Rack</button>
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
						<?php wp_nonce_field( 'edit_settings_plating', 'edit_settings_plating_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-plating-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
$settings_modals = array(
	'metals',
	'plating_metals',
	'lines',
	'barrels',
	'racks',
);
foreach ( $settings_modals as $type ) {
	$data['type'] = $type;
	$data['type_label'] = pc_cpq_slug_to_label( $type );
	echo PC_CPQ()->view( 'manage/settings/partials/import-settings-modal', $data );
}

unset( $data['type'] );