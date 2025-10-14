<div class="p-4">
	<?php echo pc_cpq_get_input_html( 'plating_line', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_method', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_tool_barrel', $Part, $i ); ?>
	<?php echo pc_cpq_get_input_html( 'plating_tool_rack', $Part, $i ); ?>
</div>
<div class="card-header p-0">
	<h3 class="card-title p-3">Operations</h3>
</div>
<table class="table table-striped table-collapsing mb-0">
	<thead>
		<tr>
			<th style="width: 10px">#</th>
			<th>Operation</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$r = 0;
		foreach ( $Part->get_Operations() as $Operation ) :
			?>
			<tr data-type="routing" data-index="<?php echo $r; ?>">
				<td style="width: 10px"><?php echo $r + 1; ?>.</td>
				<td data-model="metal"><?php echo $Operation->get_operation(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>" aria-expanded="false" aria-controls="manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>"><i class="fas fa-eye"></i></button>
						<!--<button type="button" class="btn btn-danger js-delete-part-operation" data-index="<?php echo $r; ?>" data-part-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>-->
					</div>
				</td>
			</tr>
			<tr class="collapse" id="manage-part-<?php echo $i; ?>-routing-details-<?php echo $r; ?>">
				<td colspan="4">
					<div class="part-modal-edit-row p-4 js-part-operation" data-index="<?php echo $r; ?>" data-part-index="<?php echo $i; ?>">
						<?php // echo pc_cpq_get_input_html( 'operation', $Operation, [ $i, $r ] ); ?>
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
			$r ++;
		endforeach;
		?>
		<?php if ( ! empty( PC_CPQ()->Settings()->get_Post_Operations() ) ) : ?>
			<?php foreach ( PC_CPQ()->Settings()->get_Post_Operations() as $Post_Operation ) : ?>
			<tr data-type="routing" data-index="<?php echo $r; ?>">
				<td style="width: 10px"><?php echo $r + 1; ?>.</td>
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