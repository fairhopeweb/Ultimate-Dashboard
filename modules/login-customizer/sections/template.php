<?php
/**
 * Templates section of Login Customizer.
 *
 * @var $wp_customize This variable is brought from login-customizer.php file.
 *
 * @package Ultimate Dashboard PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

use UdbPro\Udb_Customize_Login_Template_Control;

$wp_customize->add_setting(
	'udb_login[template]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Login_Template_Control(
		$wp_customize,
		'udb_login[template]',
		array(
			'label'    => __( 'Choose Template', 'ultimate-dashboard' ),
			'section'  => 'udb_login_customizer_template_section',
			'settings' => 'udb_login[template]',
		)
	)
);
