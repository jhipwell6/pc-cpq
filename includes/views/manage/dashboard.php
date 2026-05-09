<?php
$Site = PC_CPQ()->Site();
$status_badge_classes = array(
	'New' => 'badge-primary',
	'Quoted' => 'badge-success',
	'Canceled' => 'badge-danger',
	'Pending' => 'badge-warning',
	'No Quote' => 'badge-secondary',
);
$workspace_completion_percent = $workspace_total_count > 0
	? (int) round( ( $workspace_completed_count / $workspace_total_count ) * 100 )
	: 0;
?>
<div id="manage-dashboard">
<?php if ( PC_CPQ()->User()->can_manage_settings() && ! $Settings->is_onboarding_complete() ) : ?>
<div class="row">
	<div class="col-12">
		<div class="alert alert-warning">
			<h5 class="mb-2">Workspace Setup Incomplete</h5>
			<p class="mb-2">Finish your setup checklist before treating your workspace as ready for live quoting.</p>
			<a href="#onboarding-checklist" class="btn btn-sm btn-primary">Continue Workspace Setup</a>
		</div>
	</div>
</div>
<div class="row" id="onboarding-checklist">
	<div class="col-12">
		<?php echo PC_CPQ()->view( 'manage/settings/form-onboarding', array( 'Settings' => $Settings ) ); ?>
	</div>
</div>
<?php endif; ?>

<div class="row">
	<?php foreach ( $status_cards as $card ) : ?>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="info-box">
			<span class="info-box-icon bg-<?php echo esc_attr( $card['color'] ); ?>">
				<i class="<?php echo esc_attr( $card['icon'] ); ?>"></i>
			</span>
			<div class="info-box-content">
				<span class="info-box-text"><?php echo esc_html( $card['label'] ); ?></span>
				<span class="info-box-number"><?php echo esc_html( $card['value'] ); ?></span>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<div class="row">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Recent Leads</h3>
				<div class="card-tools">
					<a href="<?php echo esc_url( $Site->get_leads_page_url() ); ?>" class="btn btn-tool">View all</a>
				</div>
			</div>
			<div class="card-body p-0">
				<?php if ( ! empty( $recent_leads ) ) : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th>Lead</th>
							<th>Status</th>
							<th>Date</th>
							<th class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_leads as $lead ) : ?>
						<tr>
							<td>
								<div><?php echo esc_html( $lead['company'] ); ?></div>
								<small class="text-muted">#<?php echo esc_html( $lead['quote_number'] ); ?><?php echo $lead['title'] ? ' - ' . esc_html( $lead['title'] ) : ''; ?></small>
							</td>
							<td>
								<?php $status = $lead['status']; ?>
								<span class="badge <?php echo esc_attr( $status_badge_classes[ $status ] ?? 'badge-light' ); ?>">
									<?php echo esc_html( $status ); ?>
								</span>
							</td>
							<td><?php echo esc_html( $lead['date'] ); ?></td>
							<td class="text-right">
								<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-primary btn-sm">Open</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<div class="p-3 text-muted">No leads have been created yet.</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Follow-Ups Due Soon</h3>
			</div>
			<div class="card-body p-0">
				<?php if ( ! empty( $follow_up_leads ) ) : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th>Lead</th>
							<th>Follow-Up</th>
							<th>Status</th>
							<th class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $follow_up_leads as $lead ) : ?>
						<tr>
							<td>
								<div><?php echo esc_html( $lead['company'] ); ?></div>
								<small class="text-muted">#<?php echo esc_html( $lead['quote_number'] ); ?></small>
							</td>
							<td><?php echo esc_html( $lead['follow_up_date'] ); ?></td>
							<td><?php echo esc_html( $lead['status'] ); ?></td>
							<td class="text-right">
								<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-outline-primary btn-sm">Open</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<div class="p-3 text-muted">No follow-ups are due in the next week.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<?php if ( PC_CPQ()->User()->can_manage_settings() ) : ?>
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Workspace Status</h3>
			</div>
			<div class="card-body">
				<p class="mb-2">
					<strong><?php echo esc_html( $workspace_completed_count ); ?></strong> of
					<strong><?php echo esc_html( $workspace_total_count ); ?></strong>
					setup areas are complete.
				</p>
				<div class="progress mb-3">
					<div class="progress-bar bg-<?php echo $Settings->is_onboarding_complete() ? 'success' : 'warning'; ?>" style="width: <?php echo esc_attr( $workspace_completion_percent ); ?>%">
						<?php echo esc_html( $workspace_completion_percent ); ?>%
					</div>
				</div>
				<div class="mb-3">
					<span class="badge <?php echo $Settings->is_onboarding_complete() ? 'badge-success' : 'badge-warning'; ?>">
						<?php echo $Settings->is_onboarding_complete() ? 'Ready for use' : 'Setup still in progress'; ?>
					</span>
				</div>
				<?php foreach ( $workspace_checklist as $item ) : ?>
				<div class="d-flex justify-content-between align-items-center border-top py-2">
					<div>
						<div><?php echo esc_html( $item['title'] ); ?></div>
						<small class="text-muted"><?php echo esc_html( $item['description'] ); ?></small>
					</div>
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="btn btn-outline-primary btn-sm">
						<?php echo $item['complete'] ? 'Review' : 'Open'; ?>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Quick Actions</h3>
			</div>
			<div class="card-body">
				<div class="d-flex flex-column">
					<a href="<?php echo esc_url( trailingslashit( $Site->get_leads_page_url() ) . 'new/' ); ?>" class="btn btn-primary btn-sm mb-2">Add Lead</a>
					<a href="<?php echo esc_url( $Site->get_leads_page_url() ); ?>" class="btn btn-outline-primary btn-sm mb-2">Open Leads Queue</a>
					<?php if ( PC_CPQ()->User()->can_manage_settings() ) : ?>
					<a href="<?php echo esc_url( $Site->get_settings_page_url( 'price' ) ); ?>" class="btn btn-outline-primary btn-sm mb-2">Review Price Settings</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $Site->get_support_page_url() ); ?>" class="btn btn-outline-primary btn-sm">Open Support</a>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Quotes Expiring Soon</h3>
			</div>
			<div class="card-body p-0">
				<?php if ( ! empty( $expiring_quotes ) ) : ?>
				<ul class="list-group list-group-flush">
					<?php foreach ( $expiring_quotes as $lead ) : ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div class="pr-2">
							<div><?php echo esc_html( $lead['company'] ); ?></div>
							<small class="text-muted">Expires <?php echo esc_html( $lead['expiration_date'] ); ?></small>
						</div>
						<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-outline-primary btn-sm">Open</a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php else : ?>
				<div class="p-3 text-muted">No quotes are expiring in the next week.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
</div>
