<div class="form-group row" id="customer-input">
	<label for="raw_customer" class="col-sm-2 col-form-label">Customer</label>
	<div class="col-sm-10">
		<?php if ( $Lead->has_customer() ) : ?>
		<div class="input-group">
			<input type="text" id="raw_customer" class="form-control form-control-border" value="<?php echo $Lead->get_Customer()->get_name(); ?>" readonly>
			<div class="input-group-append">
				<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#customer-modal">Change</button>
				<a href="<?php echo $Lead->get_Customer()->get_manage_url(); ?>" target="_blank" class="btn btn-primary" type="button" id="edit-customer">Edit Customer</a>
			</div>
		</div>
		<?php ; elseif ( $Lead->get_id() == 0 ) : ?>
		<span class="d-inline-block col-form-label text-muted">The lead must be saved first</span>
		<?php ; else : ?>
		Customer not found. <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#customer-modal">Set Customer</button>
		<?php endif; ?>
	</div>
</div>