<?php
/**
 * Branding module.
 *
 * @package Ultimate_Dashboard
 */

namespace Udb\Branding;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Module;

/**
 * Class to setup branding module.
 */
class Branding_Module extends Base_Module {

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

		$this->url = ULTIMATE_DASHBOARD_PLUGIN_URL . '/modules/branding';

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
	 * Setup branding module.
	 */
	public function setup() {

		add_action( 'admin_menu', array( $this, 'submenu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_init', array( $this, 'add_settings' ) );

		// The module output.
		require_once __DIR__ . '/class-branding-output.php';
		$output = new Branding_Output();
		$output->setup();

	}

	/**
	 * Add submenu page.
	 */
	public function submenu_page() {

		add_submenu_page( 'edit.php?post_type=udb_widgets', __( 'White Label', 'ultimate-dashboard' ), __( 'White Label', 'ultimate-dashboard' ), apply_filters( 'udb_settings_capability', 'manage_options' ), 'udb_branding', array( $this, 'submenu_page_content' ) );

	}

	/**
	 * Submenu page content.
	 */
	public function submenu_page_content() {

		$template = require __DIR__ . '/templates/branding-template.php';
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

		// Register setting.
		register_setting( 'udb-branding-group', 'udb_branding' );

		// Sections.
		add_settings_section( 'udb-branding-section', __( 'WordPress Admin Branding', 'ultimate-dashboard' ), '', 'udb-branding-settings' );
		add_settings_section( 'udb-admin-colors-section', __( 'WordPress Admin Colors', 'ultimate-dashboard' ), '', 'udb-admin-colors-settings' );
		add_settings_section( 'udb-admin-logo-section', __( 'WordPress Admin Logo', 'ultimate-dashboard' ), '', 'udb-admin-logo-settings' );
		add_settings_section( 'udb-branding-misc-section', __( 'Misc', 'ultimate-dashboard' ), '', 'udb-branding-misc-settings' );

		// Branding fields.
		add_settings_field( 'udb-branding-enable-field', __( 'Enable', 'ultimate-dashboard' ), array( $this, 'enable_field' ), 'udb-branding-settings', 'udb-branding-section' );
		add_settings_field( 'udb-branding-layout-field', __( 'Layout', 'ultimate-dashboard' ), array( $this, 'choose_layout_field' ), 'udb-branding-settings', 'udb-branding-section' );

		// Admin colors fields.
		add_settings_field( 'udb-menu-item-color-field', __( 'Menu Item Color', 'ultimate-dashboard' ), array( $this, 'menu_item_color_field' ), 'udb-admin-colors-settings', 'udb-admin-colors-section' );
		add_settings_field( 'udb-accent-color-field', __( 'Accent Color', 'ultimate-dashboard' ), array( $this, 'accent_color_field' ), 'udb-admin-colors-settings', 'udb-admin-colors-section' );
		add_settings_field( 'udb-admin-bar-bg-color-field', __( 'Admin Bar Bg Color', 'ultimate-dashboard' ), array( $this, 'admin_bar_color_field' ), 'udb-admin-colors-settings', 'udb-admin-colors-section' );
		add_settings_field( 'udb-admin-menu-bg-color-field', __( 'Admin Menu Bg Color', 'ultimate-dashboard' ), array( $this, 'admin_menu_bg_color_field' ), 'udb-admin-colors-settings', 'udb-admin-colors-section' );
		add_settings_field( 'udb-admin-submenu-bg-color-field', __( 'Admin Submenu Bg Color', 'ultimate-dashboard' ), array( $this, 'admin_submenu_bg_color_field' ), 'udb-admin-colors-settings', 'udb-admin-colors-section' );

		add_settings_field( 'udb-branding-admin-bar-logo-image-field', __( 'Admin Bar Logo', 'ultimate-dashboard' ), array( $this, 'admin_bar_logo_field' ), 'udb-admin-logo-settings', 'udb-admin-logo-section' );
		add_settings_field( 'udb-branding-admin-bar-logo-url-field', __( 'Admin Bar Logo URL', 'ultimate-dashboard' ), array( $this, 'admin_bar_logo_url_field' ), 'udb-admin-logo-settings', 'udb-admin-logo-section' );

		// Misc fields.
		add_settings_field( 'udb-branding-footer-text-field', __( 'Footer Text', 'ultimate-dashboard' ), array( $this, 'footer_text_field' ), 'udb-branding-misc-settings', 'udb-branding-misc-section' );
		add_settings_field( 'udb-branding-version-text-field', __( 'Version Text', 'ultimate-dashboard' ), array( $this, 'version_text_field' ), 'udb-branding-misc-settings', 'udb-branding-misc-section' );

	}

	/**
	 * Enable branding field.
	 */
	public function enable_field() {

		$template = __DIR__ . '/templates/fields/enable.php';
		$template = apply_filters( 'udb_branding_enable_feature_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Choose layout field.
	 */
	public function choose_layout_field() {

		$template = __DIR__ . '/templates/fields/choose-layout.php';
		$template = apply_filters( 'udb_branding_choose_layout_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin bar logo field.
	 */
	public function admin_bar_logo_field() {

		$template = __DIR__ . '/templates/fields/admin-bar-logo.php';
		$template = apply_filters( 'udb_branding_admin_bar_logo_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin bar logo url field.
	 */
	public function admin_bar_logo_url_field() {

		$template = __DIR__ . '/templates/fields/admin-bar-logo-url.php';
		$template = apply_filters( 'udb_branding_admin_bar_logo_url_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Accent color field.
	 */
	public function accent_color_field() {

		$template = __DIR__ . '/templates/fields/accent-color.php';
		$template = apply_filters( 'udb_branding_accent_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin bar bg color field.
	 */
	public function admin_bar_color_field() {

		$template = __DIR__ . '/templates/fields/admin-bar-bg-color.php';
		$template = apply_filters( 'udb_branding_admin_bar_bg_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin menu bg color field.
	 */
	public function admin_menu_bg_color_field() {

		$template = __DIR__ . '/templates/fields/admin-menu-bg-color.php';
		$template = apply_filters( 'udb_branding_admin_menu_bg_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin submenu bg color field.
	 */
	public function admin_submenu_bg_color_field() {

		$template = __DIR__ . '/templates/fields/admin-submenu-bg-color.php';
		$template = apply_filters( 'udb_branding_admin_submenu_bg_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin menu item color field.
	 */
	public function menu_item_color_field() {

		$template = __DIR__ . '/templates/fields/menu-item-color.php';
		$template = apply_filters( 'udb_branding_menu_item_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Admin menu item active color field.
	 */
	public function menu_item_active_color_field() {

		$template = __DIR__ . '/templates/fields/menu-item-active-color.php';
		$template = apply_filters( 'udb_branding_menu_item_active_color_field_path', $template );
		$field    = require $template;

		$field();

	}

	/**
	 * Footer text field.
	 */
	public function footer_text_field() {

		$template = __DIR__ . '/templates/fields/footer-text.php';
		$field    = require $template;

		$field();

	}

	/**
	 * Version text field.
	 */
	public function version_text_field() {

		$template = __DIR__ . '/templates/fields/version-text.php';
		$field    = require $template;

		$field();

	}

}
