<div id="edit-settings-processes">
	<form action="" method="post" class="js-edit-settings-processes-form">
		<div class="row">
			<div class="col-md-10">
				<div class="card">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Process Settings</h3>
						<ul class="nav nav-pills ml-auto p-2">
							<li class="nav-item"><a class="nav-link active" href="#process_operations_pre" data-toggle="tab">Pre</a></li>
							<li class="nav-item"><a class="nav-link" href="#process_operations_prep" data-toggle="tab">Prep</a></li>
							<li class="nav-item"><a class="nav-link" href="#process_operations_plating" data-toggle="tab">Plating</a></li>
							<li class="nav-item"><a class="nav-link" href="#process_operations_post" data-toggle="tab">Post</a></li>
						</ul>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/settings/partials/operations', $data ); ?>
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
