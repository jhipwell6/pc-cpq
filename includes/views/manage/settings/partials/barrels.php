<div id="barrels">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Size Limit</th>
				<th>Ft<sup>2</sup> / Load</th>
				<th>Weight Limit</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Barrels() as $Barrel ) :
			?>
			<tr data-type="barrel" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Barrel->get_name(); ?></td>
				<td data-model="sizeLimit"><?php echo $Barrel->get_size_limit(); ?></td>
				<td data-model="ft2Load"><?php echo $Barrel->get_ft2_load(); ?></td>
				<td data-model="weightLimit"><?php echo $Barrel->get_weight_limit(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#barrel-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-barrel" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="barrel-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Barrels() as $Barrel ) :
				$barrel_data = array(
					'Barrel' => $Barrel,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/barrel-modal', array_merge( $data, $barrel_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>