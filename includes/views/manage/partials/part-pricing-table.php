<table class="table" style="width: 100%;">
	<thead style="text-align: left;">
		<tr>
			<th style="text-align: left;">QUANTITIES (<?php echo esc_html( $Part->get_Pricing()->get_quantity_unit_label() ); ?>)</th>
			<th style="text-align: left;">$ / Ea</th>
			<th style="text-align: left;">Ttl $ / Ea</th>
			<th style="text-align: left;">$ / <?php echo esc_html( $Part->get_Pricing()->get_price_unit_label() ); ?></th>
			<th style="text-align: left;">Time</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $Part->get_Pricing_Model() as $Pricing ) : ?>
		<tr>
			<td><strong><?php echo $Pricing->get_quantity_range(); ?></strong></td>
			<td><?php $getter = "get_{$price_prefix}cost_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
			<td><?php $getter = "get_{$price_prefix}price_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
			<td><?php $getter = "get_{$price_prefix}final_price_per_unit"; echo $Pricing->{$getter}('view'); ?></td>
			<td><?php echo $Pricing->get_total_time('view'); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
