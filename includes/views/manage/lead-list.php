<!-- Default box -->
<div class="card">
	<div class="card-header">
		<h3 class="card-title">Leads</h3>

		<div class="card-tools d-flex justify-content-end align-items-center">
			<form method="get" class="mr-2 flex-shrink-0">
				<div class="input-group">
					<input type="text" class="form-control form-control-sm" placeholder="Search leads" aria-label="Search leads" aria-describedby="search-leads"
						   name="q"
						   value="<?php echo $_GET['q'] ?? ''; ?>"   
						   >
					<div class="input-group-append">
						<button class="btn btn-sm btn-secondary" type="submit" id="search-leads">Search</button>
					</div>
				</div>
			</form>
			<?php if ( isset( $_GET['q'] ) ) : ?>
			<a href="<?php echo remove_query_arg( 'q' ); ?>" class="btn btn-secondary btn-sm mr-2 flex-shrink-0" title="View All Leads">
				View All
            </a>
			<?php endif; ?>
			<a href="<?php echo PC_CPQ()->Site()->get_leads_page_url(); ?>new/" class="btn btn-primary btn-sm flex-shrink-0" title="Add Lead">
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
					<th style="width: 8%">
						Nutshell ID
					</th>
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
				<?php foreach ( $leads as $Lead ) : ?>
					<tr data-type="lead" data-id="<?php echo $Lead->get_id(); ?>">
						<td>
							# <?php echo $Lead->get_quote_number(); ?>
							<br/>
							<small>
								Ext ID <?php echo $Lead->get_external_id(); ?>
							</small>
						</td>
						<td>
							<a href="<?php echo $Lead->get_manage_url(); ?>">
								<?php echo $Lead->get_company() ?: ( $Lead->has_customer() ? $Lead->get_Customer()->get_name() : 'N/A' ); ?>
							</a>
							<br/>
							<small>
								<?php echo $Lead->get_title(); ?>
							</small>
						</td>
						<td>
							# <?php echo $Lead->get_nutshell_id(); ?>
						</td>
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
							<span class="badge badge-success"><?php echo $Lead->get_status(); ?></span>
						</td>
						<td class="project-actions text-right">
							<a class="btn btn-primary btn-sm" href="<?php echo $Lead->get_manage_url(); ?>">
								<i class="fas fa-folder">
								</i>
								View
							</a>
							<button type="button" class="btn btn-danger btn-sm js-delete-lead" data-id="<?php echo $Lead->get_id(); ?>">
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