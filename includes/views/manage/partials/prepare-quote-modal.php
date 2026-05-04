<div class="modal fade" id="prepare-quote-modal" aria-modal="true" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Quote</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-body">
						<form action="" method="post" class="js-send-quote-form">
							<div id="send-quote-success" class="js-send-quote-success d-none">
								<div class="alert alert-success">
									<p class="m-0"><i class="icon fas fa-check"></i> Quote sent.</p>
								</div>
							</div>
							<div id="send-quote-error" class="js-send-quote-error d-none">
								<div class="alert alert-danger">
									<p class="m-0"><i class="icon fas fa-fa-ban"></i> Something went wrong. Quote not sent.</p>
								</div>
							</div>
							<?php echo pc_cpq_get_input_html( 'quote_pricing_type', $Lead ); ?>
							<div class="form-group">
								<label for="recipients" class="form-label">Recipient(s) *</label>
								<input type="text" name="recipients" class="form-control js-non-reactive" id="recipients" list="recipientsList" autocomplete="off" required />
								<small id="recipientsHelp" class="form-text text-muted">Separate multiple recipients with a comma.</small>
								<?php if ( $Lead->has_customer() && $Lead->get_Customer()->has_contacts() ) : ?>
								<datalist id="recipientsList">
									<?php foreach ( $Lead->get_Customer()->get_Contacts() as $Contact ) : ?>
									<option value="<?php echo $Contact->get_email(); ?>">
									<?php endforeach; ?>
								</datalist>
								<?php endif; ?>
							</div>
							<?php
								$selected_fees = wp_list_pluck( (array) $Lead->get_fees(), 'fee' );
								$has_saved_fees = ! empty( $selected_fees );
							?>
							<?php if ( ! empty( PC_CPQ()->Settings()->get_Fees() ) ) : ?>
							<div class="form-group">
								<label class="form-label d-block">Fees</label>
								<?php $i = 0; foreach ( PC_CPQ()->Settings()->get_Fees() as $Fee ) : ?>
								<?php
									$is_checked = $has_saved_fees ? in_array( $Fee->get_name(), $selected_fees, true ) : $Fee->is_enabled_by_default();
									$formatted_amount = $Fee->get_unit() == 'percent'
										? $Fee->get_amount() . '%'
										: '$' . $Fee->get_amount();
								?>
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="fees_<?php echo $i; ?>" name="selected_fees/<?php echo $i; ?>" value="<?php echo esc_attr( $Fee->get_name() ); ?>"<?php checked( $is_checked ); ?>>
									<label class="custom-control-label" for="fees_<?php echo $i; ?>">
										<?php echo $Fee->get_name(); ?>
										<small class="text-muted">(<?php echo esc_html( $formatted_amount ); ?>)</small>
									</label>
								</div>
								<?php $i++; endforeach; ?>
							</div>
							<?php endif; ?>
							<?php wp_nonce_field( 'send_quote', 'send_quote_nonce' ); ?>
							<?php wp_nonce_field( 'preview_quote', 'preview_quote_nonce' ); ?>
							<input type="submit" value="Send Quote" class="btn btn-success float-right js-send-quote" />
							<button type="button" class="btn btn-primary float-right js-preview-quote mr-3">Preview</button>
						</form>
					</div><!-- /.card-body -->
				</div>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
