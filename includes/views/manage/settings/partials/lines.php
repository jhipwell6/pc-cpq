<div id="lines">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Plate Cells</th>
				<th>Max Pulls / Hr</th>
				<th>Barrel Size Limit</th>
				<th>Rack Size Limit</th>
				<th>Rack Factor</th>
				<th>Weight Limit</th>
				<th>Rack Ld Max In<sup>2</sup></th>
				<th></th>
			</tr>
		</thead>
		<tbody>			
			<?php
				$i = 0;
				foreach ( $Settings->get_Lines() as $Line ) :
			?>
			<tr data-type="line" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Line->get_name(); ?></td>
				<td data-model="plateCells"><?php echo $Line->get_plate_cells(); ?></td>
				<td data-model="maxPullsPerHour"><?php echo $Line->get_max_pulls_per_hour(); ?></td>
				<td data-model="barrelSizeLimit"><?php echo $Line->get_barrel_size_limit(); ?></td>
				<td data-model="rackSizeLimit"><?php echo $Line->get_rack_size_limit(); ?></td>
				<td data-model="rackFactor"><?php echo $Line->get_rack_factor(); ?></td>
				<td data-model="weightLimit"><?php echo $Line->get_weight_limit(); ?></td>
				<td data-model="rackLdMaxIn2"><?php echo $Line->get_rack_ld_max_in2(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#line-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-line" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="line-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Lines() as $Line ) :
				$line_data = array(
					'Line' => $Line,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/line-modal', array_merge( $data, $line_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>