<?php $Input = new \PC_CPQ\Models\Input( 'quote_notes', $Lead ); ?>
<div class="form-group row">
	<div class="col-sm-12">
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