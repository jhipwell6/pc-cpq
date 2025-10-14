<div id="metals">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Density</th>
				<th>Prep Cycle</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Metals() as $Metal ) :
			?>
			<tr data-type="metal" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Metal->get_name(); ?></td>
				<td data-model="density"><?php echo $Metal->get_density(); ?></td>
				<td data-model="prepCycle"><?php echo $Metal->get_prep_cycle(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#metal-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-metal" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="metal-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Metals() as $Metal ) :
				$metal_data = array(
					'Metal' => $Metal,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/metal-modal', array_merge( $data, $metal_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>