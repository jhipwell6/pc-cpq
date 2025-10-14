<?php // if ( $Part->get_Quantities() ) : ?>	
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
			$q = 0;
			foreach ( $Part->get_Quantities() as $Quantity ) :
		?>
		<tr data-type="quantity" data-index="<?php echo $q; ?>">
			<td style="width: 10px"><?php echo $q + 1; ?>.</td>
			<td class="break-point-input"><?php echo pc_cpq_get_input_html( 'break_point', $Quantity, [ $i, $q ] ); ?></td>
			<td class="text-right py-0 align-middle">
				<div class="btn-group btn-group-sm">
					<button type="button" class="btn btn-danger js-delete-part-quantity" data-index="<?php echo $q; ?>" data-part-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
				</div>
			</td>
		</tr>
		<?php $q++; endforeach; ?>
	</tbody>
</table>
<?php // endif; ?>
<div class="p-4">
	<button type="button" class="js-add-part-quantity btn btn-primary btn-sm">Add Quantity</button>
</div>