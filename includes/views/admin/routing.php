<?php $layout = $layout ?? 'standard'; ?>
<div class="pc-cpq-pdf pc-cpq-pdf--routing pc-cpq-pdf-layout--<?php echo esc_attr( $layout ); ?>">
<div id="pdf-header">
	<?php echo $header; ?>
</div>
<br />
<hr />
<br />
<div id="pdf-routing">
	<?php 
		$quote_pricing_snapshot = $quote_pricing_snapshot ?? $Lead->get_quote_pricing_snapshot();
		$quote_parts_snapshot = $Lead->has_quote_snapshot() ? ( $quote_pricing_snapshot['parts'] ?? array() ) : array();
		$post_operations_snapshot = $Lead->has_quote_snapshot() ? ( $quote_pricing_snapshot['post_operations'] ?? array() ) : array();
		$i = 1;
		foreach ( $Lead->get_Parts() as $Part ) :
			$part_snapshot = $quote_parts_snapshot[ $i - 1 ] ?? null;
			if ( ( is_array( $Part->get_Pricing_Model() ) && ! empty( $Part->get_Pricing_Model() ) ) || ! empty( $part_snapshot ) ) :
	?>
	<div class="pc-cpq-row">
		<div class="pc-cpq-col">
			<table class="wp-list-table widefat pc-cpq-quote-table">
				<tbody>
					<tr>
						<td style="vertical-align: top;">
							<table>
								<tbody>
									<?php
										$table_data = array(
											'Part No'		=> $part_snapshot['part_number'] ?? $Part->get_part_number(),
											'Description'	=> $part_snapshot['processes_view'] ?? $Part->get_Processes('view'),
											'Customer'		=> $Lead->get_company(),
											'Quote No'		=> $Lead->get_quote_number(),
										);
										foreach ( $table_data as $label => $value ) : 
									?>
									<tr>
										<td>
											<strong><?php echo $label; ?>: </strong>
										</td>
										<td>
											<?php echo $value; ?>
										</td>
									</tr>
									<?php endforeach; ?>
									<tr>
										<td><strong>Plating Line</strong></td>
										<td><?php echo $part_snapshot['plating_line'] ?? $Part->get_plating_line(); ?></td>
									</tr>
									<tr>
										<td><strong>Thruput Capacity</strong></td>
										<td><?php echo $part_snapshot['thruput_capacity_view'] ?? $Part->get_thruput_capacity('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Base Metal</strong></td>
										<td><?php echo $part_snapshot['base_metal'] ?? $Part->get_base_metal(); ?></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="vertical-align: top;">
							<table>
								<tbody>
									<tr>
										<td><strong>Plating Method</strong></td>
										<td><?php echo $part_snapshot['plating_method'] ?? $Part->get_plating_method(); ?></td>
									</tr>
									<tr>
										<td><strong>Plating Tool</strong></td>
										<td><?php echo $part_snapshot['tool'] ?? $Part->get_tool(); ?></td>
									</tr>
									<tr>
										<td><strong>Pieces / Load</strong></td>
										<td><?php echo $part_snapshot['pieces_per_load_view'] ?? $Part->get_pieces_per_load('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Single Part Surface Area</strong></td>
										<td><?php echo $part_snapshot['area_view'] ?? $Part->get_area('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Total Part Surface Area</strong></td>
										<td><?php echo $part_snapshot['total_area_view'] ?? $Part->get_total_area('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Total Part Volume</strong></td>
										<td><?php echo $part_snapshot['volume_view'] ?? $Part->get_volume('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Single Part Weight</strong></td>
										<td><?php echo $part_snapshot['weight_view'] ?? $Part->get_weight('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Total Load Weight</strong></td>
										<td><?php echo $part_snapshot['load_weight_view'] ?? $Part->get_load_weight('view'); ?></td>
									</tr>
									<tr>
										<td><strong>Total Metal Consumption</strong></td>
										<td><?php echo $part_snapshot['metal_consumption_view'] ?? $Part->get_metal_consumption('view'); ?></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="pc-cpq-row pc-cpq-table--ops-row">
		<div class="pc-cpq-col">
			<table class="pc-cpq-table--ops">
				<thead>
					<tr>
						<th>Operation</th>
						<th>Description</th>
						<th>Time</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $part_snapshot['operations'][0] ) ) : ?>
					<tr>
						<td><?php echo $part_snapshot['operations'][0]['metal']; ?></td>
						<td><?php echo $part_snapshot['operations'][0]['operation']; ?><br /><?php echo $part_snapshot['operations'][0]['description']; ?></td>
						<td><?php echo $part_snapshot['operations'][0]['time']; ?></td>
					</tr>
					<?php else : ?>
						<?php foreach ( $Part->get_Operations() as $Operation ) : ?>
						<tr>
							<td><?php echo $Operation->get_metal(); ?></td>
							<td><?php echo $Operation->get_operation(); ?><br /><?php echo $Operation->get_description(); ?></td>
							<td><?php echo $Operation->get_time(); ?></td>
						</tr>
						<?php break; endforeach; ?>
					<?php endif; ?>
					<?php if ( ! empty( $part_snapshot['processes'] ) ) : ?>
						<?php foreach ( $part_snapshot['processes'] as $Process ) : ?>
						<tr>
							<td><?php echo $Process['metal']; ?></td>
							<td><?php echo $Process['description']; ?></td>
							<td><?php echo $Process['time']; ?></td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<?php foreach ( $Part->get_Processes() as $Process ) : ?>
						<tr>
							<td><?php echo $Process->get_metal(); ?></td>
							<td><?php echo $Process->get_description(); ?></td>
							<td><?php echo $Process->get_time(); ?></td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( ! empty( $post_operations_snapshot ) ) : ?>
						<?php foreach ( $post_operations_snapshot as $Post_Operation ) : ?>
						<tr>
							<td><?php echo $Post_Operation['operation']; ?></td>
							<td><?php echo $Post_Operation['description']; ?></td>
							<td><?php echo $Post_Operation['cycle_time']; ?></td>
						</tr>
						<?php endforeach; ?>
					<?php elseif ( ! empty( PC_CPQ()->Settings()->get_Post_Operations() ) ) : ?>
						<?php foreach ( PC_CPQ()->Settings()->get_Post_Operations() as $Post_Operation ) : ?>
						<tr>
							<td><?php echo $Post_Operation->get_operation(); ?></td>
							<td><?php echo $Post_Operation->get_description(); ?></td>
							<td><?php echo $Post_Operation->get_cycle_time(); ?></td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<pagebreak/>
	<?php
			endif;
			$i++; 
		endforeach;
	?>
</div>
</div>
