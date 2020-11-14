<?php
/**
 * Dashboard module.
 *
 * @package Ultimate_Dashboard
 */

namespace Udb\Dashboard;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;

/**
 * Class to setup dashboard module.
 */
class Dashboard_Module extends Base_Module {

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * The current module url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Module constructor.
	 */
	public function __construct() {

		$this->url = ULTIMATE_DASHBOARD_PLUGIN_URL . '/modules/dashboard';

		// Make defaults modules are available
		if ( ! get_option( 'udb_modules' ) ) {
			$modules = apply_filters( 'udb_dashboard_default_modules', array(
				'white_label'       => "true",
				'login_customizer'  => "true",
				'admin_pages'       => "true",
				'admin_menu_editor' => "true",
			) );
			update_option( 'udb_modules', serialize( $modules ) );
		}

	}

	/**
	 * Get instance of the class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Setup dashboard module.
	 */
	public function setup() {

		add_action( 'admin_menu', array( $this, 'submenu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'wp_ajax_udb_handle_module_options', array( $this, 'handle_module_options' ) );

		// The module output.
		require_once __DIR__ . '/class-dashboard-output.php';
		$output = new Dashboard_Output();
		$output->setup();

	}

	/**
	 * Add submenu page.
	 */
	public function submenu_page() {

		add_submenu_page( 'edit.php?post_type=udb_widgets', 'Feature Dashboard', 'Dashboard', apply_filters( 'udb_settings_capability', 'manage_options' ), 'udb_dashboard', array( $this, 'submenu_page_content' ) );

	}

	/**
	 * Submenu page content.
	 */
	public function submenu_page_content() {

		$template = require __DIR__ . '/templates/dashboard-template.php';
		$template();

	}

	/**
	 * Enqueue admin styles.
	 */
	public function admin_styles() {

		$enqueue = require __DIR__ . '/inc/css-enqueue.php';
		$enqueue( $this );

	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_scripts() {

		$enqueue = require __DIR__ . '/inc/js-enqueue.php';
		$enqueue( $this );

	}

	public function handle_module_options() {

		if ( empty( $_REQUEST ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'udb_modules_nonce_action' ) ) {
			die( wp_send_json_error( __( 'Invalid nonce', 'ultimate-dashboard' ), 400 ) );
		};

		$data = unserialize( get_option( 'udb_modules' ) );

		if ( $data ) {
			$name        = sanitize_key( $_REQUEST['name'] );
			$status      = sanitize_key( $_REQUEST['status'] );
			$data[$name] = $status;
			update_option( 'udb_modules', serialize( $data ) );
		}

		wp_send_json_success( ['message' => __( 'Saved', 'ultimate-dashboard' )] );

		die();
	}

	public static function get_module_prop( $name = '' ) {
		if ( empty( $name ) ) {
			return null;
		}

		$options = unserialize( get_option( 'udb_modules' ) );

		if ( empty( $options ) ) {
			return 1;
		}

		return $options[$name] === "true" ? 1 : 0;
	}

}