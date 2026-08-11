<div class="p-4">
	<?php echo pc_cpq_get_input_html( 'plating_line', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_method', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_tool_barrel', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_tool_rack', $Part, $i ); ?>
</div>
<div class="card-header p-0">
	<h3 class="card-title p-3">Operations</h3>
</div>
<table class="table table-striped table-collapsing mb-0 js-part-operations-table">
	<thead>
		<tr>
			<th style="width: 40px"></th>
			<th style="width: 10px">#</th>
			<th style="width: 120px">Type</th>
			<th>Operation</th>
			<th></th>
		</tr>
	</thead>
	<tbody data-part-index="<?php echo $i; ?>">
		<?php
		$r = 0;
		$process_index = 0;
		foreach ( $Part->get_Operations() as $Operation ) :
			$is_sortable = 'Plating' === $Operation->get_type();
			?>
			<tr data-type="routing" data-index="<?php echo $r; ?>" data-sortable="<?php echo $is_sortable ? 1 : 0; ?>" data-process-index="<?php echo $is_sortable ? $process_index : ''; ?>" data-detail-id="manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>">
				<td class="text-center align-middle js-operation-sort-handle" title="<?php echo $is_sortable ? 'Drag to reorder' : ''; ?>" style="cursor: <?php echo $is_sortable ? 'move' : 'default'; ?>;">
					<?php if ( $is_sortable ) : ?>
						<i class="fas fa-grip-vertical text-muted"></i>
					<?php endif; ?>
				</td>
				<td style="width: 10px"><?php echo $r + 1; ?>.</td>
				<td data-model="type"><?php echo $Operation->get_type(); ?></td>
				<td data-model="metal"><?php echo $Operation->get_operation(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>" aria-expanded="false" aria-controls="manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>"><i class="fas fa-eye"></i></button>
						<!--<button type="button" class="btn btn-danger js-delete-part-operation" data-index="<?php echo $r; ?>" data-part-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>-->
					</div>
				</td>
			</tr>
			<tr class="collapse js-operation-detail-row" id="manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>" data-index="<?php echo $r; ?>" data-part-index="<?php echo $i; ?>" data-sortable="<?php echo $is_sortable ? 1 : 0; ?>" data-process-index="<?php echo $is_sortable ? $process_index : ''; ?>">
				<td colspan="5">
					<div class="part-modal-edit-row p-4 js-part-operation" data-index="<?php echo $r; ?>" data-part-index="<?php echo $i; ?>" data-process-index="<?php echo $is_sortable ? $process_index : ''; ?>">
						<?php // echo pc_cpq_get_input_html( 'operation', $Operation, [ $i, $r ] ); ?>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">Type</label>
							<div class="col-sm-10">
								<div class="input-group">
									<div class="col-form-label" data-model="operation_type"><?php echo $Operation->get_type(); ?></div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">Operation</label>
							<div class="col-sm-10">
								<div class="input-group">
									<div class="col-form-label" data-model="operation"><?php echo $Operation->get_operation(); ?></div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">Description</label>
							<div class="col-sm-10">
								<div class="input-group">
									<div class="col-form-label" data-model="operation_description"><?php echo $Operation->get_description(); ?></div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-2 col-form-label">Time</label>
							<div class="col-sm-10">
								<div class="input-group">
									<div class="col-form-label" data-model="operation_time"><?php echo $Operation->get_time(); ?></div>
								</div>
							</div>
						</div>
						<?php // echo spc_get_input_html( 'time', $Operation, [ $i, $r ] ); ?>
					</div>
				</td>
			</tr>
			<?php
			if ( $is_sortable ) :
				$process_index ++;
			endif;
			$r ++;
		endforeach;
		?>
		<?php if ( ! empty( PC_CPQ()->Settings()->get_Post_Operations() ) ) : ?>
			<?php foreach ( PC_CPQ()->Settings()->get_Post_Operations() as $Post_Operation ) : ?>
			<tr data-type="routing" data-index="<?php echo $r; ?>" data-sortable="0">
				<td></td>
				<td style="width: 10px"><?php echo $r + 1; ?>.</td>
				<td data-model="type"><?php echo $Post_Operation->get_type(); ?></td>
				<td data-model="metal"><?php echo $Post_Operation->get_operation(); ?></td>
				<td class="text-right py-0 align-middle">
					
				</td>
			</tr>
			<?php $r ++; endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<!--<div class="p-4">
	<button type="button" class="js-add-part-operation btn btn-primary btn-sm">Add Operation</button>
</div>-->
<div class="p-4">
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Total Time</label>
		<div class="col-sm-10">
			<div class="input-group">
				<p class="col-form-label" data-model="part_total_operation_time" data-index="<?php echo $i; ?>"><?php echo $Part->get_total_operation_time(); ?> hrs.</p>
			</div>
		</div>
	</div>
</div>
