<div class="modal fade" id="message-lead-modal" aria-modal="true" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Message</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-body">
						<form action="" method="post" class="js-send-message-form">
							<div id="send-message-success" class="js-send-message-success d-none">
								<div class="alert alert-success">
									<p class="m-0"><i class="icon fas fa-check"></i> Message sent.</p>
								</div>
							</div>
							<div id="send-message-error" class="js-send-message-error d-none">
								<div class="alert alert-danger">
									<p class="m-0"><i class="icon fas fa-fa-ban"></i> Something went wrong. Message not sent.</p>
								</div>
							</div>
							<div class="form-group">
								<label for="recipients" class="form-label">Recipient(s) *</label>
								<input type="email" name="recipients" class="form-control js-non-reactive" id="messageRecipients" list="messageRecipientsList" autocomplete="off" required />
								<small id="recipientsHelp" class="form-text text-muted">Separate multiple recipients with a comma.</small>
								<?php if ( $Lead->has_customer() && $Lead->get_Customer()->has_contacts() ) : ?>
									<datalist id="messageRecipientsList">
										<?php foreach ( $Lead->get_Customer()->get_Contacts() as $Contact ) : ?>
											<option value="<?php echo $Contact->get_email(); ?>">
											<?php endforeach; ?>
									</datalist>
								<?php endif; ?>
							</div>
							<div class="form-group">
								<div class="d-flex align-items-center justify-content-between">
									<label for="message" class="form-label">Message</label>
									<?php if ( ! empty( PC_CPQ()->Settings()->get_Email_Templates() ) ) : ?>
									<div class="input-group w-auto mb-1">
										<select id="messageTemplate" class="form-control js-non-reactive js-load-message-template">
											<option value="null">Load a template</option>
											<?php foreach ( PC_CPQ()->Settings()->get_Email_Templates() as $Email_Template ) : ?>
											<option value="<?php echo $Email_Template->get_name(); ?>"><?php echo $Email_Template->get_name(); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<?php endif; ?>
								</div>
								<div class="input-group">
									<?php
									wp_editor( '', 'message', array(
										'quicktags' => false,
										'textarea_name' => 'message',
										'textarea_rows' => 12,
										'media_buttons' => false,
									) );
									?>
								</div>
							</div>
							<?php wp_nonce_field( 'send_message', 'send_message_nonce' ); ?>
							<input type="submit" value="Send Message" class="btn btn-success float-right js-send-message" />
						</form>
					</div><!-- /.card-body -->
				</div>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>