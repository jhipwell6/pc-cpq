<div class="modal fade" id="plating-metal-modal-<?php echo $i; ?>" aria-modal="true" role="dialog" data-type="plating_metal" data-index="<?php echo $i; ?>">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Edit Plating Metal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Plating Metal Details</h3>
					</div><!-- /.card-header -->
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'name', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'density', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'cost', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'deposit_rate', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'unit_type', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'unit_visible', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'min_lot_charge', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'precious_metal', $Plating_Metal, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'hide', $Plating_Metal, $i ); ?>
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