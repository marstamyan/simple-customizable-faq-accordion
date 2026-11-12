<?php
/**
 * FAQ design settings shared by the Customizer and generated styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep theme modification names compatible with existing installations.
 *
 * @return array
 */
function mk_simple_faq_customizer_settings() {
	return array(
		'mk_faq_title_background_color' => array(
			'label'    => __( 'Title background color', 'simple-customizable-faq-accordion' ),
			'default'  => '#38cc70',
			'property' => '--mk-faq-title-background-color',
			'type'     => 'color',
		),
		'mk_faq_title_background_color_hover' => array(
			'label'    => __( 'Title hover background color', 'simple-customizable-faq-accordion' ),
			'default'  => '#2ba659',
			'property' => '--mk-faq-title-hover-color',
			'type'     => 'color',
		),
		'mk_faq_text_background_color' => array(
			'label'    => __( 'Text background color', 'simple-customizable-faq-accordion' ),
			'default'  => '#ffffff',
			'property' => '--mk-faq-background-color',
			'type'     => 'color',
		),
		'mk_faq_title_color' => array(
			'label'    => __( 'Title color', 'simple-customizable-faq-accordion' ),
			'default'  => '#0f0f0f',
			'property' => '--mk-faq-title-color',
			'type'     => 'color',
		),
		'mk_faq_title_size' => array(
			'label'    => __( 'Title font size', 'simple-customizable-faq-accordion' ),
			'default'  => 18,
			'property' => '--mk-faq-title-font-size',
			'type'     => 'range',
			'min'      => 12,
			'max'      => 48,
			'step'     => 1,
		),
		'mk_faq_text_color' => array(
			'label'    => __( 'Text color', 'simple-customizable-faq-accordion' ),
			'default'  => '#0f0f0f',
			'property' => '--mk-faq-text-color',
			'type'     => 'color',
		),
		'mk_faq_text_size' => array(
			'label'    => __( 'Text font size', 'simple-customizable-faq-accordion' ),
			'default'  => 16,
			'property' => '--mk-faq-text-font-size',
			'type'     => 'range',
			'min'      => 12,
			'max'      => 48,
			'step'     => 1,
		),
		'mk_faq_border_line' => array(
			'label'    => __( 'Border color', 'simple-customizable-faq-accordion' ),
			'default'  => '#2caf5d',
			'property' => '--mk-faq-title-border-color',
			'type'     => 'color',
		),
		'mk_faq_block_size' => array(
			'label'    => __( 'FAQ block size', 'simple-customizable-faq-accordion' ),
			'default'  => 480,
			'property' => '--mk-faq-accordion-max-width',
			'type'     => 'range',
			'min'      => 320,
			'max'      => 1200,
			'step'     => 10,
		),
	);
}

/**
 * Sanitize both newly submitted and previously stored design values.
 *
 * @param mixed  $value Value to sanitize.
 * @param string $id    Theme modification name.
 * @return int|string|null
 */
function mk_simple_faq_sanitize_design_value( $value, $id ) {
	$settings = mk_simple_faq_customizer_settings();
	if ( ! isset( $settings[ $id ] ) ) {
		return null;
	}

	$setting = $settings[ $id ];
	if ( 'color' === $setting['type'] ) {
		$color = is_string( $value ) ? sanitize_hex_color( $value ) : null;
		return $color ? $color : $setting['default'];
	}

	if ( ! is_scalar( $value ) || ! preg_match( '/^-?\d+$/D', (string) $value ) ) {
		return $setting['default'];
	}

	return min( $setting['max'], max( $setting['min'], (int) $value ) );
}

/**
 * Customizer sanitization callback (WordPress supplies the setting object).
 *
 * @param mixed                $value   Submitted value.
 * @param WP_Customize_Setting $setting Setting being saved.
 * @return int|string|null
 */
function mk_simple_faq_sanitize_customizer_value( $value, $setting ) {
	return mk_simple_faq_sanitize_design_value( $value, $setting->id );
}

/**
 * Register controls using the same defaults and ranges used on the front end.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function mk_simple_faq_customization( $wp_customize ) {
	$wp_customize->add_section( 'mk_faq_section', array(
		'title'    => __( 'FAQ Customization', 'simple-customizable-faq-accordion' ),
		'priority' => 30,
	) );

	foreach ( mk_simple_faq_customizer_settings() as $id => $setting ) {
		$wp_customize->add_setting( $id, array(
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'default'           => $setting['default'],
			'transport'         => 'postMessage',
			'sanitize_callback' => 'mk_simple_faq_sanitize_customizer_value',
		) );

		$args = array(
			'label'    => $setting['label'],
			'section'  => 'mk_faq_section',
			'settings' => $id,
		);

		if ( 'color' === $setting['type'] ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $args ) );
			continue;
		}

		$args['type'] = 'range';
		$args['input_attrs'] = array(
			'min'  => $setting['min'],
			'max'  => $setting['max'],
			'step' => $setting['step'],
		);
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $id, $args ) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $id . '_display', array(
			'label'       => __( 'Current value (pixels)', 'simple-customizable-faq-accordion' ),
			'section'     => 'mk_faq_section',
			'settings'    => $id,
			'type'        => 'text',
			'input_attrs' => array( 'readonly' => 'readonly' ),
		) ) );
	}

	// The preview script supplies its own shortcut, including on themes without
	// customize-selective-refresh-widgets support. No partial replaces FAQ markup.
}

add_action( 'customize_register', 'mk_simple_faq_customization' );
