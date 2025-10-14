<div class="modal fade" id="customer-modal" aria-modal="true" role="dialog" data-type="customer">
	<div class="modal-dialog">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Set Customer</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-body">
						<div class="form-group row" id="find-customer">
							<label for="raw_customer" class="col-sm-3 col-form-label">Search Customers</label>
							<div class="col-sm-9">
								<div class="input-group">
									<select class="js-customer-select" id="search-customers-select"></select>
								</div>
							</div>
						</div>
						<div class="form-group p-2 text-center">
							-- OR --
						</div>
						<div class="form-group row" id="create-customer">
							<label for="create_company" class="col-sm-3 col-form-label">Create Customer</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input type="text" id="create_company" class="form-control form-control-border js-create-company" value="" placeholder="Enter company name">
								</div>
							</div>
						</div>
					</div><!-- /.card-body -->
				</div>
            </div>
            <div class="modal-footer justify-content-end">
				<button type="button" class="btn btn-primary js-save-customer" aria-label="Save Customer">Save</button>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>