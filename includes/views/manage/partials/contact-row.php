<tr data-type="contact" data-index="<?php echo $i; ?>">
	<td style="width: 10px"><?php echo $i + 1; ?>.</td>
	<td data-model="name"><?php echo $Contact->get_name(); ?></td>
	<td>
		<span data-model="phone"><?php echo $Contact->get_phone(); ?></span> / <span data-model="email"><?php echo $Contact->get_email(); ?></span>
	</td>
	<td class="text-right py-0 align-middle">
		<div class="btn-group btn-group-sm">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#contact-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
			<button type="button" class="btn btn-danger js-delete-contact" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
		</div>
	</td>
</tr>