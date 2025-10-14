<div class="form-group row">
	<label for="<?php echo $Input->get_id(); ?>" class="col-sm-2 col-form-label">
		<?php echo $Input->get_acf_field( 'label' ); ?>
		<?php if ( $Input->is_required() ) : ?> *<?php endif; ?>
	</label>
	<div class="col-sm-10">
		<div class="input-group">
			<?php echo PC_CPQ()->view( 'manage/fields/_prepend', array( 'Input' => $Input ) ); ?>
			<select
				id="<?php echo $Input->get_id(); ?>"
				class="form-control custom-select form-control-border"
				name="<?php echo $Input->get_name(); ?>"
				<?php if ( $Input->get_acf_field('multiple') ) : ?>multiple<?php endif; ?>
				<?php if ( $Input->is_required() ) : ?>required<?php endif; ?>
				>
				<?php
					$current_group = '';
					foreach ( $Input->get_acf_field('choices') as $option_value => $option_label ) :
						$this_group = preg_match( '/^#(.+)/', $option_label, $matches ) ? str_replace( '#', '', $option_label ) : $current_group;
						if ( is_array( $Input->get_value() ) ) {
							$selected = in_array( $option_value, $Input->get_value() ) ? ' selected' : '';
						} else {
							$selected = $option_value == esc_attr( $Input->get_value() ) ? ' selected' : '';
						}
				?>
				<?php if ( $this_group != $current_group && $current_group != '' ) : ?></optgroup><?php endif; ?>
				<?php if ( $this_group != '' && $this_group != $current_group ) : ?><optgroup label="<?php echo $this_group; ?>"><?php endif; ?>
				<?php if ( $option_value != '#' . $this_group ) : ?><option value="<?php echo $option_value; ?>"<?php echo $selected; ?>><?php echo $option_label; ?></option><?php endif; ?>
				<?php
					endforeach;
					if ( $current_group != '' ) { ?></optgroup><?php }
				?>
			</select>
			<?php echo PC_CPQ()->view( 'manage/fields/_append', array( 'Input' => $Input ) ); ?>
		</div>
		<?php echo PC_CPQ()->view( 'manage/fields/_instructions', array( 'Input' => $Input ) ); ?>
	</div>
</div>