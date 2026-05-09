<div class="part-summary">
	<?php
		$quote_pricing_snapshot = $Lead->get_quote_pricing_snapshot();
		$pricing_type = $quote_pricing_snapshot['pricing_type'] ?? $Lead->get_quote_pricing_type();
		$pricing_rows = array();
		if ( is_array( $part_snapshot ?? null ) ) {
			$pricing_rows = $pricing_type === 'commodity'
				? ( $part_snapshot['commodity_rows'] ?? array() )
				: ( $part_snapshot['special_rows'] ?? array() );
		}
	?>
	<?php if ( $Part->is_step_file() ) : ?>
		<div class="part-model-viewer mb-3">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<h4 class="h6 text-uppercase mb-0">3D Model</h4>
				<a href="<?php echo esc_url( $Part->get_file() ); ?>" class="btn btn-xs btn-outline-secondary" target="_blank" rel="noopener noreferrer">Open Original File</a>
			</div>
			<div class="part-model-viewer__frame">
				<canvas
					class="step-viewer"
					data-url="<?php echo esc_url( $Part->get_file() ); ?>"
					data-file-name="<?php echo esc_attr( $Part->get_file_name() ); ?>"
					width="720"
					height="420"></canvas>
				<div class="part-model-viewer__status" aria-live="polite">Loading model...</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $Part->is_pdf_file() ) : ?>
		<div class="part-model-viewer mb-3">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<h4 class="h6 text-uppercase mb-0">PDF Preview</h4>
				<div class="btn-group btn-group-sm" role="group">
					<button
						type="button"
						class="btn btn-outline-secondary js-open-file-preview"
						data-file-type="pdf"
						data-file-url="<?php echo esc_url( $Part->get_file() ); ?>"
						data-file-name="<?php echo esc_attr( $Part->get_file_name() ?: 'PDF Preview' ); ?>">
						Expand
					</button>
					<a href="<?php echo esc_url( $Part->get_file() ); ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">Open Original File</a>
				</div>
			</div>
			<div class="part-model-viewer__frame part-model-viewer__frame--pdf">
				<iframe
					class="part-pdf-viewer"
					data-pdf-url="<?php echo esc_url( $Part->get_file() ); ?>"
					title="<?php echo esc_attr( sprintf( '%s PDF preview', $Part->get_file_name() ?: 'Part' ) ); ?>"
					loading="lazy"></iframe>
				<div class="part-model-viewer__status" aria-live="polite">Loading PDF preview...</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $Part->is_image_file() ) : ?>
		<div class="part-model-viewer mb-3">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<h4 class="h6 text-uppercase mb-0">Image Preview</h4>
				<div class="btn-group btn-group-sm" role="group">
					<button
						type="button"
						class="btn btn-outline-secondary js-open-file-preview"
						data-file-type="image"
						data-file-url="<?php echo esc_url( $Part->get_file() ); ?>"
						data-file-name="<?php echo esc_attr( $Part->get_file_name() ?: 'Image Preview' ); ?>">
						Expand
					</button>
					<a href="<?php echo esc_url( $Part->get_file() ); ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">Open Original File</a>
				</div>
			</div>
			<div class="part-model-viewer__frame part-model-viewer__frame--image">
				<img
					class="part-image-viewer"
					data-image-url="<?php echo esc_url( $Part->get_file() ); ?>"
					alt="<?php echo esc_attr( sprintf( '%s image preview', $Part->get_file_name() ?: 'Part' ) ); ?>"
					loading="lazy" />
				<div class="part-model-viewer__status" aria-live="polite">Loading image preview...</div>
			</div>
		</div>
	<?php endif; ?>
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
						<td><?php echo $part_snapshot['material_cost_formatted'] ?? $Part->get_material_cost( 'view' ); ?></td>
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
	<?php if ( ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) || ! empty( $pricing_rows ) ) : ?>
	<div class="row">
		<div class="col-12">
			<h4 class="h6 text-uppercase mt-3">Special Pricing</h4>
			<?php 
				$price_prefix = '';
				include PC_CPQ()->plugin_path() . '/includes/views/manage/partials/part-pricing-table.php'; 
			?>
		</div>
		<div class="col-12">
			<h4 class="h6 text-uppercase mt-3">Commodity Pricing</h4>
			<?php 
				$price_prefix = 'base_';
				include PC_CPQ()->plugin_path() . '/includes/views/manage/partials/part-pricing-table.php'; 
			?>
		</div>
	</div>
	<?php endif; ?>
</div>
