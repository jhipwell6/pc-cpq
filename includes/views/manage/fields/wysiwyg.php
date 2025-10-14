<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="col-sm-2 col-form-label"><?php echo $Input->get_acf_field('label'); ?></label>
	<div class="col-sm-10">
		<div class="input-group">
			<?php echo PC_CPQ()->view( 'manage/fields/_prepend', array( 'Input' => $Input ) ); ?>
			<?php
				wp_editor( $Input->get_value(), $Input->get_id(), array(
					'quicktags' => false,
					'textarea_name' => $Input->get_name(),
					'textarea_rows' => 12,
					'media_buttons' => false,
				) ); ?>
			<?php echo PC_CPQ()->view( 'manage/fields/_append', array( 'Input' => $Input ) ); ?>
		</div>
		<?php echo PC_CPQ()->view( 'manage/fields/_instructions', array( 'Input' => $Input ) ); ?>
	</div>
</div>