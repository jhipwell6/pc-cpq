<div class="modal fade" id="email-template-modal-<?php echo $i; ?>" aria-modal="true" role="dialog" data-type="email_template" data-index="<?php echo $i; ?>">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Edit Email Template</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Template Details</h3>
					</div><!-- /.card-header -->
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'name', $Email_Template, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'template', $Email_Template, $i ); ?>
					</div><!-- /.card-body -->
				</div>
            </div>
            <div class="modal-footer justify-content-end">
				<button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Done">Done</button>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>