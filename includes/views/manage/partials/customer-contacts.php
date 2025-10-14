<div id="customer-contacts">
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="width: 10px">#</th>
				<th>Name</th>
				<th>Phone / Email</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ( $Customer->get_Contacts() as $Contact ) {
					$contact_data = array(
						'Contact' => $Contact,
						'i' => $i,
					);
					echo PC_CPQ()->view( 'manage/partials/contact-row', array_merge( $data, $contact_data ) );
					$i++;
				}
			?>
		</tbody>
	</table>
	<div class="contact-modals">
		<?php
			$i = 0;
			foreach ( $Customer->get_Contacts() as $Contact ) {
				$contact_data = array(
					'Contact' => $Contact,
					'i' => $i,
				);
				echo PC_CPQ()->view( 'manage/partials/contact-modal', array_merge( $data, $contact_data ) );
				$i++;
			}
		?>
	</div>
</div>