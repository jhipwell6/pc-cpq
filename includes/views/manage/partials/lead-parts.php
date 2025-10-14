<div id="lead-parts">	
	<table class="table table-striped table-collapsing">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>File Name</th>
				<th>Dwg. # / Rev. # / Part #</th>
				<th style="text-align: right">
					<button type="button" class="btn btn-warning btn-sm m-0 js-paste-part-all d-none" data-index="<?php echo $i; ?>" title="Paste to All">Paste to All</button>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Lead->get_Parts() as $Part ) :
			?>
			<tr data-type="part" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="fileName"><?php echo $Part->get_file_name(); ?></td>
				<td>
					<span data-model="drawingNumber"><?php echo $Part->get_drawing_number(); ?></span> / 
					<span data-model="revisionNumber"><?php echo $Part->get_revision_number(); ?></span> / 
					<span data-model="partNumber"><?php echo $Part->get_part_number(); ?></span>
				</td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-info js-self-tooltip" data-toggle="collapse" data-target="#manage-part-details-<?php echo $i; ?>" aria-expanded="false" aria-controls="manage-part-details-<?php echo $i; ?>" title="View"><i class="fas fa-eye"></i></button>
						<button type="button" class="btn btn-primary js-self-tooltip" data-toggle="modal" data-target="#part-modal-<?php echo $i; ?>" data-backdrop="static" title="Edit"><i class="fas fa-edit"></i></button>
						<div class="btn-group btn-group-sm" role="group">
							<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
							  <i class="fas fa-clone"></i>
							</button>
							<div class="dropdown-menu rounded-0 p-0">
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 js-clone-part" data-index="<?php echo $i; ?>" title="Copy Part">Copy Part</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-copy-part-process" data-index="<?php echo $i; ?>" title="Copy Process">Copy Process</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-paste-part-process disabled" data-index="<?php echo $i; ?>" title="Paste Process">Paste Process</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-copy-part-quantities" data-index="<?php echo $i; ?>" title="Copy Quantities">Copy Quantities</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-paste-part-quantities disabled" data-index="<?php echo $i; ?>" title="Paste Quantities">Paste Quantities</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-copy-part-pricing" data-index="<?php echo $i; ?>" title="Copy Pricing">Copy Pricing</button>
							  <button type="button" class="btn btn-warning btn-block btn-sm rounded-0 m-0 js-paste-part-pricing disabled" data-index="<?php echo $i; ?>" title="Paste Pricing">Paste Pricing</button>
							</div>
						</div>
						<?php if ( $Part->get_file() ) : ?>
						<a href="<?php echo $Part->get_file() ? esc_url( $Part->get_file() ) : '#'; ?>" class="btn btn-secondary js-self-tooltip" title="Download File" target="_blank"><i class="fas fa-download"></i></a>
						<?php endif; ?>
						<button type="button" class="btn btn-danger js-delete-part js-self-tooltip" data-index="<?php echo $i; ?>" title="Delete"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<tr class="collapse" id="manage-part-details-<?php echo $i; ?>">
				<td colspan="4">
					<?php 
						$part_data = array(
							'Part' => $Part,
							'i' => $i,
						);
						echo PC_CPQ()->view( 'manage/partials/part-summary', array_merge( $data, $part_data ) );
					?>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="part-modals">
		<?php
			$i = 0;
			foreach ( $Lead->get_Parts() as $Part ) :
				$part_data = array(
					'Part' => $Part,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/partials/part-modal', array_merge( $data, $part_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>