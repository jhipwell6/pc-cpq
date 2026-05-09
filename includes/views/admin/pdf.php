<?php $layout = $layout ?? 'standard'; ?>
<div class="pc-cpq-pdf pc-cpq-pdf--quote pc-cpq-pdf-layout--<?php echo esc_attr( $layout ); ?>">
<div id="pdf-header">
	<?php echo $header; ?>
</div>
<?php echo PC_CPQ()->view( 'admin/partials/quote-details', $data ); ?>
<br />
<hr />
<br />
<div id="pdf-quote">
	<?php if ( $Lead->get_quote_notes() ) : ?>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<?php echo $Lead->get_quote_notes(); ?>
		</div>
	</div>
	<?php endif; ?>
	<?php 
		$quote_parts_snapshot = $quote_pricing_snapshot['parts'] ?? array();
		$i = 1;
		foreach ( $Lead->get_Parts() as $Part ) :
			$part_snapshot = $quote_parts_snapshot[ $i - 1 ] ?? null;
			if ( ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) || ! empty( $part_snapshot['active_rows'] ) ) :
				$show_metal_factor = $part_snapshot['show_metal_factor'] ?? $Lead->include_metal_factor();
				$price_prefix = $pricing_type == 'commodity' ? 'base_' : '';
	?>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<table class="parts-table<?php echo $show_metal_factor ? ' has-metal-factor' : ''; ?>">
				<thead>
					<tr>
						<th class="pc-cpq-col--item">Item</th>
						<th class="pc-cpq-col--details">Part Details</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="pc-cpq-col--item"><?php echo $i; ?></td>
						<td class="pc-cpq-col--details">
							<table class="parts-table__nested">
								<tbody>
									<tr class="parts-table__nested-head">
										<th class="pc-cpq-col--desc"<?php echo $show_metal_factor ? '' : ' colspan="2"'; ?>>Description</th>
										<?php if ( $show_metal_factor ) : ?>
										<th class="pc-cpq-col--factor">Metal Factor(s)</th>
										<?php endif; ?>
									</tr>
									<tr>
										<td class="pc-cpq-col--desc"<?php echo $show_metal_factor ? '' : ' colspan="2"'; ?>>
											Part #: <?php echo $part_snapshot['part_number'] ?? $Part->get_part_number(); ?><br />
											Drawing #: <?php echo $part_snapshot['drawing_number'] ?? $Part->get_drawing_number(); ?><br />
											Revision #: <?php echo $part_snapshot['revision_number'] ?? $Part->get_revision_number(); ?><br />
											Base Metal: <?php echo $part_snapshot['base_metal'] ?? $Part->get_base_metal(); ?><br />
											Min Lot Charge: <?php echo $part_snapshot['min_lot_charge_formatted'] ?? to_currency( $Part->get_min_lot_charge() ); ?><br />
											<?php echo $part_snapshot['processes_view'] ?? $Part->get_Processes( 'view' ); ?>
										</td>
										<?php if ( $show_metal_factor ) : ?>
										<td class="pc-cpq-col--factor">
											<?php if ( ! empty( $part_snapshot['metal_factors'] ) ) : ?>
												<?php foreach ( $part_snapshot['metal_factors'] as $item ) : ?>
													<?php echo $item['metal'] . ' - ' . $item['factor']; ?><br />
												<?php endforeach; ?>
											<?php elseif ( $Part->has_metal_factors() ) : ?>
												<?php foreach ( $Part->get_metal_factors() as $metal => $factor ) : ?>
													<?php echo $metal . ' - ' . round( floatval( $factor ), 4 ); ?><br />
												<?php endforeach; ?>
											<?php endif; ?>
										</td>
										<?php endif; ?>
									</tr>
									<tr class="parts-table__nested-head">
										<th class="pc-cpq-col--qty">Quantities (<?php echo esc_html( $part_snapshot['quantity_unit_label'] ?? $Part->get_Pricing()->get_quantity_unit_label() ); ?>)</th>
										<th class="pc-cpq-col--price">$ / <?php echo esc_html( $part_snapshot['price_unit_label'] ?? $Part->get_Pricing()->get_price_unit_label() ); ?></th>
									</tr>
									<tr>
										<td class="pc-cpq-col--qty">
											<?php if ( ! empty( $part_snapshot['active_rows'] ) ) : ?>
												<?php foreach ( $part_snapshot['active_rows'] as $row ) : ?>
													<?php echo $row['quantity_range']; ?><br />
												<?php endforeach; ?>
											<?php else : ?>
												<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
													<?php echo $Pricing->get_quantity_range(); ?><br />
												<?php endforeach; ?>
											<?php endif; ?>
										</td>
										<td class="pc-cpq-col--price">
											<?php if ( ! empty( $part_snapshot['active_rows'] ) ) : ?>
												<?php foreach ( $part_snapshot['active_rows'] as $row ) : ?>
													<?php echo $row['final_price_per_unit']; ?><br />
												<?php endforeach; ?>
											<?php else : ?>
												<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
													<?php $getter = "get_{$price_prefix}final_price_per_unit"; echo $Pricing->{$getter}( 'view' ); ?><br />
												<?php endforeach; ?>
											<?php endif; ?>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
			endif;
			$i++; 
		endforeach;
	?>
</div>
<br />
<div id="pdf-footer">
	<?php echo $footer; ?>
</div>
<br />
<hr />
<br />
<div id="pdf-terms">
	<?php echo $terms; ?>
</div>
</div>
