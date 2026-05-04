<div id="fees">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Amount</th>
				<th>Unit</th>
				<th>Enabled by default</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Fees() as $Fee ) :
			?>
			<tr data-type="fee" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Fee->get_name(); ?></td>
				<td data-model="amount"><?php echo $Fee->get_amount(); ?></td>
				<td data-model="unit"><?php echo $Fee->get_unit() == 'percent' ? '%' : '$'; ?></td>
				<td data-model="enabledByDefault"><?php echo $Fee->get_enabled_by_default() == 1 ? 'Yes' : '-'; ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#fee-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-fee" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="fee-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Fees() as $Fee ) :
				$fee_data = array(
					'Fee' => $Fee,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/fee-modal', array_merge( $data, $fee_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>
