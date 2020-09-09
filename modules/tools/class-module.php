<?php
/**
 * Tools module.
 *
 * @package Ultimate_Dashboard
 */

namespace Udb\Tools;

use Udb\Base\Module as Base_Module;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Class to setup tools module.
 */
class Module extends Base_Module {
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

		$this->url = ULTIMATE_DASHBOARD_PLUGIN_URL . '/modules/tools';

	}

	/**
	 * Setup tools module.
	 */
	public function setup() {

		add_action( 'admin_menu', array( $this, 'submenu_page' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_init', array( $this, 'add_settings' ) );

	}

	/**
	 * Settings page.
	 */
	public function submenu_page() {
		add_submenu_page( 'edit.php?post_type=udb_widgets', 'Tools', 'Tools', 'manage_options', 'udb_tools', array( $this, 'submenu_page_content' ) );
	}

	/**
	 * Settings page callback.
	 */
	public function submenu_page_content() {

		$template = require __DIR__ . '/templates/tools-template.php';
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

	/**
	 * Add settings.
	 */
	public function add_settings() {

		// Settings groups.
		register_setting( 'udb-export-group', 'udb_export', array( 'sanitize_callback' => array( $this, 'process_export' ) ) );
		register_setting( 'udb-import-group', 'udb_import', array( 'sanitize_callback' => array( $this, 'process_import' ) ) );

		// Settings sections.
		add_settings_section( 'udb-export-section', __( 'Export Widgets', 'ultimate-dashboard' ), '', 'ultimate-dashboard-export' );
		add_settings_section( 'udb-import-section', __( 'Import Widgets', 'ultimate-dashboard' ), '', 'ultimate-dashboard-import' );

		// Settings fields.
		add_settings_field( 'udb-export-field', '', array( $this, 'render_export_field' ), 'ultimate-dashboard-export', 'udb-export-section', array( 'class' => 'is-gapless has-small-text' ) );
		add_settings_field( 'udb-import-field', '', array( $this, 'render_import_field' ), 'ultimate-dashboard-import', 'udb-import-section', array( 'class' => 'is-gapless has-small-text' ) );

	}

	/**
	 * Render export field.
	 *
	 * @param array $args The setting's arguments.
	 */
	public function render_export_field( $args ) {

		$field = require __DIR__ . '/templates/fields/export-field.php';
		$field();

	}

	/**
	 * Render import field.
	 *
	 * @param array $args The setting's arguments.
	 */
	public function render_import_field( $args ) {

		$field = require __DIR__ . '/templates/fields/import-field.php';
		$field();

	}

	/**
	 * Process the export.
	 */
	public function process_export() {

		$process = require __DIR__ . '/inc/process-export.php';
		$process();

	}

	/**
	 * Process the import.
	 */
	public function process_import() {

		$process = require __DIR__ . '/inc/process-import.php';
		$process();

	}

}