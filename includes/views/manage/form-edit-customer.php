<?php if ( ! PC_CPQ()->is_request( 'ajax' ) ) { ?><div id="edit-customer"><?php } ?>
	<form action="" method="post" class="js-edit-customer-form">
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
						<div class="form-group row">
							<label for="name" class="col-sm-2 col-form-label">Name</label>
							<div class="col-sm-10">
								<input type="text" id="name" class="form-control form-control-border" name="name" value="<?php echo $Customer->get_name(); ?>" />
							</div>
						</div>
						<?php echo pc_cpq_get_input_html( 'customer_code', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'phone', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'fax', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'website', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'sales_id', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'terms_code', $Customer ); ?>
					</div>
					<!-- /.card-body -->
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Billing</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'billing_street_address', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_street_address_2', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_city', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_state', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_other_state', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_zip', $Customer ); ?>
						<?php echo pc_cpq_get_input_html( 'billing_country', $Customer ); ?>
					</div>
					<!-- /.card-body -->
				</div>
			</div>
			<div class="col-md-5">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Contacts</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/partials/customer-contacts', $data ); ?>
					</div>
					<div class="card-footer">
						<button type="button" class="js-add-contact btn btn-primary btn-sm">Add Contact</button>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Shipping</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<?php echo PC_CPQ()->view( 'manage/partials/customer-shipping', $data ); ?>
					</div>
					<div class="card-footer">
						<button type="button" class="js-add-shipping btn btn-primary btn-sm">Add Shipping</button>
					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
			</div>
			<div class="col-md-2">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Quote</h3>
					</div>
					<div class="card-body">
						<?php wp_nonce_field( 'edit_customer', 'edit_customer_nonce' ); ?>
						<input type="hidden" name="customer_id" value="<?php echo $Customer->get_id(); ?>" />
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-customer-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
<?php if ( ! PC_CPQ()->is_request( 'ajax' ) ) { ?></div><?php } ?>