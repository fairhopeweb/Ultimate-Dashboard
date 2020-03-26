<?php
/**
 * Form button section of Login Customizer.
 *
 * @var $wp_customize This variable is brought from login-customizer.php file.
 * @var $branding This variable is brought from login-customizer.php file.
 * @var $branding_enabled This variable is brought from login-customizer.php file.
 * @var $accent_color This variable is brought from login-customizer.php file.
 *
 * @package Ultimate Dashboard PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Udb_Customize_Color_Control;
use Udb\Udb_Customize_Range_Control;

$wp_customize->add_setting(
	'udb_login[button_height]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '35px',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'esc_attr',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Range_Control(
		$wp_customize,
		'udb_login[button_height]',
		array(
			'type'        => 'range',
			'section'     => 'udb_login_customizer_button_section',
			'settings'    => 'udb_login[button_height]',
			'label'       => __( 'Button Height', 'ultimate-dashboard' ),
			'input_attrs' => array(
				'min'  => 20,
				'max'  => 80,
				'step' => 1,
			),
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_horizontal_padding]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '15px',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'esc_attr',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Range_Control(
		$wp_customize,
		'udb_login[button_horizontal_padding]',
		array(
			'type'        => 'range',
			'section'     => 'udb_login_customizer_button_section',
			'settings'    => 'udb_login[button_horizontal_padding]',
			'label'       => __( 'Button Side Padding', 'ultimate-dashboard' ),
			'input_attrs' => array(
				'min'  => 10,
				'max'  => 60,
				'step' => 1,
			),
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_border_radius]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '3px',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'esc_attr',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Range_Control(
		$wp_customize,
		'udb_login[button_border_radius]',
		array(
			'type'        => 'range',
			'section'     => 'udb_login_customizer_button_section',
			'settings'    => 'udb_login[button_border_radius]',
			'label'       => __( 'Button Border Radius', 'ultimate-dashboard' ),
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 50,
				'step' => 1,
			),
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_text_color]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '#ffffff',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Color_Control(
		$wp_customize,
		'udb_login[button_text_color]',
		array(
			'label'    => __( 'Button Text Color', 'ultimate-dashboard' ),
			'section'  => 'udb_login_customizer_button_section',
			'settings' => 'udb_login[button_text_color]',
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_text_color_hover]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => '#ffffff',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Color_Control(
		$wp_customize,
		'udb_login[button_text_color_hover]',
		array(
			'label'    => __( 'Button Text Color (Hover)', 'ultimate-dashboard' ),
			'section'  => 'udb_login_customizer_button_section',
			'settings' => 'udb_login[button_text_color_hover]',
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_bg_color]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => ( $has_accent_color ? $accent_color : '#007cba' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Color_Control(
		$wp_customize,
		'udb_login[button_bg_color]',
		array(
			'label'    => __( 'Button Background Color', 'ultimate-dashboard' ),
			'section'  => 'udb_login_customizer_button_section',
			'settings' => 'udb_login[button_bg_color]',
		)
	)
);

$wp_customize->add_setting(
	'udb_login[button_bg_color_hover]',
	array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'default'           => ( $has_accent_color ? $accent_color : '#007cba' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new Udb_Customize_Color_Control(
		$wp_customize,
		'udb_login[button_bg_color_hover]',
		array(
			'label'    => __( 'Button Background Color (Hover)', 'ultimate-dashboard' ),
			'section'  => 'udb_login_customizer_button_section',
			'settings' => 'udb_login[button_bg_color_hover]',
		)
	)
);
