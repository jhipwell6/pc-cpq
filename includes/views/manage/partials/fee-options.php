<?php
$fees = (array) PC_CPQ()->Settings()->get_Fees();
$selected_fees = $Lead->get_selected_fee_names();
$has_saved_fees = ! empty( $selected_fees );
$field_label = isset( $field_label ) ? $field_label : 'Fees';
$input_name_prefix = isset( $input_name_prefix ) ? $input_name_prefix : 'selected_fees';
$input_id_prefix = isset( $input_id_prefix ) ? $input_id_prefix : 'fees';
$show_hidden_marker = ! empty( $show_hidden_marker );
?>
<?php if ( $show_hidden_marker ) : ?>
<input type="hidden" name="has_fee_selection" value="1">
<?php endif; ?>
<?php if ( ! empty( $fees ) ) : ?>
<div class="form-group">
	<label class="form-label d-block"><?php echo esc_html( $field_label ); ?></label>
	<?php foreach ( $fees as $i => $Fee ) : ?>
	<?php
		$is_checked = $has_saved_fees ? in_array( $Fee->get_name(), $selected_fees, true ) : $Fee->is_enabled_by_default();
		$formatted_amount = 'percent' === $Fee->get_unit()
			? $Fee->get_amount() . '%'
			: '$' . $Fee->get_amount();
		$input_id = $input_id_prefix . '_' . $i;
	?>
	<div class="custom-control custom-checkbox">
		<input type="checkbox" class="custom-control-input" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_name_prefix ); ?>/<?php echo esc_attr( $i ); ?>" value="<?php echo esc_attr( $Fee->get_name() ); ?>"<?php checked( $is_checked ); ?>>
		<label class="custom-control-label" for="<?php echo esc_attr( $input_id ); ?>">
			<?php echo esc_html( $Fee->get_name() ); ?>
			<small class="text-muted">(<?php echo esc_html( $formatted_amount ); ?>)</small>
		</label>
	</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>
