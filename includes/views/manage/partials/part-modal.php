<div class="modal fade" id="part-modal-<?php echo $i; ?>" aria-modal="true" role="dialog" data-type="part" data-index="<?php echo $i; ?>">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <div class="modal-header">
				<h4 class="modal-title">Edit Part</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
            </div>
            <div class="modal-body p-0">
				<div class="card shadow-none m-0">
					<div class="card-header d-flex p-0">
						<h3 class="card-title p-3">Part Details</h3>
						<ul class="nav nav-pills ml-auto p-2">
							<li class="nav-item"><a class="nav-link active" href="#part_<?php echo $i; ?>_general" data-toggle="tab">1. General</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_quantities" data-toggle="tab">2. Quantities</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_measurements" data-toggle="tab">3. Measurements</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_processes" data-toggle="tab">4. Processes</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_plating" data-toggle="tab">5. Plating</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_pricing" data-toggle="tab">6. Pricing</a></li>
							<li class="nav-item"><a class="nav-link" href="#part_<?php echo $i; ?>_overrides" data-toggle="tab">7. Overrides</a></li>
						</ul>
					</div><!-- /.card-header -->
					<div class="card-body p-0">
						<div class="tab-content">
							<div class="tab-pane p-4 active" id="part_<?php echo $i; ?>_general">
								<?php if ( ! $Part->has_file() ) : ?>
									<?php echo PC_CPQ()->view( 'manage/fields/upload-part-file', array( 'Part' => $Part, 'i' => $i ) ); ?>
								<?php endif; ?>
								<?php echo pc_cpq_get_input_html( 'file_name', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'base_metal', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'drawing_number', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'revision_number', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'part_number', $Part, $i ); ?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane" id="part_<?php echo $i; ?>_quantities">
							<?php 
								$part_data = array(
									'Part' => $Part,
									'i' => $i,
								);
								echo PC_CPQ()->view( 'manage/partials/part-tab-quantities', array_merge( $data, $part_data ) );
							?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane tab-part-measurements p-4" id="part_<?php echo $i; ?>_measurements">
								<?php echo pc_cpq_get_input_html( 'area_computed', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'volume_computed', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_x_computed', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_y_computed', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_z_computed', $Part, $i ); ?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane" id="part_<?php echo $i; ?>_processes">
							<?php 
								$part_data = array(
									'Part' => $Part,
									'i' => $i,
								);
								echo PC_CPQ()->view( 'manage/partials/part-tab-processes', array_merge( $data, $part_data ) );
							?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane" id="part_<?php echo $i; ?>_plating">
							<?php 
								$part_data = array(
									'Part' => $Part,
									'i' => $i,
								);
								echo PC_CPQ()->view( 'manage/partials/part-tab-plating', array_merge( $data, $part_data ) );
							?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane p-4" id="part_<?php echo $i; ?>_pricing">
								<?php $Pricing = $Part->get_Pricing(); ?>
								<?php echo pc_cpq_get_input_html( 'margin', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'eff', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'people', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'eau', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'shift', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'break_in', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'metal_adder', $Pricing, [ $i, 0 ] ); ?>
								<?php echo pc_cpq_get_input_html( 'price_unit', $Pricing, [ $i, 0 ] ); ?>
							</div>
							<!-- /.tab-pane p-4 -->
							<div class="tab-pane p-4" id="part_<?php echo $i; ?>_overrides">
								<?php echo pc_cpq_get_input_html( 'area_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'volume_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_x_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_y_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'd_z_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'pieces_per_load_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'weight_override', $Part, $i ); ?>
								<?php echo pc_cpq_get_input_html( 'thruput_capacity_override', $Part, $i ); ?>
							</div>
							<!-- /.tab-pane p-4 -->
						</div>
						<!-- /.tab-content -->
					</div><!-- /.card-body -->
				</div>
            </div>
            <div class="modal-footer justify-content-end">
				<button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Done">Done</button>
            </div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>