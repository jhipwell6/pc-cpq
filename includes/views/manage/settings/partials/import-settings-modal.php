<div class="modal fade" id="import-settings-modal-<?php echo $type; ?>" aria-modal="true" role="dialog" data-type="<?php echo $type; ?>">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Import <?php echo $type_label; ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-body">
						<form action="" method="post" class="js-import-settings-file-form">
							<div id="import-success" class="js-import-success d-none">
								<div class="alert alert-success">
									<p class="m-0"><i class="icon fas fa-check"></i> <?php echo $type_label; ?> imported successfully.</p>
								</div>
							</div>
							<div id="import-error" class="js-import-error d-none">
								<div class="alert alert-danger">
									<p class="m-0"><i class="icon fas fa-fa-ban"></i> Something went wrong. <?php echo $type_label; ?> not imported.</p>
								</div>
							</div>
							<div class="form-group">
								<label for="import-settings-file-<?php echo $type; ?>">Upload <?php echo $type_label; ?> File</label>
								<div class="input-group">
									<div class="custom-file">
										<input type="file" class="custom-file-input js-import-settings-file" id="import-settings-file-<?php echo $type; ?>" data-type="<?php echo $type; ?>">
										<label class="custom-file-label" for="import-settings-file-<?php echo $type; ?>">Choose file</label>
									</div>
									<div class="input-group-append">
										<input type="submit" value="Import" class="btn btn-success">
									</div>
								</div>
							</div>
						</form>
					</div><!-- /.card-body -->
				</div>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>