<div class="part-summary">
	<?php /*if ( $Part->get_file() ) : ?>
	<canvas class="step-viewer" data-url="<?php echo esc_attr( $Part->get_file() ); ?>" width="400" height="300"></canvas>
	<?php endif; */?>
	<div class="row">
		<div class="col-6">
			<h4 class="h6 text-uppercase">Plating</h4>
			<table class="table">
				<tbody>
					<tr>
						<td><strong>Plating Line</strong></td>
						<td><?php echo $Part->get_plating_line(); ?></td>
					</tr>
					<tr>
						<td><strong>Thruput Capacity</strong></td>
						<td><?php echo $Part->get_thruput_capacity( 'view' ); ?></td>
					</tr>
					<tr>
						<td><strong>Plating Method</strong></td>
						<td><?php echo $Part->get_plating_method(); ?></td>
					</tr>
					<tr>
						<td><strong>Plating Tool</strong></td>
						<td><?php echo $Part->get_tool(); ?></td>
					</tr>
					<tr>
						<td><strong>Pieces / Load</strong></td>
						<td><?php echo floor( $Part->get_pieces_per_load( 'view' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-6">
			<h4 class="h6 text-uppercase">Measurements</h4>
			<table class="table">
				<tbody>
					<tr>
						<td><strong>Single Part Surface Area</strong></td>
						<td
							data-convertable-text="1"
							data-unit-imperial="ft2"
							data-value-imperial="<?php echo $Part->get_area(); ?>"
							data-unit-metric="mm2"
							data-value-metric="">
								<?php echo $Part->get_area( 'view' ); ?>
						</td>
					</tr>
					<tr>
						<td><strong>Total Part Surface Area</strong></td>
						<td
							data-convertable-text="1"
							data-unit-imperial="ft2"
							data-value-imperial="<?php echo $Part->get_total_area(); ?>"
							data-unit-metric="mm2"
							data-value-metric="">
								<?php echo $Part->get_total_area( 'view' ); ?>
						</td>
					</tr>
					<tr>
						<td><strong>Single Part Volume</strong></td>
						<td
							data-convertable-text="1"
							data-unit-imperial="in3"
							data-value-imperial="<?php echo $Part->get_volume(); ?>"
							data-unit-metric="mm3"
							data-value-metric="">
								<?php echo $Part->get_volume( 'view' ); ?>
						</td>
					</tr>
					<tr>
						<td><strong>Single Part Weight</strong></td>
						<td
							data-convertable-text="1"
							data-unit-imperial="lb"
							data-value-imperial="<?php echo $Part->get_weight(); ?>"
							data-unit-metric="g"
							data-value-metric="">
								<?php echo $Part->get_weight( 'view' ); ?>
						</td>
					</tr>
					<tr>
						<td><strong>Total Load Weight</strong></td>
						<td
							data-convertable-text="1"
							data-unit-imperial="lb"
							data-value-imperial="<?php echo $Part->get_load_weight(); ?>"
							data-unit-metric="g"
							data-value-metric="">
								<?php echo $Part->get_load_weight( 'view' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="col-6">
			<h4 class="h6 text-uppercase mt-3">Metal</h4>
			<table class="table">
				<tbody>
					<tr>
						<td><strong>Base Metal</strong></td>
						<td><?php echo $Part->get_base_metal(); ?></td>
					</tr>
					<tr>
						<td><strong>Metal Density</strong></td>
						<td><?php echo $Part->get_metal_density( 'view' ); ?></td>
					</tr>
					<tr>
						<td><strong>Prep Cycle Time</strong></td>
						<td><?php echo $Part->get_prep_cycle(); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-6">
			<h4 class="h6 text-uppercase mt-3">Totals</h4>
			<table class="table">
				<tbody>
					<tr>
						<td><strong>Process Time</strong></td>
						<td><?php echo $Part->get_process_time( 'view' ); ?></td>
					</tr>
					<tr>
						<td><strong>Material $ / Unit</strong></td>
						<td><?php echo $Part->get_material_cost( 'view' ); ?></td>
					</tr>
					<?php if ( $Lead->include_metal_factor() && $Part->has_metal_factors() ) : ?>
					<tr>
						<td><strong>Metal Factor(s)</strong></td>
						<td>
							<?php foreach ( $Part->get_metal_factors() as $metal => $factor ) : ?>
							<?php echo $metal . ' - ' . round( floatval( $factor ), 4 ); ?><br />
							<?php endforeach; ?>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><strong>Pieces / Hour</strong></td>
						<td><?php echo $Part->get_pieces_per_hour( 'view' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) : ?>
	<div class="row">
		<div class="col-6">
			<h4 class="h6 text-uppercase mt-3">Special Pricing</h4>
			<?php 
				$price_prefix = '';
				include PC_CPQ()->plugin_path() . '/includes/views/manage/partials/part-pricing-table.php'; 
			?>
		</div>
		<div class="col-6">
			<h4 class="h6 text-uppercase mt-3">Commodity Pricing</h4>
			<?php 
				$price_prefix = 'base_';
				include PC_CPQ()->plugin_path() . '/includes/views/manage/partials/part-pricing-table.php'; 
			?>
		</div>
	</div>
	<?php endif; ?>
</div>