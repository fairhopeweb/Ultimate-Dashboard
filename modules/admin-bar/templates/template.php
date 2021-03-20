<?php
/**
 * Admin menu page template.
 *
 * @package Ultimate Dashboard PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Vars;

$existing_menu = Vars::get( 'existing_admin_bar_menu' );
$existing_menu = $this->nodes_to_array( $existing_menu );

$saved_menu  = get_option( 'udb_admin_bar', array() );
$parsed_menu = ! $saved_menu ? $existing_menu : $this->parse_menu( $saved_menu, $existing_menu );

wp_localize_script(
	'udb-admin-bar',
	'udbAdminBarBuilder',
	array(
		'existingMenu' => $existing_menu,
		'builderItems' => $this->to_builder_format( $parsed_menu ),
	)
);
?>

<div class="wrap heatbox-wrap udb-admin-bar-editor-page">

	<div class="heatbox-header heatbox-margin-bottom">

		<div class="heatbox-container heatbox-container-center">

			<div class="logo-container">

				<div>
					<span class="title">
						<?php echo esc_html( get_admin_page_title() ); ?>
						<span class="version"><?php echo esc_html( ULTIMATE_DASHBOARD_PLUGIN_VERSION ); ?></span>
					</span>
					<p class="subtitle"><?php _e( 'Fully customize the WordPress admin bar.', 'ultimate-dashboard' ); ?></p>
				</div>

				<div>
					<img src="<?php echo esc_url( ULTIMATE_DASHBOARD_PLUGIN_URL ); ?>/assets/img/logo.png">
				</div>

			</div>

		</div>

	</div>

	<div class="heatbox-container heatbox-container-center">

		<h1 style="display: none;"></h1>

		<?php if ( ! udb_is_pro_active() ) : ?>

			<div class="udb-pro-admin-bar-nag">
				<p><?php _e( 'This feature is available in Ultimate Dashboard PRO.', 'ultimate-dashboard' ); ?></p>
				<a href="https://ultimatedashboard.io/pro/?utm_source=plugin&utm_medium=admin_bar_link&utm_campaign=udb" class="button button-large button-primary" target="_blank">
					<?php _e( 'Get Ultimate Dashboard PRO', 'ultimate-dashboard' ); ?>
				</a>
			</div>

		<?php endif; ?>

		<?php do_action( 'udb_admin_bar_before_form' ); ?>

		<form action="options.php" method="post" class="udb-admin-bar--edit-form">

			<div class="heatbox udb-admin-bar-box">

				<div class="udb-admin-bar-box--header">
					<h2 class="udb-admin-bar-box--title">
						<?php _e( 'Admin Bar Editor', 'ultimate-dashboard' ); ?>
					</h2>

					<?php do_action( 'udb_admin_bar_header' ); ?>
				</div>

				<div class="udb-admin-bar--edit-area">
					<div id="udb-admin-bar--workspace" class="udb-admin-bar--workspace">
						<ul class="udb-admin-bar--menu-list">
							<!-- to be re-written via js -->
							<li class="loading"></li>
						</ul>

						<?php do_action( 'udb_admin_bar_add_menu_button' ); ?>
					</div>
				</div>

				<div class="heatbox-footer">

					<?php if ( ! udb_is_pro_active() ) : ?>

						<div class="udb-pro-settings-page-notice udb-pro-admin-bar-notice">
							<p><?php _e( 'This feature is available in Ultimate Dashboard PRO.', 'ultimate-dashboard' ); ?></p>
							<a href="https://ultimatedashboard.io/pro/?utm_source=plugin&utm_medium=admin_bar_link&utm_campaign=udb" class="button button-large button-primary" target="_blank">
								<?php _e( 'Get Ultimate Dashboard PRO', 'ultimate-dashboard' ); ?>
							</a>
						</div>

					<?php endif; ?>

					<?php do_action( 'udb_admin_bar_form_footer' ); ?>

				</div>
			</div>

		</form>

	</div>

</div>
