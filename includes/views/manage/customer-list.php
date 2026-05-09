<?php if ( isset( $_GET['editor_lock_notice'] ) && isset( $_GET['editor_lock_user'] ) ) : ?>
<div class="alert alert-warning">
	<?php
	$lock_user = sanitize_text_field( wp_unslash( $_GET['editor_lock_user'] ) );
	$lock_label = sanitize_text_field( wp_unslash( $_GET['editor_lock_label'] ?? 'customer' ) );
	echo esc_html( sprintf( '%s is already editing that %s. Please try again after they leave or the lock expires.', $lock_user, $lock_label ) );
	?>
</div>
<?php endif; ?>
<!-- Default box -->
<div class="card" id="customer-list-card">
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
			<a href="<?php echo PC_CPQ()->Site()->get_customers_page_url(); ?>new/" class="btn btn-primary btn-sm flex-shrink-0" title="Add Customer" id="customer-list-add-button">
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
				<?php foreach ( $customers as $customer_index => $Customer ) : ?>
				<?php $lock_status = PC_CPQ()->Post_Lock()->get_post_lock_status( $Customer->get_id(), 'customer' ); ?>
				<tr data-type="customer" data-id="<?php echo $Customer->get_id(); ?>">
					<td>
						# <?php echo $Customer->get_id(); ?>
						<br/>
						<small>
							Sales ID <?php echo $Customer->get_sales_id(); ?>
						</small>
					</td>
					<td>
						<a href="<?php echo $Customer->get_manage_url(); ?>"<?php echo 0 === $customer_index ? ' class="js-tour-first-customer"' : ''; ?>>
							<?php echo $Customer->get_name(); ?>
						</a>
						<?php if ( ! empty( $lock_status['locked'] ) ) : ?>
							<br/>
							<small class="text-warning">
								<i class="fas fa-lock"></i>
								Locked by <?php echo esc_html( $lock_status['lockUserName'] ); ?>
							</small>
						<?php endif; ?>
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
						<?php if ( ! empty( $lock_status['locked'] ) ) : ?>
						<span class="badge badge-warning mr-2">Locked</span>
						<?php endif; ?>
						<a class="btn btn-primary btn-sm<?php echo 0 === $customer_index ? ' js-tour-first-customer-button' : ''; ?>" href="<?php echo $Customer->get_manage_url(); ?>">
							<i class="fas fa-folder">
							</i>
							View
						</a>
						<button type="button" class="btn btn-danger btn-sm js-delete-customer" data-id="<?php echo $Customer->get_id(); ?>"<?php echo ! empty( $lock_status['locked'] ) ? ' disabled title="Locked by ' . esc_attr( $lock_status['lockUserName'] ) . '"' : ''; ?>>
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
