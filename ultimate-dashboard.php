<?php
/**
 * Plugin Name: Ultimate Dashboard
 * Plugin URI: https://ultimatedashboard.io/
 * Description: Ultimate Dashboard gives you full control over your WordPress Dashboard. Remove the default Dashboard Widgets and and create your own for a better user experience.
 * Version: 2.3.1
 * Author: MapSteps
 * Author URI: https://mapsteps.com/
 * Text Domain: ultimate-dashboard
 * Domain Path: /languagess
 *
 * @package Ultimate Dashboard
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'ULTIMATE_DASHBOARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ULTIMATE_DASHBOARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Text domain
 */
function udb_textdomain() {
	load_plugin_textdomain( 'ultimate-dashboard', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'udb_textdomain' );

/**
 * Admin Scripts & Styles
 */
function udb_admin_scripts() {

	global $pagenow, $typenow;

	$plugin_data = get_plugin_data( __FILE__ );

	// Widget edit screen & create a new Widget screen.
	if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'udb_widgets' === $typenow ) {

		wp_register_style( 'font-awesome', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/font-awesome.min.css', array(), '4.7.0' );
		wp_enqueue_style( 'font-awesome' );

		wp_register_style( 'ultimate-dashboard-cpt', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/ultimate-dashboard-cpt.css', array(), $plugin_data['Version'] );
		wp_enqueue_style( 'ultimate-dashboard-cpt' );

		wp_register_script( 'ultimate-dashboard-cpt', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/js/ultimate-dashboard-cpt.js', array( 'jquery' ), $plugin_data['Version'], true );
		wp_enqueue_script( 'ultimate-dashboard-cpt' );
	}

	// Dashboard Widget Overview & Settings.
	if ( 'edit.php' === $pagenow && 'udb_widgets' === $typenow ) {

		wp_register_style( 'font-awesome', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/font-awesome.min.css', array(), '4.7.0' );
		wp_enqueue_style( 'font-awesome' );

		wp_register_style( 'ultimate-dashboard-settings', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/ultimate-dashboard-settings.css', array(), $plugin_data['Version'] );
		wp_enqueue_style( 'ultimate-dashboard-settings' );

	}

	// WordPress Dashboard.
	if ( 'index.php' === $pagenow ) {

		wp_register_style( 'font-awesome', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/font-awesome.min.css', array(), '4.7.0' );
		wp_enqueue_style( 'font-awesome' );

		wp_register_style( 'ultimate-dashboard-index', ULTIMATE_DASHBOARD_PLUGIN_URL . 'assets/css/ultimate-dashboard-index.css', array(), $plugin_data['Version'] );
		wp_enqueue_style( 'ultimate-dashboard-index' );
	}

	// Highlight PRO Link in Sub-Menu.
	echo '<style>#adminmenu #menu-posts-udb_widgets a[href="edit.php?post_type=udb_widgets&page=addons"] { color: tomato; }</style>';

}
add_action( 'admin_enqueue_scripts', 'udb_admin_scripts' );

/**
 * Action Links
 *
 * @param string $links Links.
 */
function udb_add_action_links( $links ) {
	$settings = array( '<a href="' . admin_url( 'edit.php?post_type=udb_widgets&page=settings' ) . '">' . __( 'Settings', 'ultimate-dashboard' ) . '</a>' );
	return array_merge( $links, $settings );

}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'udb_add_action_links' );

// Required Files.
require_once ULTIMATE_DASHBOARD_PLUGIN_DIR . 'inc/init.php';
