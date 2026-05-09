<?php
$status_badge_classes = array(
	'New' => 'badge-primary',
	'Quoted' => 'badge-success',
	'Canceled' => 'badge-danger',
	'Pending' => 'badge-warning',
	'No Quote' => 'badge-secondary',
);
$status_card_meta = array(
	'New' => array( 'label' => 'New', 'color' => 'primary' ),
	'Pending' => array( 'label' => 'Pending', 'color' => 'warning' ),
	'Quoted' => array( 'label' => 'Quoted', 'color' => 'success' ),
	'No Quote' => array( 'label' => 'No Quote', 'color' => 'secondary' ),
	'Canceled' => array( 'label' => 'Canceled', 'color' => 'danger' ),
);
?>
<div id="reports-page">
<div class="row">
	<div class="col-12">
		<div class="card" id="reports-filters-card">
			<div class="card-header">
				<h3 class="card-title">Report Filters</h3>
			</div>
			<div class="card-body">
				<form method="get" class="form-row align-items-end">
					<div class="col-md-3">
						<label for="report-from">From</label>
						<input type="date" id="report-from" name="report_from" class="form-control" value="<?php echo esc_attr( $filters['from'] ); ?>">
					</div>
					<div class="col-md-3">
						<label for="report-to">To</label>
						<input type="date" id="report-to" name="report_to" class="form-control" value="<?php echo esc_attr( $filters['to'] ); ?>">
					</div>
					<div class="col-md-3">
						<button type="submit" class="btn btn-primary">Apply Filters</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-7">
		<div class="card" id="reports-activity-card">
			<div class="card-header">
				<h3 class="card-title">Activity Trends</h3>
			</div>
			<div class="card-body">
				<div class="mb-2 text-muted">Daily totals for leads created, quotes sent, follow-ups scheduled, and quotes expiring.</div>
				<div class="position-relative" style="height: 320px;">
					<canvas id="reports-activity-trend-chart"></canvas>
				</div>
			</div>
		</div>

		<div class="card" id="reports-status-trends-card">
			<div class="card-header">
				<h3 class="card-title">Lead Status Trends</h3>
			</div>
			<div class="card-body">
				<div class="mb-2 text-muted">Daily lead volume by status for the selected date range.</div>
				<div class="position-relative" style="height: 320px;">
					<canvas id="reports-status-trend-chart"></canvas>
				</div>
			</div>
		</div>

		<div class="card" id="reports-summary-card">
			<div class="card-header">
				<h3 class="card-title">Lead Status Summary</h3>
				<div class="card-tools">
					<a href="<?php echo esc_url( $Reports->get_export_url( 'lead_status_summary', $filters ) ); ?>" class="btn btn-tool">Export CSV</a>
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<?php foreach ( $lead_status_summary as $status => $count ) : ?>
					<?php $meta = $status_card_meta[ $status ] ?? array( 'label' => $status, 'color' => 'light' ); ?>
					<div class="col-md-4 col-sm-6 mb-3">
						<div class="info-box mb-0">
							<span class="info-box-icon bg-<?php echo esc_attr( $meta['color'] ); ?>">
								<span class="font-weight-bold"><?php echo esc_html( $count ); ?></span>
							</span>
							<div class="info-box-content">
								<span class="info-box-text"><?php echo esc_html( $meta['label'] ); ?></span>
								<span class="info-box-number">Leads in range</span>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="card" id="reports-quotes-sent-card">
			<div class="card-header">
				<h3 class="card-title">Quotes Sent In Range</h3>
				<div class="card-tools">
					<a href="<?php echo esc_url( $Reports->get_export_url( 'quotes_sent', $filters ) ); ?>" class="btn btn-tool">Export CSV</a>
				</div>
			</div>
			<div class="card-body p-0">
				<?php if ( ! empty( $quotes_sent_rows ) ) : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th>Lead</th>
							<th>Status</th>
							<th>Quote Date</th>
							<th class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $quotes_sent_rows as $lead ) : ?>
						<tr>
							<td>
								<div><?php echo esc_html( $lead['company'] ); ?></div>
								<small class="text-muted">#<?php echo esc_html( $lead['quote_number'] ); ?><?php echo $lead['title'] ? ' - ' . esc_html( $lead['title'] ) : ''; ?></small>
							</td>
							<td>
								<span class="badge <?php echo esc_attr( $status_badge_classes[ $lead['status'] ] ?? 'badge-light' ); ?>">
									<?php echo esc_html( $lead['status'] ); ?>
								</span>
							</td>
							<td><?php echo esc_html( $lead['quote_date'] ); ?></td>
							<td class="text-right">
								<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-outline-primary btn-sm">Open</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<div class="p-3 text-muted">No quotes were sent in the selected date range.</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="card" id="reports-follow-up-card">
			<div class="card-header">
				<h3 class="card-title">Follow-Up Queue</h3>
				<div class="card-tools">
					<a href="<?php echo esc_url( $Reports->get_export_url( 'follow_up_queue', $filters ) ); ?>" class="btn btn-tool">Export CSV</a>
				</div>
			</div>
			<div class="card-body p-0">
				<?php if ( ! empty( $follow_up_rows ) ) : ?>
				<table class="table table-striped mb-0">
					<thead>
						<tr>
							<th>Lead</th>
							<th>Follow-Up Date</th>
							<th>Status</th>
							<th class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $follow_up_rows as $lead ) : ?>
						<tr>
							<td>
								<div><?php echo esc_html( $lead['company'] ); ?></div>
								<small class="text-muted">#<?php echo esc_html( $lead['quote_number'] ); ?></small>
							</td>
							<td><?php echo esc_html( $lead['follow_up_date'] ); ?></td>
							<td>
								<span class="badge <?php echo esc_attr( $status_badge_classes[ $lead['status'] ] ?? 'badge-light' ); ?>">
									<?php echo esc_html( $lead['status'] ); ?>
								</span>
							</td>
							<td class="text-right">
								<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-outline-primary btn-sm">Open</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<div class="p-3 text-muted">No follow-ups fall within the selected date range.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="card" id="reports-expiring-card">
			<div class="card-header">
				<h3 class="card-title">Quotes Sent / Expiring</h3>
				<div class="card-tools">
					<a href="<?php echo esc_url( $Reports->get_export_url( 'quotes_expiring', $filters ) ); ?>" class="btn btn-tool">Export CSV</a>
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-sm-6 mb-3">
						<div class="info-box mb-0">
							<span class="info-box-icon bg-success">
								<span class="font-weight-bold"><?php echo esc_html( $quotes_sent_summary ); ?></span>
							</span>
							<div class="info-box-content">
								<span class="info-box-text">Quotes Sent</span>
								<span class="info-box-number">In selected range</span>
							</div>
						</div>
					</div>
					<div class="col-sm-6 mb-3">
						<div class="info-box mb-0">
							<span class="info-box-icon bg-warning">
								<span class="font-weight-bold"><?php echo esc_html( $quotes_expiring_summary ); ?></span>
							</span>
							<div class="info-box-content">
								<span class="info-box-text">Quotes Expiring</span>
								<span class="info-box-number">In selected range</span>
							</div>
						</div>
					</div>
				</div>
				<?php if ( ! empty( $expiring_quote_rows ) ) : ?>
				<ul class="list-group list-group-flush border-top">
					<?php foreach ( $expiring_quote_rows as $lead ) : ?>
					<li class="list-group-item d-flex justify-content-between align-items-center px-0">
						<div class="pr-2">
							<div><?php echo esc_html( $lead['company'] ); ?></div>
							<small class="text-muted">Expires <?php echo esc_html( $lead['expiration_date'] ); ?></small>
						</div>
						<a href="<?php echo esc_url( $lead['manage_url'] ); ?>" class="btn btn-outline-primary btn-sm">Open</a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php else : ?>
				<p class="text-muted mb-0">No quotes are expiring in the selected range.</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="card" id="reports-follow-up-summary-card">
			<div class="card-header">
				<h3 class="card-title">Follow-Up Queue Summary</h3>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-sm-6 mb-3">
						<div class="info-box mb-0">
							<span class="info-box-icon bg-danger">
								<span class="font-weight-bold"><?php echo esc_html( $follow_up_overdue_count ); ?></span>
							</span>
							<div class="info-box-content">
								<span class="info-box-text">Overdue</span>
								<span class="info-box-number">Needs attention now</span>
							</div>
						</div>
					</div>
					<div class="col-sm-6 mb-3">
						<div class="info-box mb-0">
							<span class="info-box-icon bg-info">
								<span class="font-weight-bold"><?php echo esc_html( $follow_up_range_count ); ?></span>
							</span>
							<div class="info-box-content">
								<span class="info-box-text">In Range</span>
								<span class="info-box-number">Scheduled follow-ups</span>
							</div>
						</div>
					</div>
				</div>
				<p class="text-muted mb-0">Use the date range above to review scheduled follow-up workload, while overdue follow-ups remain anchored to the current day.</p>
			</div>
		</div>
	</div>
</div>
</div>

<script type="application/json" id="reports-activity-trend-data"><?php echo wp_json_encode( $activity_trend_chart ); ?></script>
<script type="application/json" id="reports-status-trend-data"><?php echo wp_json_encode( $lead_status_trend_chart ); ?></script>
