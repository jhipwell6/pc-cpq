<div id="edit-lead" class="js-post-lock-scope">
	<div
		class="js-post-lock-root mb-3"
		data-post-lock="<?php echo esc_attr( wp_json_encode( isset( $post_lock ) ? $post_lock : array() ) ); ?>"
	>
		<div class="alert alert-warning js-post-lock-alert d-none mb-0"></div>
	</div>
	<form action="" method="post" class="js-edit-lead-form">
		<div class="row">
			<div class="col-md-4">
				<div class="card" id="lead-details-card">
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
						<?php echo pc_cpq_get_input_html( 'include_metal_factor', $Lead ); ?>
						<?php echo pc_cpq_get_input_html( 'notes', $Lead ); ?>
						<?php
							if ( PC_CPQ()->Settings()->is_nutshell_enabled() ) {
								echo PC_CPQ()->view( 'manage/fields/nutshell-input', [ 'Lead' => $Lead ] );
							}
						?>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
				<div class="card" id="lead-contact-card">
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
				<div class="card" id="lead-parts-card">
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
			<div class="col-md-3">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card" id="lead-quote-card">
					<div class="card-header">
						<h3 class="card-title">Quote</h3>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/partials/quote-details', $data ); ?>
					</div>
					<div class="card-body border-top">
						<?php echo pc_cpq_get_input_html( 'pricing_mode', $Lead ); ?>
					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						<?php wp_nonce_field( 'edit_lead', 'edit_lead_nonce' ); ?>
						<input type="hidden" name="lead_id" value="<?php echo $Lead->get_id(); ?>" />
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-lead-submit">
						<button type="button" id="lead-prepare-quote-button" class="btn btn-primary js-prepare-quote" data-toggle="modal" data-target="#prepare-quote-modal" disabled>Prepare New Quote</button>
					</div>
					<!-- /.card-footer -->
				</div>
				<!-- /.card -->
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Quote Notes</h3>
					</div>
					<div class="card-body">
						<?php echo PC_CPQ()->view( 'manage/fields/quote-notes', [ 'Lead' => $Lead ] ); ?>
					</div>
					<!-- /.card-body -->
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

<template id="part-file-preview-modal-template">
	<div class="modal fade" id="part-file-preview-modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered part-file-preview-modal" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">File Preview</h5>
					<div class="part-file-preview-modal__tools d-flex align-items-center mx-3 d-none">
						<div class="btn-group btn-group-sm" role="group" aria-label="Image zoom controls">
							<button type="button" class="btn btn-outline-primary js-file-preview-zoom-out">-</button>
							<button type="button" class="btn btn-outline-primary js-file-preview-zoom-reset">Reset</button>
							<button type="button" class="btn btn-outline-primary js-file-preview-zoom-in">+</button>
						</div>
					</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body part-file-preview-modal__body"></div>
			</div>
		</div>
	</div>
</template>

<template id="part-file-preview-pdf-template">
	<iframe class="part-file-preview-modal__iframe" src="{{url}}" title="{{name}}"></iframe>
</template>

<template id="part-file-preview-image-template">
	<div class="part-file-preview-modal__image-wrap">
		<img class="part-file-preview-modal__image" src="{{url}}" alt="{{name}}" />
	</div>
</template>
