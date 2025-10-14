<div id="customer-shipping">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Address</th>
				<th>State</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Customer->get_Shipping() as $Shipping ) :
			?>
			<tr data-type="shipping" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="address"><?php echo $Shipping->get_shipping_street_address(); ?></td>
				<td data-model="state">
					<?php echo $Shipping->get_shipping_state(); ?>
				</td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#shipping-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-shipping" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="shipping-modals">
		<?php
			$i = 0;
			foreach ( $Customer->get_Shipping() as $Shipping ) :
				$shipping_data = array(
					'Shipping' => $Shipping,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/partials/shipping-modal', array_merge( $data, $shipping_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>