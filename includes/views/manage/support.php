<?php
$support_email = 'support@polycoatcpq.com';
$support_subject = rawurlencode( PC_CPQ_NAME . ' Support Request' );
$support_mailto = $support_email ? 'mailto:' . antispambot( $support_email ) . '?subject=' . $support_subject : '';
?>
<div class="row">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Get Help</h3>
			</div>
			<div class="card-body">
				<p class="mb-3">Use this page when you need help with workspace setup, lead workflows, pricing behavior, or customer-facing quote output.</p>
				<p class="mb-0">When you reach out, include the lead number, customer name, and a short note about what you expected to happen versus what actually happened.</p>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Before You Reach Out</h3>
			</div>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">Confirm your latest changes are saved before testing again.</li>
					<li class="list-group-item">If a quote was already sent, check whether the snapshot needs to be refreshed with <strong>Requote</strong>.</li>
					<li class="list-group-item">For pricing questions, note whether the lead is using the default pricing mode or a lead override.</li>
					<li class="list-group-item">For setup questions, review <strong>Workspace setup</strong> first.</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Contact</h3>
			</div>
			<div class="card-body">
				<p class="mb-2"><strong>Email</strong></p>
				<?php if ( $support_email ) : ?>
					<p class="mb-3">
						<a href="<?php echo esc_url( $support_mailto ); ?>"><?php echo esc_html( antispambot( $support_email ) ); ?></a>
					</p>
				<?php else : ?>
					<p class="text-muted mb-3">No support email is configured for your workspace yet.</p>
				<?php endif; ?>
				<p class="mb-2"><strong>What to include</strong></p>
				<ul class="pl-3 mb-0">
					<li>Lead or quote number</li>
					<li>Customer or company name</li>
					<li>Relevant screenshot or PDF</li>
					<li>Exact steps to reproduce</li>
				</ul>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Common Topics</h3>
			</div>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">Pricing modes and defaults</li>
					<li class="list-group-item">Quote snapshots and requotes</li>
					<li class="list-group-item">Workspace setup</li>
					<li class="list-group-item">Templates, fees, and process settings</li>
				</ul>
			</div>
		</div>
	</div>
</div>
