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
		$i = 1;
		foreach ( $Lead->get_Parts() as $Part ) :
			if ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) :
	?>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<table class="parts-table<?php $Lead->include_metal_factor() ? ' has-metal-factor' : ''; ?>">
				<thead>
					<tr>
						<th class="pc-cpq-col--item">Item</th>
						<th class="pc-cpq-col--desc">Description</th>
						<th class="pc-cpq-col--qty">Quantities</th>
						<?php if ( $Lead->include_metal_factor() ) : ?><th class="pc-cpq-col--factor">Metal Factor(s)</th><?php endif; ?>
						<th class="pc-cpq-col--price">$ / <?php echo $Part->get_Pricing()->get_price_unit(); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="pc-cpq-col--item"><?php echo $i; ?></td>
						<td class="pc-cpq-col--desc">
							Part #: <?php echo $Part->get_part_number(); ?><br />
							Drawing #: <?php echo $Part->get_drawing_number(); ?><br />
							Revision #: <?php echo $Part->get_revision_number(); ?><br />
							Base Metal: <?php echo $Part->get_base_metal(); ?><br />
							Min Lot Charge: <?php echo to_currency( $Part->get_min_lot_charge() ); ?><br />
							<?php echo $Part->get_Processes( 'view' ); ?>
						</td>
						<td class="pc-cpq-col--qty">
							<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
							<?php echo $Pricing->get_quantity_range(); ?><br />
							<?php endforeach; ?>
						</td>
						<?php if ( $Lead->include_metal_factor() ) : ?>
						<td class="pc-cpq-col--factor">
							<?php if ( $Part->has_metal_factors() ) : ?>
							<?php foreach ( $Part->get_metal_factors() as $metal => $factor ) : ?>
							<?php echo $metal . ' - ' . round( floatval( $factor ), 4 ); ?><br />
							<?php endforeach; ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>
						<td class="pc-cpq-col--price">
							<?php $price_prefix = $pricing_type == 'commodity' ? 'base_' : ''; ?>
							<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
							<?php $getter = "get_{$price_prefix}final_price_per_unit"; echo $Pricing->{$getter}( 'view' ); ?><br />
							<?php endforeach; ?>
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