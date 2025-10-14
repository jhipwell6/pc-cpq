<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="col-sm-2 col-form-label">
		<?php echo $Input->get_acf_field( 'label' ); ?>
		<?php if ( $Input->is_required() ) : ?> *<?php endif; ?>
	</label>
	<div class="col-sm-10">
		<div class="input-group">
			<?php echo PC_CPQ()->view( 'manage/fields/_prepend', array( 'Input' => $Input ) ); ?>
			<input
				type="text"
				id="<?php echo $Input->get_id(); ?>"
				class="form-control form-control-border"
				name="<?php echo $Input->get_name(); ?>"
				value="<?php echo $Input->get_name() == 'post_ops_order' ? esc_attr( $Input->get_value() ) : $Input->get_value(); ?>"
				<?php if ( $Input->is_required() ) : ?>required<?php endif; ?>
				<?php if ( $Input->is_readonly() ) : ?>readonly<?php endif; ?>
				>
			<?php echo PC_CPQ()->view( 'manage/fields/_append', array( 'Input' => $Input ) ); ?>
		</div>
		<?php echo PC_CPQ()->view( 'manage/fields/_instructions', array( 'Input' => $Input ) ); ?>
	</div>
</div>