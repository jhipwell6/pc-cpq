<?php

namespace PC_CPQ\Helpers;

use \League\Csv\Reader;
use \League\Csv\Writer;
use \League\Csv\CharsetConverter;

if ( ! defined( 'ABSPATH' ) )
	exit;

class CSV_Import_Export_Options
{

	public function __construct( $option_name, $option_page, $namespaced_model_name )
	{
		$this->option_name = $option_name;
		$this->option_label = pc_cpq_slug_to_label( $this->option_name );
		$this->option_page = $option_page;
		$this->model_name = $namespaced_model_name;
		$this->import_action_param = 'pq_' . $this->option_name . '_csv_import';
		$this->export_action_param = 'pq_' . $this->option_name . '_csv_export';
		$this->import_file_name = 'pq_' . $this->option_name . '_csv_import_file';

		add_action( 'admin_head', array( $this, 'add_admin_action_buttons' ), 100 );
		add_action( 'admin_init', array( $this, 'import_from_csv' ) );
		add_action( 'admin_init', array( $this, 'export_to_csv' ) );
	}

	public function add_admin_action_buttons()
	{
		if ( $this->is_this_options_screen() ) {
			ob_start();
			?>
			<div id="pq-map-action-buttons">
				<br />
				<a id="pq-import-<?php echo $this->option_name; ?>" class="page-title-action thickbox" href="#TB_inline?&width=300&height=200&inlineId=pq-import-<?php echo $this->option_name; ?>-data">Import <?php echo $this->option_label; ?></a>
				<a id="pq-export-<?php echo $this->option_name; ?>" class="page-title-action" href="<?php echo add_query_arg( array( 'pq_export_' . $this->option_name => 1 ) ); ?>">Export <?php echo $this->option_label; ?></a>
				<div id="pq-import-<?php echo $this->option_name; ?>-data" style="display:none;">
					<h3>Import <?php echo $this->option_label; ?></h3>
					<form method="post" id="pq-import-<?php echo $this->option_name; ?>-data-form" enctype="multipart/form-data">
						<input type="file" name="pq_<?php echo $this->option_name; ?>_import_file" />
						<br />
						<input type="submit" name="pq_import_<?php echo $this->option_name; ?>" class="button button-primary" value="Import" />
						<br />
						<p>All data is replaced, not merged.</p>
					</form>
				</div>
			</div>
			<?php
			$output = ob_get_clean();
			?>
			<script type="text/javascript">
				( function ( $ ) {
					$( document ).ready( () => {
						var html = '<?php echo str_replace( array( "\r", "\n", "\t" ), '', $output ); ?>';
						$( '.acf-settings-wrap>h1' ).after( html );
					} )
				}( jQuery ) );
			</script>
			<?php
			add_thickbox();
		}
	}

	public function import_from_csv()
	{
		$do_import = filter_input( INPUT_POST, 'pq_import_' . $this->option_name );
		if ( $this->is_this_options_screen() && $do_import ) {
			self::import( $this->option_name );
		}
	}

	public function export_to_csv()
	{
		$do_export = filter_input( INPUT_GET, 'pq_export_' . $this->option_name );
		if ( $this->is_this_options_screen() && $do_export ) {
			self::export( $this->option_name, $this->model_name );
		}
	}

	public static function import( $option_name )
	{
		$file = $_FILES['pq_' . $option_name . '_import_file']['tmp_name'];
		$csv = Reader::createFromPath( $file, 'r' );
		$csv->setHeaderOffset( 0 );
		$records = $csv->getRecords();
		$array = iterator_to_array( $records );

		update_field( $option_name, $array, 'option' );
	}

	public static function export( $option_name, $model_name )
	{
		$Class = $model_name;
		$Model = new $Class( 0, [] );
		$keys = $Model->get_property_keys();
		if ( ( $key = array_search( 'id', $keys ) ) !== false ) {
			unset( $keys[$key] );
		}

		$encoder = ( new CharsetConverter() )
			->inputEncoding( 'utf-8' )
			->outputEncoding( 'iso-8859-15' )
		;

		$csv = Writer::createFromString();
		$csv->addFormatter( $encoder );
		$csv->insertOne( $keys );

		$options = get_field( $option_name, 'option' );
		if ( ! empty( $options ) ) {
			foreach ( $options as $raw_option ) {
				$option = array_map( function( $opt ) {
					if ( is_array( $opt ) ) {
						return implode( ',', $opt );
					} else {
						return (string) $opt;
					}
				}, $raw_option );
				$csv->insertOne( $option );
				$i++;
			}
			$csv->output( 'wp-pq-' . $option_name . '.csv' );
			die;
		}
	}

	private function is_this_options_screen()
	{
		global $pagenow;
		return ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'acf-options-' . $this->option_page == $_GET['page'] ) ? true : false;
	}

}
