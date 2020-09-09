<?php
/**
 * Login customizer output.
 *
 * @package Ultimate_Dashboard
 */

namespace Udb\LoginCustomizer;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Output as Base_Output;

/**
 * Class to setup login customizer output.
 */
class Output extends Base_Output {

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
	 * Setup login customizer output.
	 */
	public function setup() {

		add_filter( 'login_headertext', array( $this, 'login_headertext' ), 20 );
		add_filter( 'login_headerurl', array( $this, 'login_logo_url' ), 20 );
		add_action( 'login_head', array( $this, 'print_login_styles' ), 20 );
		add_action( 'login_head', array( $this, 'print_login_live_styles' ), 20 );

	}

	/**
	 * Set login page header text.
	 *
	 * @param string $text The existing header text.
	 * @return string The modified header text.
	 */
	public function login_headertext( $text ) {

		$login = get_option( 'udb_login', array() );
		$text  = isset( $login['logo_title'] ) && ! empty( $login['logo_title'] ) ? $login['logo_title'] : $text;

		return $text;

	}

	/**
	 * Change login logo url.
	 *
	 * @param string $url The existing login logo url.
	 * @return string The modified login logo url.
	 */
	public function login_logo_url( $url ) {

		$login = get_option( 'udb_login', array() );

		if ( isset( $login['logo_url'] ) && ! empty( $login['logo_url'] ) ) {
			$url = str_ireplace( '{home_url}', home_url(), $login['logo_url'] );
		}

		return $url;

	}

	/**
	 * Print login styles.
	 */
	public function print_login_styles() {

		echo '<style>';
		ob_start();
		require __DIR__ . '/assets/css/login.css.php';

		$css = ob_get_clean();

		echo apply_filters( 'udb_print_login_styles', $css );
		echo '</style>';

	}

	/**
	 * Print login live styles.
	 */
	public function print_login_live_styles() {

		if ( ! is_customize_preview() ) {
			return;
		}

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[logo_height]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[bg_color]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_position]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_bg_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_width]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_top_padding]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_bottom_padding]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_horizontal_padding]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_border_width]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_border_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[form_border_radius]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_height]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_horizontal_padding]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_border_width]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_border_radius]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_text_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_text_color_focus]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_bg_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_bg_color_focus]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_border_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[fields_border_color_focus]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[labels_color]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_height]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_horizontal_padding]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_text_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_text_color_hover]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_bg_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_bg_color_hover]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[button_border_radius]"></style>';

		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[footer_link_color]"></style>';
		echo '<style class="udb-login-customizer-live-style" data-listen-value="udb_login[footer_link_color_hover]"></style>';

	}

}