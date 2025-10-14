<div id="plating-metals">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Density</th>
				<th>Cost / Unit</th>
				<th>Rate</th>
				<th>Unit Type</th>
				<th>Unit Visible</th>
				<th>Min Lot Charge</th>
				<th>Precious Metal?</th>
				<th>Hide?</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Plating_Metals() as $Plating_Metal ) :
			?>
			<tr data-type="plating_metal" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Plating_Metal->get_name(); ?></td>
				<td data-model="density"><?php echo $Plating_Metal->get_density(); ?></td>
				<td data-model="cost"><?php echo $Plating_Metal->get_cost(); ?></td>
				<td data-model="depositRate"><?php echo $Plating_Metal->get_deposit_rate(); ?></td>
				<td data-model="unitType"><?php echo $Plating_Metal->get_unit_type(); ?></td>
				<td data-model="unitVisible"><?php echo $Plating_Metal->get_unit_visible() == 1 ? 'Yes' : '-'; ?></td>
				<td data-model="minLotCharge"><?php echo $Plating_Metal->get_min_lot_charge(); ?></td>
				<td data-model="preciousMetal"><?php echo $Plating_Metal->get_precious_metal() == 1 ? 'Yes' : '-'; ?></td>
				<td data-model="hide"><?php echo $Plating_Metal->get_hide() == 1 ? 'Yes' : '-'; ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#plating-metal-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-plating-metal" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="plating_metal-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Plating_Metals() as $Plating_Metal ) :
				$plating_metal_data = array(
					'Plating_Metal' => $Plating_Metal,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/plating-metal-modal', array_merge( $data, $plating_metal_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>