<?php

/**
 * Plugin Name: PolyCoat CPQ
 * Plugin URI: https://polycoatcpq.com/
 * Description: PolyCoat Configure, Price, Quote Tool
 * Version: 1.5.0
 * Author: The Snowberry Team
 * Author URI: https://snowberrymedia.com/
 *
 * Text Domain: pc-cpq
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

final class PC_CPQ
{
	/**
	 * @var string
	 */
	public $name = 'PolyCoat CPQ';
	
	/**
	 * @var string
	 */
	public $version = '1.5.0';

	/**
	 * @var string
	 */
	public $domain = 'pc-cpq';

	/**
	 * Factory for returning lead
	 * @var null
	 */
	private $lead_factory = null;

	/**
	 * Factory for returning customer
	 * @var null
	 */
	private $customer_factory = null;

	/**
	 * Part lookup service
	 * @var null
	 */
	public $part_lookup = null;
	
	/**
	 * Site settings
	 * @var null
	 */
	public $settings = null;

	/**
	 * Plugin instance.
	 * @see instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * Static Singleton Factory Method
	 * @return self returns a single instance of our class
	 */
	public static function instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PC_CPQ;
		}
		return self::$instance;
	}

	/**
	 * Initiate the plugin
	 * @return void
	 */
	protected function __construct()
	{
		$this->define_constants();
		add_action( 'plugins_loaded', array( $this, 'setup' ), -15 );
	}

	public function setup()
	{
		$this->includes();
		$this->init_factories();
		$this->init_services();
	}

	/**
	 * Return the Model of an lead item
	 * @param  mixed $lead_item    item
	 * @return [type]          [description]
	 */
	public function lead( $lead_item = false )
	{
		return $this->lead_factory->get( $lead_item );
	}
	
	/**
	 * Return the Model of a Quote
	 * @param  object $Lead
	 */
	public function Quote( \PC_CPQ\Models\Lead $Lead )
	{
		return new \PC_CPQ\Models\Quote( $Lead );
	}

	/**
	 * Return the Model of an customer item
	 * @param  mixed $customer_item    item
	 * @return [type]          [description]
	 */
	public function customer( $customer_item = false )
	{
		return $this->customer_factory->get( $customer_item );
	}

	/**
	 * Define constant if not already set
	 * @author  woocommerce
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value )
	{
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @var string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type )
	{
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Define Constants
	 */
	private function define_constants()
	{
		$this->define( 'DS', DIRECTORY_SEPARATOR );
		$this->define( 'PC_CPQ_PLUGIN_FILE', __FILE__ );
		$this->define( 'PC_CPQ_NAME', $this->name );
		$this->define( 'PC_CPQ_VERSION', $this->version );
		$this->define( 'PC_CPQ_DOMAIN', $this->domain );
		$this->define( 'PC_CPQ_DEBUG', true );

		$this->define( 'WPMVC_ACTION_SCHEDULER_IS_ENABLED', false );
	}

	/**
	 * Include required files
	 * @return void
	 */
	private function includes()
	{
		// core
		include_once $this->plugin_path() . '/includes/core/custom-fields.php'; // load fields early

		// helpers
		include_once $this->plugin_path() . '/includes/helpers/general-functions.php';
		include_once $this->plugin_path() . '/includes/helpers/template-functions.php';
		include_once $this->plugin_path() . '/includes/helpers/csv-import-export-options.php';
		include_once $this->plugin_path() . '/includes/helpers/access.php';
		include_once $this->plugin_path() . '/includes/helpers/constants.php';
		include_once $this->plugin_path() . '/includes/helpers/defaults.php';
		include_once $this->plugin_path() . '/includes/helpers/form-handler.php';
		include_once $this->plugin_path() . '/includes/helpers/geometry.php';
		include_once $this->plugin_path() . '/includes/helpers/quotes.php';
		include_once $this->plugin_path() . '/includes/helpers/translate.php';
		include_once $this->plugin_path() . '/includes/helpers/utilities.php';
		
		// models
		include_once $this->plugin_path() . '/includes/models/Site.php';
		include_once $this->plugin_path() . '/includes/models/Settings.php';
		include_once $this->plugin_path() . '/includes/models/settings/Email_Template.php';
		include_once $this->plugin_path() . '/includes/models/settings/Metal.php';
		include_once $this->plugin_path() . '/includes/models/settings/Plating_Metal.php';
		include_once $this->plugin_path() . '/includes/models/settings/Line.php';
		include_once $this->plugin_path() . '/includes/models/settings/Barrel.php';
		include_once $this->plugin_path() . '/includes/models/settings/Rack.php';
		include_once $this->plugin_path() . '/includes/models/settings/Operation.php';
		include_once $this->plugin_path() . '/includes/models/Quote.php';
		include_once $this->plugin_path() . '/includes/models/Lead.php';
		include_once $this->plugin_path() . '/includes/models/Lead_Quantity.php';
		include_once $this->plugin_path() . '/includes/models/Part.php';
		include_once $this->plugin_path() . '/includes/models/Part_Process.php';
		include_once $this->plugin_path() . '/includes/models/Part_Routing.php';
		include_once $this->plugin_path() . '/includes/models/Part_Plating_Tool.php';
		include_once $this->plugin_path() . '/includes/models/Part_Pricing.php';
		include_once $this->plugin_path() . '/includes/models/Part_Pricing_Inputs.php';
		include_once $this->plugin_path() . '/includes/models/Part_Quantity.php';
		include_once $this->plugin_path() . '/includes/models/Customer.php';
		include_once $this->plugin_path() . '/includes/models/Customer_Contact.php';
		include_once $this->plugin_path() . '/includes/models/Customer_Shipping.php';
		include_once $this->plugin_path() . '/includes/models/Input.php';

		// core
		include_once $this->plugin_path() . '/includes/core/post-types.php';
		include_once $this->plugin_path() . '/includes/core/user-roles.php';
		include_once $this->plugin_path() . '/includes/core/abstracts/factory.php';
		include_once $this->plugin_path() . '/includes/core/lead-factory.php';
		include_once $this->plugin_path() . '/includes/core/customer-factory.php';
		include_once $this->plugin_path() . '/includes/core/part-lookup.php';
		include_once $this->plugin_path() . '/includes/core/nutshell-service.php';

		// frontend
		include_once $this->plugin_path() . '/includes/controllers/pc-cpq-custom-fields.php';
		include_once $this->plugin_path() . '/includes/controllers/pc-cpq-forms.php';
		include_once $this->plugin_path() . '/includes/controllers/pc-cpq-template.php';
		
		// manage
		include_once $this->plugin_path() . '/includes/controllers/manage/pc-cpq-manage.php';
		include_once $this->plugin_path() . '/includes/controllers/manage/pc-cpq-manage-lead.php';
		include_once $this->plugin_path() . '/includes/controllers/manage/pc-cpq-manage-customer.php';
		include_once $this->plugin_path() . '/includes/controllers/manage/pc-cpq-manage-settings.php';

		// admin
		include_once $this->plugin_path() . '/includes/controllers/admin/pc-cpq-lead-admin.php';
		include_once $this->plugin_path() . '/includes/controllers/admin/pc-cpq-plating-admin.php';
		include_once $this->plugin_path() . '/includes/controllers/admin/pc-cpq-processes-admin.php';

		// libraries
		require_once $this->plugin_path() . '/libraries/nutshell/NutshellApi.php';
	}

	/**
	 * Create factories to create new class instances
	 */
	public function init_factories()
	{
		$this->lead_factory = new \PC_CPQ\Core\Lead_Factory;
		$this->customer_factory = new \PC_CPQ\Core\Customer_Factory;
	}

	/**
	 * Init services
	 */
	public function init_services()
	{
		$this->part_lookup = new \PC_CPQ\Core\Part_Lookup;
		\PC_CPQ\Core\Part_Lookup::install();
	}

	/**
	 * Load the view
	 */
	public function view( $template, $data = array() )
	{
		if ( ! empty( $data ) ) {
			extract( $data );
		} else {
			$Lead = $this->lead( get_the_ID() );
		}

		ob_start();
		include $this->plugin_path() . '/includes/views/' . $template . '.php';
		return ob_get_clean();
	}

	/**
	 * Send an email with a loaded view
	 */
	public function email( $to, $subject, $template, $data = array(), $do_send = true )
	{
		if ( ! empty( $data ) ) {
			extract( $data );
		}

		$Email = new \PC_CPQ\Models\Email( $template );

		ob_start();
		include $this->plugin_path() . '/includes/views/emails/partials/header.php';
		include $this->plugin_path() . '/includes/views/emails/' . $template . '.php';
		include $this->plugin_path() . '/includes/views/emails/partials/footer.php';
		$message = ob_get_clean();

		if ( $do_send ) {
			$Email->send( $to, $subject, $message );
		} else {
			$Email->set_message( $message );
		}

		return $Email;
	}
	
	public function Site( $site_id = null )
	{
		if ( $site_id === null ) {
			$site_id = get_current_blog_id();
		}
		return new \PC_CPQ\Models\Site( $site_id );
	}
	
	public function Settings()
	{
		if ( null === $this->settings ) {
			$this->settings = new \PC_CPQ\Models\Settings();
		}
		return $this->settings;
	}

	public function debug_log()
	{
		$log_location = $this->log_path() . '/pc-cpq-debug.log';
		$args = func_get_args();
		$log = $this->log( $args );
		error_log( $log, 3, $log_location );
	}

	public function inspect()
	{
		$args = func_get_args();
		$log = $this->log( $args );
		echo '<pre>';
		echo $log;
		echo '</pre>';
	}

	private function log( $args )
	{
		$datetime = new \DateTime( 'NOW' );
		$timestamp = $datetime->format( 'Y-m-d H:i:s' );
		$formatted = array_map( function ( $item ) {
			return print_r( $item, true );
		}, $args );
		array_unshift( $formatted, $timestamp );
		return implode( ' ', $formatted ) . "\n";
	}

	public function is_debug_mode()
	{
		return $_SERVER['REMOTE_ADDR'] == '38.148.156.152' ? true : false;
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url()
	{
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path()
	{
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url()
	{
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Get the log path.
	 * @return string
	 */
	public function log_path()
	{
		return $this->plugin_path() . '/logs';
	}

}

function PC_CPQ()
{
	return PC_CPQ::instance();
}

PC_CPQ();
