<?php if ( $Input->get_acf_field( 'append' ) ) : ?>
	<div class="input-group-append">
		<span class="input-group-text" id="<?php echo $Input->get_id(); ?>-append"><?php echo $Input->get_acf_field( 'append' ); ?></span>
	</div>
<?php endif; ?>