<div id="post-operations">
	<div class="d-none">
		<?php echo pc_cpq_get_input_html( 'post_ops_order', $Settings ); ?>
	</div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Description</th>
				<th>Setup Time</th>
				<th>Setup Unit</th>
				<th>Cycle Time</th>
				<th>Cycle Unit</th>
				<th>Efficiency</th>
				<th>Type</th>
				<th>Metal/Material</th>
				<th></th>
			</tr>
		</thead>
		<tbody>			
			<?php
				$i = 0;
				foreach ( $Settings->get_Post_Operations() as $Operation ) :
			?>
			<tr data-type="operation" data-index="<?php echo $i; ?>" data-name="<?php echo esc_attr( $Operation->get_operation() ); ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="operation"><?php echo $Operation->get_operation(); ?></td>
				<td data-model="description"><?php echo $Operation->get_truncated_description(); ?></td>
				<td data-model="setupTime"><?php echo $Operation->get_setup_time(); ?></td>
				<td data-model="setupUnit"><?php echo $Operation->get_setup_unit(); ?></td>
				<td data-model="cycleTime"><?php echo $Operation->get_cycle_time(); ?></td>
				<td data-model="cycleUnit"><?php echo $Operation->get_cycle_unit(); ?></td>
				<td data-model="efficiency"><?php echo $Operation->get_efficiency(); ?></td>
				<td data-model="type"><?php echo $Operation->get_type(); ?></td>
				<td data-model="metalMaterial"><?php echo $Operation->get_type() == 'Prep' ? $Operation->get_base_metal_list() : $Operation->get_material(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary js-sortable-handle"><i class="fas fa-arrows-alt"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
</div>