<table class="wp-list-table widefat pc-cpq-quote-table pc-cpq-quote-pricing-table" style="width: 100%;">
	<thead style="text-align: left;">
		<tr>
			<th style="text-align: left;">QUANTITIES (<?php echo esc_html( $part_snapshot['quantity_unit_label'] ?? $Part->get_Pricing()->get_quantity_unit_label() ); ?>)</th>
			<th style="text-align: left;">$ / Ea</th>
			<th style="text-align: left;">Tll $ / Ea</th>
			<th style="text-align: left;">$ / <?php echo esc_html( $part_snapshot['price_unit_label'] ?? $Part->get_Pricing()->get_price_unit_label() ); ?></th>
			<th style="text-align: left;">Time</th>
		</tr>
	</thead>
	<tbody>
		<?php if ( ! empty( $pricing_rows ) ) : ?>
			<?php foreach ( $pricing_rows as $row ) : ?>
			<tr>
				<td><strong><?php echo $row['quantity_range']; ?></strong></td>
				<td><?php echo $row['cost_per_unit']; ?></td>
				<td><?php echo $row['price_per_unit']; ?></td>
				<td><?php echo $row['final_price_per_unit']; ?></td>
				<td><?php echo $row['total_time']; ?></td>
			</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
			<tr>
				<td><strong><?php echo $Pricing->get_quantity_range(); ?></strong></td>
				<td><?php $getter = "get_{$price_prefix}cost_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
				<td><?php $getter = "get_{$price_prefix}price_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
				<td><?php $getter = "get_{$price_prefix}final_price_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
				<td><?php echo $Pricing->get_total_time('view'); ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
