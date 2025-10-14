<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="col-sm-2 col-form-label"><?php echo $Input->get_acf_field('label'); ?></label>
	<div class="col-sm-10">
		<div class="input-group">
			<?php echo PC_CPQ()->view( 'manage/fields/_prepend', array( 'Input' => $Input ) ); ?>
			<textarea
				id="<?php echo $Input->get_id(); ?>"
				class="form-control rounded-0"
				name="<?php echo $Input->get_name(); ?>"
				rows="4"
			><?php echo strip_tags( $Input->get_value() ); ?></textarea>
			<?php echo PC_CPQ()->view( 'manage/fields/_append', array( 'Input' => $Input ) ); ?>
		</div>
		<?php echo PC_CPQ()->view( 'manage/fields/_instructions', array( 'Input' => $Input ) ); ?>
	</div>
</div>