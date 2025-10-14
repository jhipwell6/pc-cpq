<div id="racks">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Size Limit</th>
				<th>Weight Limit</th>
				<th>Piece Count</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Racks() as $Rack ) :
			?>
			<tr data-type="rack" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Rack->get_name(); ?></td>
				<td data-model="sizeLimit"><?php echo $Rack->get_size_limit(); ?></td>
				<td data-model="weightLimit"><?php echo $Rack->get_weight_limit(); ?></td>
				<td data-model="pieceCount"><?php echo $Rack->get_piece_count(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#rack-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-rack" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="rack-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Racks() as $Rack ) :
				$rack_data = array(
					'Rack' => $Rack,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/rack-modal', array_merge( $data, $rack_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>