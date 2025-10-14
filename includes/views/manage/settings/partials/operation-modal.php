<div class="modal fade" id="operation-modal-<?php echo $i; ?>" aria-modal="true" role="dialog" data-type="operation" data-index="<?php echo $i; ?>">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Edit Operation</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Operation Details</h3>
					</div><!-- /.card-header -->
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'type', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'base_metal', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'material', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'operation', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'description', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'setup_time', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'setup_unit', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'cycle_time', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'cycle_unit', $Operation, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'efficiency', $Operation, $i ); ?>
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