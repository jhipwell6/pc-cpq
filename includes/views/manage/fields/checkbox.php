<fieldset class="form-group">
	<legend class="label"><?php echo $Input->get_acf_field('label'); ?></legend>
	<?php
		foreach ( $Input->get_acf_field('choices') as $choice_value => $choice_label ) :
			$slug = sanitize_title( $choice_value );
			$checked = $choice_value == $value ? ' checked' : '';
	?>
	<div class="custom-control custom-checkbox custom-control-inline">
		<input class="custom-control-input" type="checkbox" id="<?php echo $Input->get_id(); ?>_<?php echo $slug; ?>" name="<?php echo $Input->get_name(); ?>"<?php echo $checked; ?> value="<?php echo $choice_value; ?>">
		<label for="<?php echo $Input->get_id(); ?>_<?php echo $slug; ?>" class="custom-control-label"><?php echo $choice_label; ?></label>
	</div>
	<?php endforeach; ?>
</fieldset>