<div class="pc-cpq-row">
	<div class="pc-cpq-col">
		<h3>Parts</h3>
	</div>
</div>
<!-- loop -->
<?php
	$quote_pricing_snapshot = $Lead->get_quote_pricing_snapshot();
	$quote_parts_snapshot = $Lead->has_quote_snapshot() ? ( $quote_pricing_snapshot['parts'] ?? array() ) : array();
	$pricing_type = $quote_pricing_snapshot['pricing_type'] ?? $Lead->get_quote_pricing_type();
	$i = 0;
	foreach ( $Lead->get_Parts() as $Part ) :
		$part_snapshot = $quote_parts_snapshot[ $i ] ?? null;
?>
<div class="pc-cpq-row">
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<h3><?php echo $Part->get_file_name(); ?></h3>
		</div>
	</div>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<h4>Plating</h4>
			<table class="wp-list-table widefat pc-cpq-quote-table">
				<tbody>
					<tr>
						<td><strong>Plating Line</strong></td>
						<td><?php echo $Part->get_plating_line(); ?></td>
					</tr>
					<tr>
						<td><strong>Thruput Capacity</strong></td>
						<td><?php echo $Part->get_thruput_capacity('view'); ?></td>
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
						<td><?php echo floor( $Part->get_pieces_per_load('view') ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="pc-cpq-col">
			<h4>Measurements</h4>
			<table class="wp-list-table widefat pc-cpq-quote-table">
				<tbody>
					<tr>
						<td><strong>Single Part Surface Area</strong></td>
						<td><?php echo $Part->get_area('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Total Part Surface Area</strong></td>
						<td><?php echo $Part->get_total_area('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Single Part Volume</strong></td>
						<td><?php echo $Part->get_volume('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Single Part Weight</strong></td>
						<td><?php echo $Part->get_weight('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Total Load Weight</strong></td>
						<td><?php echo $Part->get_load_weight('view'); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<h4>Metal</h4>
			<table class="wp-list-table widefat pc-cpq-quote-table">
				<tbody>
					<tr>
						<td><strong>Base Metal</strong></td>
						<td><?php echo $Part->get_base_metal(); ?></td>
					</tr>
					<tr>
						<td><strong>Metal Density</strong></td>
						<td><?php echo $Part->get_metal_density('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Prep Cycle Time</strong></td>
						<td><?php echo $Part->get_prep_cycle(); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="pc-cpq-col">
			<h4>Totals</h4>
			<table class="wp-list-table widefat pc-cpq-quote-table">
				<tbody>
					<tr>
						<td><strong>Process Time</strong></td>
						<td><?php echo $Part->get_process_time('view'); ?></td>
					</tr>
					<tr>
						<td><strong>Material $ / Unit</strong></td>
						<td><?php echo $part_snapshot['material_cost_formatted'] ?? $Part->get_material_cost('view'); ?></td>
					</tr>
					<?php if ( $Lead->include_metal_factor() && ( ! empty( $part_snapshot['metal_factors'] ) || $Part->has_metal_factors() ) ) : ?>
					<tr>
						<td><strong>Metal Factor(s)</strong></td>
						<td>
							<?php if ( ! empty( $part_snapshot['metal_factors'] ) ) : ?>
								<?php foreach ( $part_snapshot['metal_factors'] as $item ) : ?>
								<?php echo $item['metal'] . ' - ' . $item['factor']; ?><br />
								<?php endforeach; ?>
							<?php else : ?>
								<?php foreach ( $Part->get_metal_factors() as $metal => $factor ) : ?>
								<?php echo $metal . ' - ' . round( floatval( $factor ), 4 ); ?><br />
								<?php endforeach; ?>
							<?php endif; ?>
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
	<?php
		$pricing_rows = array();
		if ( is_array( $part_snapshot ) ) {
			$pricing_rows = $pricing_type === 'commodity'
				? ( $part_snapshot['commodity_rows'] ?? array() )
				: ( $part_snapshot['special_rows'] ?? array() );
		}
		if ( ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) || ! empty( $pricing_rows ) ) :
	?>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<h3>Special Pricing</h3>
			<?php 
				$price_prefix = '';
				include PC_CPQ()->plugin_path() . '/includes/views/admin/partials/pricing-table.php'; 
			?>
		</div>
		<div class="pc-cpq-col">
			<h3>Commodity Pricing</h3>
			<?php 
				$price_prefix = 'base_';
				include PC_CPQ()->plugin_path() . '/includes/views/admin/partials/pricing-table.php'; 
			?>
		</div>
	</div>
	<?php endif; ?>
</div>
<?php $i++; endforeach; ?>
<!-- end loop -->
