<table class="table table-striped table-collapsing mb-0">
	<thead>
		<tr>
			<th style="width: 10px">#</th>
			<th>Process</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$p = 0;
		foreach ( $Part->get_Processes() as $Process ) :
			?>
			<tr data-type="process" data-index="<?php echo $p; ?>" data-part-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $p + 1; ?>.</td>
				<td data-model="metal"><?php echo $Process->get_metal(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#manage-part-<?php echo $i; ?>-process-details-<?php echo $p; ?>" aria-expanded="false" aria-controls="manage-part-<?php echo $i; ?>-process-details-<?php echo $p; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-part-process" data-index="<?php echo $p; ?>" data-part-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<tr class="collapse" id="manage-part-<?php echo $i; ?>-process-details-<?php echo $p; ?>">
				<td colspan="4">
					<div class="p-4">
						<?php
						echo pc_cpq_get_input_html( 'metal', $Process, [ $i, $p ] );
						echo pc_cpq_get_input_html( 'specification', $Process, [ $i, $p ] );
						echo pc_cpq_get_input_html( 'min_thickness', $Process, [ $i, $p ] );
						echo pc_cpq_get_input_html( 'max_thickness', $Process, [ $i, $p ] );
						echo pc_cpq_get_input_html( 'unit', $Process, [ $i, $p ] );
						// echo spc_get_input_html( 'time', $Process, [ $i, $p ] );
						?>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">Time</label>
							<div class="col-sm-10">
								<div class="input-group">
									<div class="col-form-label" data-model="time"><?php echo $Process->get_time(); ?></div>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<?php
			$p ++;
		endforeach;
		?>
	</tbody>
</table>
<div class="p-4">
	<button type="button" class="js-add-part-process btn btn-primary btn-sm">Add Process</button>
</div>