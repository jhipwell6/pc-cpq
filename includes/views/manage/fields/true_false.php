<fieldset class="form-group row">
	<legend class="label col-sm-2 float-sm-left col-form-label"><?php echo $Input->get_acf_field('label'); ?></legend>
	<div class="col-sm-10 col-form-label">
		<div class="custom-control custom-switch">
			<?php $checked = $Input->get_value() ? ' checked' : ''; ?>
			<input type="checkbox" class="custom-control-input" id="<?php echo $Input->get_id(); ?>" name="<?php echo $Input->get_name(); ?>"<?php echo $checked; ?> value="1">
			<label class="custom-control-label" for="<?php echo $Input->get_id(); ?>"><?php echo $Input->get_acf_field('instructions'); ?></label>
		</div>
	</div>
</fieldset>