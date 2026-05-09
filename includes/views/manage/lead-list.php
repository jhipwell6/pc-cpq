<?php if ( isset( $_GET['editor_lock_notice'] ) && isset( $_GET['editor_lock_user'] ) ) : ?>
<div class="alert alert-warning">
	<?php
	$lock_user = sanitize_text_field( wp_unslash( $_GET['editor_lock_user'] ) );
	$lock_label = sanitize_text_field( wp_unslash( $_GET['editor_lock_label'] ?? 'lead' ) );
	echo esc_html( sprintf( '%s is already editing that %s. Please try again after they leave or the lock expires.', $lock_user, $lock_label ) );
	?>
</div>
<?php endif; ?>
<!-- Default box -->
<div class="card" id="lead-list-card">
	<div class="card-header">
		<h3 class="card-title">Leads</h3>

		<div class="card-tools d-flex justify-content-end align-items-center">
			<form method="get" class="mr-2 flex-shrink-0">
				<div class="input-group input-group-sm">
					<select class="form-control form-select" name="status" aria-label="Filter leads by status">
						<option value="">All Statuses</option>
						<?php foreach ( $status_options as $status_value => $status_label ) : ?>
							<option value="<?php echo esc_attr( $status_value ); ?>"<?php selected( $selected_status, $status_value ); ?>>
								<?php echo esc_html( $status_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="text" class="form-control form-control-sm" placeholder="Search leads" aria-label="Search leads" aria-describedby="search-leads"
						   name="q"
						   value="<?php echo esc_attr( $_GET['q'] ?? '' ); ?>"
						   >
					<div class="input-group-append">
						<button class="btn btn-sm btn-secondary" type="submit" id="search-leads">Search</button>
					</div>
				</div>
			</form>
			<?php if ( isset( $_GET['q'] ) || ! empty( $selected_status ) ) : ?>
			<a href="<?php echo esc_url( remove_query_arg( array( 'q', 'status', 'offset' ) ) ); ?>" class="btn btn-secondary btn-sm mr-2 flex-shrink-0" title="View All Leads">
				View All
            </a>
			<?php endif; ?>
			<a href="<?php echo PC_CPQ()->Site()->get_leads_page_url(); ?>new/" class="btn btn-primary btn-sm flex-shrink-0" title="Add Lead" id="lead-list-add-button">
				<i class="fas fa-plus"></i> Add Lead
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
						Lead
					</th>
					<?php if ( PC_CPQ()->Settings()->is_nutshell_enabled() ) : ?>
					<th style="width: 8%">
						Nutshell ID
					</th>
					<?php endif; ?>
					<th style="width: 10%">
						Date
					</th>
					<th>
						Service
					</th>
					<th>
						Industry
					</th>
					<th>
						Stage
					</th>
					<th style="width: 8%" class="text-center">
						Status
					</th>
					<th style="width: 36%">
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $leads as $lead_index => $Lead ) : ?>
					<?php
					$status = $Lead->get_status();
					$status_badge_classes = [
						'New' => 'badge-primary',
						'Quoted' => 'badge-success',
						'Canceled' => 'badge-danger',
						'Pending' => 'badge-warning',
						'No Quote' => 'badge-secondary',
					];
					$status_badge_class = $status_badge_classes[ $status ] ?? 'badge-light';
					$lock_status = PC_CPQ()->Post_Lock()->get_post_lock_status( $Lead->get_id(), 'lead' );
					?>
					<tr data-type="lead" data-id="<?php echo $Lead->get_id(); ?>">
						<td>
							# <?php echo $Lead->get_quote_number(); ?>
							<br/>
							<small>
								Ext ID <?php echo $Lead->get_external_id(); ?>
							</small>
						</td>
						<td>
							<a href="<?php echo $Lead->get_manage_url(); ?>"<?php echo 0 === $lead_index ? ' class="js-tour-first-lead"' : ''; ?>>
								<?php echo $Lead->get_company() ?: ( $Lead->has_customer() ? $Lead->get_Customer()->get_name() : 'N/A' ); ?>
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
								<?php echo $Lead->get_title(); ?>
							</small>
						</td>
						<?php if ( PC_CPQ()->Settings()->is_nutshell_enabled() ) : ?>
						<td>
							# <?php echo $Lead->get_nutshell_id() ?? 'N/A'; ?>
						</td>
						<?php endif; ?>
						<td>
							<?php echo $Lead->get_date( 'm/d/Y h:i A' ); ?>
						</td>
						<td>
							<?php echo $Lead->get_service(); ?>
						</td>
						<td>
							<?php echo $Lead->get_industry(); ?>
						</td>
						<td>
							<?php echo $Lead->get_stage(); ?>
						</td>
						<td class="project-state">
							<span class="badge <?php echo $status_badge_class; ?>"><?php echo $status; ?></span>
						</td>
						<td class="project-actions text-right">
							<?php if ( ! empty( $lock_status['locked'] ) ) : ?>
							<span class="badge badge-warning mr-2">Locked</span>
							<?php endif; ?>
							<a class="btn btn-primary btn-sm<?php echo 0 === $lead_index ? ' js-tour-first-lead-button' : ''; ?>" href="<?php echo $Lead->get_manage_url(); ?>">
								<i class="fas fa-folder">
								</i>
								View
							</a>
							<button type="button" class="btn btn-danger btn-sm js-delete-lead" data-id="<?php echo $Lead->get_id(); ?>"<?php echo ! empty( $lock_status['locked'] ) ? ' disabled title="Locked by ' . esc_attr( $lock_status['lockUserName'] ) . '"' : ''; ?>>
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
