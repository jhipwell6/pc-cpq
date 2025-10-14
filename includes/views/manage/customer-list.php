<!-- Default box -->
<div class="card">
	<div class="card-header">
		<h3 class="card-title">Customers</h3>
		
		<div class="card-tools d-flex justify-content-end align-items-center">
			<form method="get" class="mr-2 flex-shrink-0">
				<div class="input-group">
					<input type="text" class="form-control form-control-sm" placeholder="Search customers" aria-label="Search customers" aria-describedby="search-customers"
						   name="q"
						   value="<?php echo $_GET['q'] ?? ''; ?>"   
						   >
					<div class="input-group-append">
						<button class="btn btn-sm btn-secondary" type="submit" id="search-customers">Search</button>
					</div>
				</div>
			</form>
			<?php if ( isset( $_GET['q'] ) ) : ?>
			<a href="<?php echo remove_query_arg( 'q' ); ?>" class="btn btn-secondary btn-sm mr-2 flex-shrink-0" title="View All Customers">
				View All
            </a>
			<?php endif; ?>
			<a href="<?php echo PC_CPQ()->Site()->get_customers_page_url(); ?>new/" class="btn btn-primary btn-sm flex-shrink-0" title="Add Customer">
				<i class="fas fa-plus"></i> Add Customer
            </a>
		</div>
	</div>
	<div class="card-body p-0">
		<table class="table table-striped projects">
			<thead>
				<tr>
					<th style="width: 5%">
						#
					</th>
					<th style="width: 10%">
						Customer
					</th>
					<th style="width: 10%">
						Date
					</th>
					<th>
						Phone
					</th>
					<th>
						Website
					</th>
					<th style="width: 46%">
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $customers as $Customer ) : ?>
				<tr data-type="customer" data-id="<?php echo $Customer->get_id(); ?>">
					<td>
						# <?php echo $Customer->get_id(); ?>
						<br/>
						<small>
							Sales ID <?php echo $Customer->get_sales_id(); ?>
						</small>
					</td>
					<td>
						<a href="<?php echo $Customer->get_manage_url(); ?>">
							<?php echo $Customer->get_name(); ?>
						</a>
						<br/>
						<small>
							<?php echo $Customer->get_customer_code(); ?>
						</small>
					</td>
					<td>
						<?php echo $Customer->get_post_date('m/d/Y h:i A'); ?>
					</td>
					<td>
						<?php echo $Customer->get_phone(); ?>
					</td>
					<td>
						<?php echo $Customer->get_website(); ?>
					</td>
					<td class="project-actions text-right">
						<a class="btn btn-primary btn-sm" href="<?php echo $Customer->get_manage_url(); ?>">
							<i class="fas fa-folder">
							</i>
							View
						</a>
						<button type="button" class="btn btn-danger btn-sm js-delete-customer" data-id="<?php echo $Customer->get_id(); ?>">
							<i class="fas fa-trash"></i>
							Delete
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<!-- /.card-body -->
	<div class="card-footer clearfix">
		<?php echo PC_CPQ()->view( 'manage/partials/pagination', [ 'max_pages' => $max_pages ] );?>
	</div>
	<!-- /.card-footer -->
</div>
<!-- /.card -->