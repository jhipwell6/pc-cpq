<div id="quote-details">
	<table class="table">
		<tbody>
			<tr>
				<th>Quote Sent</th>
				<td class="text-right"><?php echo $Lead->get_quote_date() ? $Lead->get_quote_date() : 'N/A'; ?></td>
			</tr>
			<tr>
				<th>Follow Up</th>
				<td class="text-right">
					<?php echo $Lead->get_follow_up_date() ? $Lead->get_follow_up_date() : 'N/A'; ?>
					<?php if ( $Lead->get_follow_up_date() ) { ?>
<!--					<div id="edit-follow-up-date" data-target-input="nearest">
						<input type="text" id="follow_up_date" data-target="#edit-follow-up-date" class="form-control form-control-border datetimepicker-input" name="follow_up_date" value="<?php echo esc_attr( $Lead->get_follow_up_date() ); ?>">
						<button type="button" class="btn btn-xs" title="Edit Follow Up Date" data-toggle="datetimepicker" data-target="#edit-follow-up-date"><i class="fas fa-edit"></i></button>
					</div>-->
						
					<button type="button" class="btn btn-xs" title="Edit Follow Up Date" data-toggle="collapse" data-target="#edit-follow-up-date"><i class="fas fa-edit"></i></button>
					<div id="edit-follow-up-date" class="collapse">
						<input type="text" id="follow_up_date" class="form-control form-control-border" name="follow_up_date" value="<?php echo esc_attr( $Lead->get_follow_up_date() ); ?>">
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>Expires</th>
				<td class="text-right">
					<?php echo $Lead->get_expiration_date() ? $Lead->get_expiration_date() : 'N/A'; ?>
					<?php if ( $Lead->get_expiration_date() ) { ?>
					<button type="button" class="btn btn-xs" title="Edit Expiration Date" data-toggle="collapse" data-target="#edit-expiration-date"><i class="fas fa-edit"></i></button>
					<div id="edit-expiration-date" class="collapse">
						<input type="text" id="expiration_date" class="form-control form-control-border" name="expiration_date" value="<?php echo esc_attr( $Lead->get_expiration_date() ); ?>">
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>Quote</th>
				<td class="text-right">
					<?php if ( $Lead->get_quote_pdf() ) : ?>
					<a href="<?php echo $Lead->get_quote_pdf(); ?>" target="_blank">View Quote <small class="fas fa-external-link-alt"></small></a>
					<?php ; else : ?>
					N/A
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>Routing</th>
				<td class="text-right">
					<?php if ( $Lead->get_routing_pdf() ) : ?>
					<a href="<?php echo $Lead->get_routing_pdf(); ?>" target="_blank">View Routing <small class="fas fa-external-link-alt"></small></a>
					<?php ; else : ?>
					N/A
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>