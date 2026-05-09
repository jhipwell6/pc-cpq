<div id="edit-settings-parts">
	<form action="" method="post" class="js-edit-settings-parts-form">
		<div class="row">
			<div class="col-md-6">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Price Default Settings</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php echo pc_cpq_get_input_html( 'default_pricing_mode', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'hourly_rate', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_margin', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_eff', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_people', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_eau', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_shift', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_break_in', $Settings ); ?>
						<?php echo pc_cpq_get_input_html( 'default_metal_adder', $Settings ); ?>
					</div>
					<!-- /.card-body -->
				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">How Pricing Works</h3>
					</div>
					<div class="card-body">
						<p class="mb-3">These defaults fill new leads and parts. They set the starting assumptions for labor, utilization, markup, and setup cost before part-specific routing and material details are applied.</p>
						<div class="border-top pt-3">
							<h4 class="h6 mb-2">Utilization</h4>
							<p class="mb-2"><strong>Starts with:</strong> hourly rate, efficiency, people, and shifts.</p>
							<p class="mb-2"><strong>Then adjusts for:</strong> how fully the line is utilized at the quoted quantity.</p>
							<p class="mb-0"><strong>Then adds:</strong> material cost, break-in per unit, and the selected pricing unit conversion.</p>
						</div>
						<div class="border-top pt-3 mt-3">
							<h4 class="h6 mb-2">Cost Plus</h4>
							<p class="mb-2"><strong>Starts with:</strong> labor cost based on hourly rate, people, efficiency, and total production time.</p>
							<p class="mb-2"><strong>Then adds:</strong> material cost and break-in per unit.</p>
							<p class="mb-0"><strong>Then marks up:</strong> the total unit cost by your margin before unit conversion.</p>
						</div>
						<div class="border-top pt-3 mt-3">
							<h4 class="h6 mb-2">What These Defaults Control</h4>
							<ul class="mb-0 pl-3">
								<li><strong>Hourly Rate</strong>, <strong>EFF</strong>, and <strong># People</strong> shape labor cost.</li>
								<li><strong>Margin</strong> raises the selling price above cost.</li>
								<li><strong># of Shifts</strong> matters most in utilization pricing because it changes available capacity.</li>
								<li><strong>Break In</strong> is spread across the quoted quantity.</li>
								<li><strong>Metal Adder</strong> fills a default metal-related pricing input for new work.</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-2 ml-auto">
				<?php echo PC_CPQ()->view( 'manage/partials/save-alerts' ); ?>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Save</h3>
					</div>
					<div class="card-body">
						<?php wp_nonce_field( 'edit_settings_parts', 'edit_settings_parts_nonce' ); ?>
						<input type="submit" value="Save Changes" class="btn btn-success float-right js-edit-settings-parts-submit">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
