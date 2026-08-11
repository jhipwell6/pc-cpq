<?php
$all_operations = $Settings->get_Operations();
$pre_operations = array_values( array_filter( $all_operations, function( $Operation ) {
	return 'Pre' === $Operation->get_type();
} ) );
$prep_operations = array_values( array_filter( $all_operations, function( $Operation ) {
	return 'Prep' === $Operation->get_type();
} ) );
$plating_operations = array_values( array_filter( $all_operations, function( $Operation ) {
	return 'Plating' === $Operation->get_type();
} ) );
$post_operations = array_values( array_filter( $all_operations, function( $Operation ) {
	return 'Post' === $Operation->get_type();
} ) );
?>
<div id="operations">
	<div class="tab-content">
		<div class="tab-pane active" id="process_operations_pre">
			<div class="tab-header">
				<div class="card-tools">
					<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-operations" title="Import Operations">
						<i class="fas fa-file-import"></i> Import Operations
					</button>
					<button type="button" class="js-export-settings btn btn-tool" title="Export Operations" data-type="operations">
						<i class="fas fa-file-export"></i> Export Operations
					</button>
				</div>
			</div>
			<table class="table table-striped mb-0">
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
						<th>Metal/Material</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pre_operations as $display_index => $Operation ) : ?>
						<tr data-type="operation" data-index="<?php echo $Operation->get_id(); ?>">
							<td style="width: 10px"><?php echo $display_index + 1; ?>.</td>
							<td data-model="operation"><?php echo $Operation->get_operation(); ?></td>
							<td data-model="description"><?php echo $Operation->get_truncated_description(); ?></td>
							<td data-model="setupTime"><?php echo $Operation->get_setup_time(); ?></td>
							<td data-model="setupUnit"><?php echo $Operation->get_setup_unit(); ?></td>
							<td data-model="cycleTime"><?php echo $Operation->get_cycle_time(); ?></td>
							<td data-model="cycleUnit"><?php echo $Operation->get_cycle_unit(); ?></td>
							<td data-model="efficiency"><?php echo $Operation->get_efficiency(); ?></td>
							<td data-model="metalMaterial"><?php echo $Operation->get_prep_match_label(); ?></td>
							<td class="text-right py-0 align-middle">
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#operation-modal-<?php echo $Operation->get_id(); ?>"><i class="fas fa-edit"></i></button>
									<button type="button" class="btn btn-danger js-delete-operation" data-index="<?php echo $Operation->get_id(); ?>"><i class="fas fa-trash"></i></button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="process_operations_prep">
			<div class="tab-header">
				<div class="card-tools">
					<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-operations" title="Import Operations">
						<i class="fas fa-file-import"></i> Import Operations
					</button>
					<button type="button" class="js-export-settings btn btn-tool" title="Export Operations" data-type="operations">
						<i class="fas fa-file-export"></i> Export Operations
					</button>
				</div>
			</div>
			<table class="table table-striped mb-0">
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
						<th>Metal/Material</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $prep_operations as $display_index => $Operation ) : ?>
						<tr data-type="operation" data-index="<?php echo $Operation->get_id(); ?>">
							<td style="width: 10px"><?php echo $display_index + 1; ?>.</td>
							<td data-model="operation"><?php echo $Operation->get_operation(); ?></td>
							<td data-model="description"><?php echo $Operation->get_truncated_description(); ?></td>
							<td data-model="setupTime"><?php echo $Operation->get_setup_time(); ?></td>
							<td data-model="setupUnit"><?php echo $Operation->get_setup_unit(); ?></td>
							<td data-model="cycleTime"><?php echo $Operation->get_cycle_time(); ?></td>
							<td data-model="cycleUnit"><?php echo $Operation->get_cycle_unit(); ?></td>
							<td data-model="efficiency"><?php echo $Operation->get_efficiency(); ?></td>
							<td data-model="metalMaterial"><?php echo $Operation->get_prep_match_label(); ?></td>
							<td class="text-right py-0 align-middle">
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#operation-modal-<?php echo $Operation->get_id(); ?>"><i class="fas fa-edit"></i></button>
									<button type="button" class="btn btn-danger js-delete-operation" data-index="<?php echo $Operation->get_id(); ?>"><i class="fas fa-trash"></i></button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="process_operations_plating">
			<div class="tab-header">
				<div class="card-tools">
					<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-operations" title="Import Operations">
						<i class="fas fa-file-import"></i> Import Operations
					</button>
					<button type="button" class="js-export-settings btn btn-tool" title="Export Operations" data-type="operations">
						<i class="fas fa-file-export"></i> Export Operations
					</button>
				</div>
			</div>
			<table class="table table-striped mb-0">
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
						<th>Metal/Material</th>
						<th>Plating Method</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $plating_operations as $display_index => $Operation ) : ?>
						<tr data-type="operation" data-index="<?php echo $Operation->get_id(); ?>">
							<td style="width: 10px"><?php echo $display_index + 1; ?>.</td>
							<td data-model="operation"><?php echo $Operation->get_operation(); ?></td>
							<td data-model="description"><?php echo $Operation->get_truncated_description(); ?></td>
							<td data-model="setupTime"><?php echo $Operation->get_setup_time(); ?></td>
							<td data-model="setupUnit"><?php echo $Operation->get_setup_unit(); ?></td>
							<td data-model="cycleTime"><?php echo $Operation->get_cycle_time(); ?></td>
							<td data-model="cycleUnit"><?php echo $Operation->get_cycle_unit(); ?></td>
							<td data-model="efficiency"><?php echo $Operation->get_efficiency(); ?></td>
							<td data-model="metalMaterial"><?php echo $Operation->get_material_list(); ?></td>
							<td data-model="platingMethod"><?php echo $Operation->get_plating_method(); ?></td>
							<td class="text-right py-0 align-middle">
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#operation-modal-<?php echo $Operation->get_id(); ?>"><i class="fas fa-edit"></i></button>
									<button type="button" class="btn btn-danger js-delete-operation" data-index="<?php echo $Operation->get_id(); ?>"><i class="fas fa-trash"></i></button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="process_operations_post">
			<div class="tab-header">
				<div class="card-tools">
					<button type="button" class="btn btn-tool" data-toggle="modal" data-target="#import-settings-modal-operations" title="Import Operations">
						<i class="fas fa-file-import"></i> Import Operations
					</button>
					<button type="button" class="js-export-settings btn btn-tool" title="Export Operations" data-type="operations">
						<i class="fas fa-file-export"></i> Export Operations
					</button>
				</div>
			</div>
			<div id="post-operations">
				<div class="d-none">
					<?php echo pc_cpq_get_input_html( 'post_ops_order', $Settings ); ?>
				</div>
				<table class="table table-striped mb-0">
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
						<th>Metal/Material</th>
						<th style="width: 44px"></th>
						<th></th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ( $post_operations as $display_index => $Operation ) : ?>
							<tr data-type="operation" data-index="<?php echo $Operation->get_id(); ?>" data-name="<?php echo esc_attr( $Operation->get_operation() ); ?>">
								<td style="width: 10px"><?php echo $display_index + 1; ?>.</td>
								<td data-model="operation"><?php echo $Operation->get_operation(); ?></td>
								<td data-model="description"><?php echo $Operation->get_truncated_description(); ?></td>
								<td data-model="setupTime"><?php echo $Operation->get_setup_time(); ?></td>
								<td data-model="setupUnit"><?php echo $Operation->get_setup_unit(); ?></td>
								<td data-model="cycleTime"><?php echo $Operation->get_cycle_time(); ?></td>
								<td data-model="cycleUnit"><?php echo $Operation->get_cycle_unit(); ?></td>
								<td data-model="efficiency"><?php echo $Operation->get_efficiency(); ?></td>
								<td data-model="metalMaterial"><?php echo $Operation->get_material_list(); ?></td>
								<td class="text-right py-0 align-middle">
									<div class="btn-group btn-group-sm">
										<button type="button" class="btn btn-primary js-sortable-handle"><i class="fas fa-arrows-alt"></i></button>
									</div>
								</td>
								<td class="text-right py-0 align-middle">
									<div class="btn-group btn-group-sm">
										<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#operation-modal-<?php echo $Operation->get_id(); ?>"><i class="fas fa-edit"></i></button>
										<button type="button" class="btn btn-danger js-delete-operation" data-index="<?php echo $Operation->get_id(); ?>"><i class="fas fa-trash"></i></button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="card-footer">
		<button type="button" class="js-add-operation btn btn-primary btn-sm">Add Operation</button>
	</div>
	<div class="operation-modals">
		<?php
			foreach ( $all_operations as $Operation ) :
				$operation_data = array(
					'Operation' => $Operation,
					'i' => $Operation->get_id(),
				);
				echo PC_CPQ()->view( 'manage/settings/partials/operation-modal', array_merge( $data, $operation_data ) );
			endforeach;
		?>
	</div>
</div>
