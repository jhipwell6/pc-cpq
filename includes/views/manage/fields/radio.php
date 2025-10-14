<fieldset class="form-group row">
	<legend class="label col-sm-2 float-sm-left col-form-label"><?php echo $Input->get_acf_field('label'); ?></legend>
	<div class="col-sm-10 col-form-label">
		<?php
			foreach ( $Input->get_acf_field('choices') as $choice_value => $choice_label ) :
				$slug = sanitize_title( $choice_value );
				$checked = $choice_value == $Input->get_value() ? ' checked' : '';
		?>
		<div class="custom-control custom-radio custom-control-inline">
			<input class="custom-control-input" type="radio" id="<?php echo $Input->get_id(); ?>_<?php echo $slug; ?>" name="<?php echo $Input->get_name(); ?>"<?php echo $checked; ?> value="<?php echo $choice_value; ?>">
			<label for="<?php echo $Input->get_id(); ?>_<?php echo $slug; ?>" class="custom-control-label"><?php echo $choice_label; ?></label>
		</div>
		<?php endforeach; ?>
	</div>
</fieldset>