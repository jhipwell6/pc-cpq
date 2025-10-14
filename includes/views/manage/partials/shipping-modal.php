<div class="modal fade" id="shipping-modal-<?php echo $i; ?>" aria-modal="true" role="dialog" data-type="shipping" data-index="<?php echo $i; ?>">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Edit Shipping</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Shipping Details</h3>
					</div><!-- /.card-header -->
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'shipping_street_address', $Shipping, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'shipping_street_address_2', $Shipping, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'shipping_city', $Shipping, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'shipping_state', $Shipping, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'shipping_zip', $Shipping, $i ); ?>
						<?php echo pc_cpq_get_input_html( 'shipping_country', $Shipping, $i ); ?>
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