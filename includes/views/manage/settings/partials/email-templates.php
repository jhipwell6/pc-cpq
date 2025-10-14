<div id="email-templates">	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Settings->get_Email_Templates() as $Email_Template ) :
			?>
			<tr data-type="email_template" data-index="<?php echo $i; ?>">
				<td style="width: 10px"><?php echo $i + 1; ?>.</td>
				<td data-model="name"><?php echo $Email_Template->get_name(); ?></td>
				<td class="text-right py-0 align-middle">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#email-template-modal-<?php echo $i; ?>"><i class="fas fa-edit"></i></button>
						<button type="button" class="btn btn-danger js-delete-email-template" data-index="<?php echo $i; ?>"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		</tbody>
	</table>
	<div class="email-template-modals">
		<?php
			$i = 0;
			foreach ( $Settings->get_Email_Templates() as $Email_Template ) :
				$template_data = array(
					'Email_Template' => $Email_Template,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/settings/partials/email-template-modal', array_merge( $data, $template_data ) );
			$i++;
			endforeach;
		?>
	</div>
</div>