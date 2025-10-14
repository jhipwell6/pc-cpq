<div id="edit-lead">
	<form action="" method="post" class="js-edit-lead-form">
		<div class="row">
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Details</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'quote_number', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'status', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'service', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'finishing_type', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'industry', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'business', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'stage', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'certification', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'include_metal_factor', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'notes', $Lead ); ?>
						<?php echo PC_CPQ()->view( 'manage/fields/nutshell-input', [ 'Lead' => $Lead ] ); ?>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Contact</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'first_name', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'last_name', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'phone', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'email', $Lead ); ?>
						<?php echo PC_CPQ()->view( 'manage/fields/customer-input', [ 'Lead' => $Lead ] ); ?>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->

			</div>
			<div class="col-md-5">
				<?php if ( $Lead->get_id() ) : ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Parts</h3>
						<div class="card-tools">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-sm btn-default active">
									<input type="radio" name="unit_system" id="unit_system_imperial" value="imperial" autocomplete="off" class="js-non-reactive" checked> Imperial
								</label>
								<label class="btn btn-sm btn-default">
									<input type="radio" name="unit_system" id="unit_system_metric" value="metric" autocomplete="off" class="js-non-reactive"> Metric
								</label>
							</div>
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/partials/lead-parts', $data ); ?>
					</div>
					<div class="card-footer">
						<button type="button" class="js-add-part btn btn-primary btn-sm">Add Part</button>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
				<?php endif; ?>
			</div>
			<div class="col-md-2">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Quote</h3>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/partials/quote-details', $data ); ?>
					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						<?php wp_nonce_field( 'edit_lead', 'edit_lead_nonce' ); ?>
						<input type="hidden" name="lead_id" value="<?php echo $Lead->get_id(); ?>" />
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-lead-submit">
						<button type="button" class="btn btn-primary js-prepare-quote" data-toggle="modal" data-target="#prepare-quote-modal" disabled>Prepare New Quote</button>
					</div>
					<!-- /.card-footer -->
				</div>
				<!-- /.card -->
				<?php if ( $Lead->get_id() ) : ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Message</h3>
					</div>
					<div class="card-body">
						<button type="button" class="btn btn-primary float-right js-message-lead" data-toggle="modal" data-target="#message-lead-modal">Message Lead</button>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
				<?php endif; ?>
			</div>
		</div>
	</form>
	<?php echo PC_CPQ()->view( 'manage/partials/prepare-quote-modal', $data ); ?>
	<?php echo PC_CPQ()->view( 'manage/partials/preview-quote-modal', $data ); ?>
	<?php if ( $Lead->get_id() ) { echo PC_CPQ()->view( 'manage/partials/message-lead-modal', $data ); } ?>
	<?php
		if ( $Lead->get_id() ) {
			if ( ! isset( $data['Customer'] ) ) {
				$data['Customer'] = PC_CPQ()->customer( 0 );
			}
			echo PC_CPQ()->view( 'manage/partials/customer-modal', $data );
		}
	?>
</div>