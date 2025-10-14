<div id="lead-quantities">
	<?php if ( $Lead->get_Quantities() ) : ?>	
	<table class="table table-striped table-collapsing">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Quantity</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Lead->get_Quantities() as $Quantity ) :
			?>
			<tr data-type="quantity" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td class="break-point-input"><?php echo pc_cpq_get_input_html( 'break_point', $Quantity, $i ); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-danger js-delete-quantity" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>