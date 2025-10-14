<?php

namespace PC_CPQ\Controllers\Admin;

use \WP_MVC\Controllers\Abstracts\MVC_Controller_Registry;
use \PC_CPQ\Core\Nutshell_Service;
use \PC_CPQ\Helpers\Translate;
use \PC_CPQ\Helpers\Constants;
use \PC_CPQ\Helpers\Defaults;
use \PC_CPQ\Helpers\Quotes;
use \WP_Query;
use \GFAPI;
use \GPDFAPI;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PC_CPQ_Lead_Admin extends MVC_Controller_Registry
{
	const QUOTE_FORM_ID = 1;

	private static $quote_pdf_id = '61f00df78801d';
	private static $routing_pdf_id = '624371a9027e0';

	/**
	 * Initializes variables and sets up WordPress hooks/actions.
	 * @return void
	 */
	protected function __construct()
	{
		add_filter( 'acf/render_field/key=field_6075ed3ab0f1f', array( $this, 'render_quote' ), 10, 1 );
		add_action( 'gfpdf_post_html_fields', array( $this, 'render_quote_pdf' ), 10, 2 );
		add_action( 'gfpdf_post_html_fields', array( $this, 'render_routing_pdf' ), 10, 2 );
		add_filter( 'gfpdf_field_middleware', array( $this, 'skip_fields_for_pdf' ), 10, 7 );
		add_filter( 'acf/load_value', array( $this, 'set_pricing_defaults' ), 10, 3 );
		add_filter( 'acf/load_value/key=field_6192cf3a293cb', array( $this, 'set_process_time' ), 10, 3 );

		add_action( 'wp_ajax_update_lead_status', array( $this, 'update_lead_status' ) );
		add_action( 'wp_ajax_nopriv_update_lead_status', array( $this, 'update_lead_status' ) );
		add_action( 'wp_ajax_lookup_parts', array( $this, 'lookup_parts' ) );
		add_action( 'wp_ajax_nopriv_lookup_parts', array( $this, 'lookup_parts' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_lead_meta_box' ) );
		add_filter( 'gform_notification_events', array( $this, 'quote_created_event' ) );
		add_filter( 'gform_notification_events', array( $this, 'no_quote_event' ) );
		add_filter( 'gform_notification_events', array( $this, 'message_event' ) );
		
		add_filter( 'manage_edit-lead_columns', array( $this, 'add_column_head' ) );
		add_action( 'manage_lead_posts_custom_column', array( $this, 'manage_column_content' ) );
	}

	public function render_quote( $field )
	{
		// bail if we're on the frontend
		if ( PC_CPQ()->is_request( 'frontend' ) )
			return false;

		$Lead = PC_CPQ()->lead( get_the_ID() );
		include_once PC_CPQ()->plugin_path() . '/includes/views/admin/quote.php';
	}

	public function render_quote_pdf( $entry, $config )
	{
		if ( $config['settings']['id'] != self::$quote_pdf_id ) {
			return false;
		}

		$entry_id = rgar( $entry, 'id' );
		$lead = new WP_Query( array(
			'post_type' => 'lead',
			'posts_per_page' => 1,
			'meta_query' => array( array(
					'key' => 'form_entry_id',
					'value' => $entry_id
				) )
			) );

		if ( $lead->have_posts() ) {
			while ( $lead->have_posts() ) {
				$lead->the_post();
				$Lead = PC_CPQ()->lead( get_the_ID() );
				$data = array(
					'Lead' => $Lead,
					'header' => PC_CPQ()->Settings()->get_quote_header(),
					'footer' => PC_CPQ()->Settings()->get_quote_footer(),
					'terms' => PC_CPQ()->Settings()->get_quote_terms(),
					'pricing_type' => $Lead->get_quote_pricing_type(),
				);

				echo $this->get_pdf_css();
				echo PC_CPQ()->view( 'admin/pdf', $data );
			}
			wp_reset_query();
		}
	}

	public function render_routing_pdf( $entry, $config )
	{
		if ( $config['settings']['id'] != self::$routing_pdf_id ) {
			return false;
		}

		$entry_id = rgar( $entry, 'id' );
		$lead = new WP_Query( array(
			'post_type' => 'lead',
			'posts_per_page' => 1,
			'meta_query' => array( array(
					'key' => 'form_entry_id',
					'value' => $entry_id
				) )
			) );

		if ( $lead->have_posts() ) {
			while ( $lead->have_posts() ) {
				$lead->the_post();
				$Lead = PC_CPQ()->lead( get_the_ID() );
				$data = array(
					'Lead' => $Lead,
					'header' => PC_CPQ()->Settings()->get_quote_header(),
				);
				
				echo $this->get_pdf_css();
				echo PC_CPQ()->view( 'admin/routing', $data );
			}
			wp_reset_query();
		}
	}

	private function get_pdf_css()
	{
		$css = '';
		$file = PC_CPQ()->plugin_path() . '/assets/css/pc-cpq-pdf.css';

		if ( file_exists( $file ) ) {
			ob_start();
			include $file;
			$css = ob_get_clean();
		}

		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );

		return sprintf( "<style id='pc-cpq-pdf-styles'>%s</style>", $css );
	}

	public function skip_fields_for_pdf( $action, $field, $entry, $form, $config, $products, $blacklisted )
	{
		if ( $config['settings']['id'] == self::$quote_pdf_id ) {
			return true;
		}

		if ( $config['settings']['id'] == self::$routing_pdf_id ) {
			return true;
		}

		if ( $config['settings']['id'] == '607de79325579' && $field->type == 'hidden' ) {
			return true;
		}

		return $action;
	}

	public function set_pricing_defaults( $value, $post_id, $field )
	{
		switch ( $field['key'] ) {
			case 'field_6078d4fd2d956':
				$value = $this->maybe_load_default( $value, Defaults::$margin );
				break;

			case 'field_6078d50e2d957':
				$value = $this->maybe_load_default( $value, Defaults::$eff );
				break;

			case 'field_6078d5242d958':
				$value = $this->maybe_load_default( $value, Defaults::$people );
				break;

			case 'field_6078d53a2d959':
				$value = $this->maybe_load_default( $value, Defaults::$eau );
				break;

			case 'field_6078d5482d95a':
				$value = $this->maybe_load_default( $value, Defaults::$shift );
				break;

			case 'field_61d73807f01de':
				$value = $this->maybe_load_default( $value, Defaults::$break_in );
				break;

			case 'field_627aa4b56941c':
				$value = $this->maybe_load_default( $value, Defaults::$metal_adder );
				break;
		}

		return $value;
	}

	private function maybe_load_default( $value, $default )
	{
		if ( isset( $value ) && $value != '' && $value != $default ) {
			return $value;
		}

		return $default;
	}

	public function set_process_time( $value, $post_id, $field )
	{
		$processes = $value;
		if ( ! empty( $processes ) ) {
			$i = 0;
			foreach ( $processes as $process ) {
				$process = Translate::field_keys( $process );

				// skip processes that already have a time
				if ( $process['time'] && ! $process['time'] == 'NAN' ) {
					$i ++;
					continue;
				}

				$avg_thickness = $this->calculate_average_thickness( $process['min_thickness'], $process['max_thickness'] );
				$deposit_rate = Constants::get_plating_metal_value( $process['metal'], 'deposit_rate' );
				$processes[$i]['field_6192cf73293ce'] = $deposit_rate > 0 ? ceil( $avg_thickness / $deposit_rate ) : 0;
				$i ++;
			}

			$value = $processes;
		}

		return $value;
	}

	public function update_lead_status()
	{
		if ( isset( $_REQUEST ) ) {
			$post_id = isset( $_REQUEST['ID'] ) ? $_REQUEST['ID'] : null;
			update_post_meta( $post_id, 'new', 0 );

			$return = array(
				'message' => __( 'Saved', PC_CPQ_DOMAIN )
			);
			wp_send_json_success( $return );
		}

		die();
	}

	public function add_lead_meta_box()
	{
		add_meta_box( 'spc_lead_meta', __( 'Quote', PC_CPQ_DOMAIN ), array( $this, 'spc_lead_meta_output' ), 'lead', 'side', 'core' );
		add_meta_box( 'spc_lead_meta_pdfs', __( 'PDFs', PC_CPQ_DOMAIN ), array( $this, 'spc_lead_meta_pdfs_output' ), 'lead', 'side', 'core' );
		add_meta_box( 'spc_lead_meta_files', __( 'Files', PC_CPQ_DOMAIN ), array( $this, 'spc_lead_meta_files_output' ), 'lead', 'side', 'core' );
	}

	public function spc_lead_meta_output( $post )
	{
		$Lead = PC_CPQ()->lead( $post->ID );
		?>
		<div id="quote-date">
			Entry ID: <?php echo $Lead->get_form_entry_id() ? $Lead->get_form_entry_id() : 'N/A'; ?><br />
			Quote sent: <span data-model="quote_date"><?php echo $Lead->is_sent() && $Lead->get_quote_date() ? $Lead->get_quote_date() : 'N/A'; ?></span>
		</div>
		<div id="quote-action">
			<?php if ( $Lead->get_form_entry_id() && $Lead->get_status() != 'No Quote' && $Lead->get_status() != 'Canceled' ) : ?>
				<input type="submit" class="button button-primary button-large" name="send_quote" id="spc-send-quote" value="Send Quote" />
			<?php endif; ?>
			<?php if ( $Lead->get_form_entry_id() && ( $Lead->get_status() == 'No Quote' || $Lead->get_status() == 'Canceled' ) ) : ?>
				<input type="submit" class="button button-primary button-large" name="send_no_quote" id="spc-send-no-quote" value="Send No Quote" />
			<?php endif; ?>
		</div>
		<?php
	}

	public function spc_lead_meta_pdfs_output( $post )
	{
//		$Lead = SPC()->lead( $post->ID );
//		if ( class_exists( 'GPDFAPI' ) && $Lead->get_form_entry_id() ) {
//			$pdfs = GPDFAPI::get_entry_pdfs( $Lead->get_form_entry_id() );
//			if ( ! is_wp_error( $pdfs ) && count( $pdfs ) > 0 ) {
//				echo '<ul>';
//				foreach ( $pdfs as $pdf ) {
//					echo '<li>' . do_shortcode( '[gravitypdf type="view" name="' . $pdf['name'] . '" text="' . $pdf['name'] . '" id="' . $pdf['id'] . '" entry="' . $Lead->get_form_entry_id() . '"]' ) . '</li>';
//				}
//				echo '</ul>';
//			}
//		}
	}

	public function spc_lead_meta_files_output( $post )
	{
//		$Lead = SPC()->lead( $post->ID );
//		if ( ! empty( $Lead->get_files() ) ) {
//			echo '<ul>';
//			foreach ( $Lead->get_files() as $file ) {
//				$filename = array_pop( explode( '/', $file ) );
//				echo '<li><a href="' . $file . '" target="_blank">' . $filename . '</a></li>';
//			}
//			echo '</ul>';
//		}
	}

	public function quote_created_event( $notification_events )
	{
		$notification_events['quote_created'] = __( 'Quote created', PC_CPQ_DOMAIN );
		return $notification_events;
	}

	public function no_quote_event( $notification_events )
	{
		$notification_events['no_quote'] = __( 'No quote', PC_CPQ_DOMAIN );
		return $notification_events;
	}
	
	public function message_event( $notification_events )
	{
		$notification_events['message'] = __( 'Message', PC_CPQ_DOMAIN );
		return $notification_events;
	}

	public function lookup_parts()
	{
		if ( ! isset( $_REQUEST['action'] ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] != 'lookup_parts' ) ) {
			return false;
		}

		$return = array();
		$parts = isset( $_REQUEST['parts'] ) ? $_REQUEST['parts'] : null;
		if ( $parts ) {
			foreach ( $parts as $part ) {
				$hash = md5( implode( '', $part ) );
				$result = PC_CPQ()->part_lookup->search_parts( $hash, $_REQUEST['ID'] );
				if ( ! empty( $result ) ) {
					$Lead = PC_CPQ()->lead( $result[0]['post_id'] );
					$return[] = array(
						'id' => $Lead->get_id(),
						'title' => $Lead->get_title(),
						'edit_url' => $Lead->get_edit_url(),
					);
				} else {
					$return[] = $result;
				}
			}
		}
		wp_send_json( $return );
	}

	private function calculate_average_thickness( $min, $max )
	{
		$values = array();
		if ( $min != '' && $min > 0 ) {
			$values[] = $min;
		}
		if ( $max != '' && $max > 0 ) {
			$values[] = $max;
		}

		return ! empty( $values ) ? array_sum( $values ) / count( $values ) : 0;
	}

	/**
	 * Column Headers
	 * @param [type] $columns [description]
	 * @return  array array of column headers
	 */
	public function add_column_head( $columns )
	{
		$new = array();
		foreach ( $columns as $key => $title ) {

			$new[$key] = $title;

			// Add new and auth columns after the title column
			if ( $key == 'title' ) {
				$new['company'] = 'Company';
				$new['ids'] = 'IDs';
				$new['status'] = 'Status';
				$new['is_authorized'] = 'Authorized?';
			}
		}
		return $new;
	}

	/**
	 * Column Content
	 * @param  [type] $name [description]
	 * @return null
	 */
	public function manage_column_content( $name )
	{
		global $post;
		$Lead = PC_CPQ()->lead( $post->ID );

		switch ( $name ) {
			case 'company':
				echo $Lead->get_company();
				break;

			case 'ids':
				echo 'Quote #: ' . $Lead->get_quote_number();
				echo ( (bool) $Lead->get_nutshell_id() ) ? '<br />Nutshell ID: ' . $Lead->get_nutshell_id() : '';
				break;

			case 'status':
				echo metadata_exists( 'post', $Lead->get_id(), 'new' ) && get_post_meta( $Lead->get_id(), 'new', true ) ? '<strong>NEW</strong>' : $Lead->get_status();
				break;

			case 'is_authorized':
				echo metadata_exists( 'post', $Lead->get_id(), 'is_authorized' ) && get_post_meta( $Lead->get_id(), 'is_authorized', true ) ? '<span class="dashicons-before dashicons-yes-alt"></span>' : '<span class="dashicons-before dashicons-dismiss"></span>';
				break;
		}
	}
}

PC_CPQ_Lead_Admin::instance();
