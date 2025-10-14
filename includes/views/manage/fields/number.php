<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="<?php echo $Input->is_override() ? 'col-sm-3' : 'col-sm-2'; ?> col-form-label">
		<?php echo $Input->get_label(); ?>
		<?php if ( $Input->is_required() ) : ?> *<?php endif; ?>
	</label>
	<div class="<?php echo $Input->is_override() ? 'col-sm-9' : 'col-sm-10'; ?>">
		<div class="input-group">
			<?php echo PC_CPQ()->view( 'manage/fields/_prepend', array( 'Input' => $Input ) ); ?>
			<input
				type="text"
				id="<?php echo $Input->get_id(); ?>"
				class="form-control form-control-border"
				name="<?php echo $Input->get_name(); ?>"
				value="<?php echo $Input->get_value(); ?>"
				<?php if ( $Input->get_acf_field('min') ) : ?>min="<?php echo $Input->get_acf_field('min'); ?>"<?php endif; ?>
				<?php if ( $Input->get_acf_field('max') ) : ?>max="<?php echo $Input->get_acf_field('max'); ?>"<?php endif; ?>
				<?php if ( $Input->get_acf_field('step') ) : ?>step="<?php echo $Input->get_acf_field('step'); ?>"<?php endif; ?>
				<?php if ( $Input->is_required() ) : ?>required<?php endif; ?>
				<?php if ( $Input->is_readonly() ) : ?>readonly<?php endif; ?>
				>
			<?php echo PC_CPQ()->view( 'manage/fields/_append', array( 'Input' => $Input ) ); ?>
		</div>
		<?php echo PC_CPQ()->view( 'manage/fields/_instructions', array( 'Input' => $Input ) ); ?>
	</div>
</div>