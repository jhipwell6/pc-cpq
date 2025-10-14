<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="col-sm-2 col-form-label"><?php echo $Input->get_acf_field( 'label' ); ?></label>
	<div class="col-sm-10">
		<?php
			$field = $Input->get_acf_field();
			$value = $Input->get_value();
			$field['class'] = 'form-control form-control-border';
			$field['value'] = $value ? [ $value->get_id() ] : '';
			$select = new acf_field_post_object();
			$select->render_field( $field );
		?>
	</div>
</div>