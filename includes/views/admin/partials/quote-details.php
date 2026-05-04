<table class="wp-list-table widefat pc-cpq-quote-table" style="margin-bottom: 30px;">
	<tbody>
		<tr>
			<td style="vertical-align: top; width: 80px;">
				<strong>Quote To: </strong>
			</td>
			<td style="vertical-align: top;">
				<?php echo $Lead->has_customer() ? $Lead->get_Customer()->get_formatted_address() : ''; ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="wp-list-table widefat pc-cpq-quote-table">
	<tbody>
		<tr>
			<td style="vertical-align: top;">
				<table>
					<tbody>
						<?php
							$selected_fee_names = wp_list_pluck( (array) $Lead->get_fees(), 'fee' );
							$selected_fees = array_filter( PC_CPQ()->Settings()->get_Fees(), function( $Fee ) use ( $selected_fee_names ) {
								return in_array( $Fee->get_name(), $selected_fee_names, true );
							} );

							$formatted_fees = array_map( function( $Fee ) {
								$amount = $Fee->get_unit() == 'percent'
									? $Fee->get_amount() . '%'
									: to_currency( $Fee->get_amount() );

								return $Fee->get_name() . ' (' . $amount . ')';
							}, $selected_fees );

							$table_data = array(
								'Quote Number'		=> $Lead->get_quote_number(),
								'Quote Date'		=> $Lead->get_quote_date(),
								'Expires'			=> $Lead->get_expiration_date(),
								'Customer'			=> $Lead->get_company(),
								'Salesman'			=> 'N/A', // todo
								'Terms'				=> 'Payment in Advance',
//								'Min Lot Charge'	=> to_currency( $Lead->get_min_lot_charge() ),
							);

							if ( ! empty( $formatted_fees ) ) {
								$table_data['Fees'] = implode( '<br />', $formatted_fees );
							}

							foreach ( $table_data as $label => $value ) : 
						?>
						<tr>
							<td>
								<strong><?php echo $label; ?>: </strong>
							</td>
							<td>
								<?php echo $value; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			<td style="vertical-align: top;">
				<table>
					<tbody>
						<?php
							$table_data = array(
								'Contact'		=> $Lead->get_full_name(),
//								'Inquiry'		=> $Lead->get_title(),
								'Phone'			=> $Lead->get_phone(),
								'Email'			=> $Lead->get_email(),
								'Certification' => $Lead->needs_certification() ? 'Yes ($35)' : 'No',
								'Ship Via'		=> 'ORIGIN',
								'FOB'			=> 'ORIGIN',
							);
							foreach ( $table_data as $label => $value ) : 
						?>
						<tr>
							<td>
								<strong><?php echo $label; ?>: </strong>
							</td>
							<td>
								<?php echo $value; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
