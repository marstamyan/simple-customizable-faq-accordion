<?php
function mk_simple_faq_customization( $wp_customize ) {
	$wp_customize->add_section( 'mk_faq_section', array(
		'title'    => __( 'FAQ Customization', MK_FAQ_TEXT_DOMAIN ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'mk_faq_section', array(
		'transport' => 'postMessage',
	) );

	// Title background color
	$wp_customize->add_setting( 'mk_faq_title_background_color', array(
		'default'           => '#38cc70',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_title_background_color', array(
		'label'    => esc_html__( 'Title background color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_title_background_color',
	) ) );

	// Title background color hover
	$wp_customize->add_setting( 'mk_faq_title_background_color_hover', array(
		'default'           => '#2ba659',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_title_background_color_hover', array(
		'label'    => esc_html__( 'Title hover background color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_title_background_color_hover',
	) ) );

	// Text background color
	$wp_customize->add_setting( 'mk_faq_text_background_color', array(
		'default'           => '#38cc70',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_text_background_color', array(
		'label'    => esc_html__( 'Text background color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_text_background_color',
	) ) );

	// Title color
	$wp_customize->add_setting( 'mk_faq_title_color', array(
		'default'           => '#fff',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_title_color', array(
		'label'    => esc_html__( 'Title color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_title_color',
	) ) );

	// Title font size
	$wp_customize->add_setting( 'mk_faq_title_size', array(
		'default'           => '18',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'mk_faq_title_size', array(
		'label'       => esc_html__( 'Title font size', MK_FAQ_TEXT_DOMAIN ),
		'section'     => 'mk_faq_section',
		'settings'    => 'mk_faq_title_size',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 48,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'mk_faq_title_size_display', array(
		'label'       => '',
		'section'     => 'mk_faq_section',
		'settings'    => 'mk_faq_title_size',
		'type'        => 'text',
		'input_attrs' => array(
			'readonly' => 'readonly',
		),
	) ) );

	// Text color
	$wp_customize->add_setting( 'mk_faq_text_color', array(
		'default'           => '#0f0f0f',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_text_color', array(
		'label'    => esc_html__( 'Text color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_text_color',
	) ) );

	// Text font size
	$wp_customize->add_setting( 'mk_faq_text_size', array(
		'default'           => '16',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'mk_faq_text_size', array(
		'label'       => esc_html__( 'Text font size', MK_FAQ_TEXT_DOMAIN ),
		'section'     => 'mk_faq_section',
		'settings'    => 'mk_faq_text_size',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 48,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'mk_faq_text_size_display', array(
		'label'       => '',
		'section'     => 'mk_faq_section',
		'settings'    => 'mk_faq_text_size',
		'type'        => 'text',
		'input_attrs' => array(
			'readonly' => 'readonly',
		),
	) ) );

	// Border line
	$wp_customize->add_setting( 'mk_faq_border_line', array(
		'default'           => '#2caf5d',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mk_faq_border_line', array(
		'label'    => esc_html__( 'Border color', MK_FAQ_TEXT_DOMAIN ),
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_border_line',
	) ) );

	// pen
	$wp_customize->add_setting( 'mk_faq_customize_trigger', array(
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->selective_refresh->add_partial( 'mk_faq_customize_trigger_partial', array(
		'selector'         => '#mk-faq-container',
		'settings'         => 'mk_faq_customize_trigger',
		'section'          => 'mk_faq_section',
		'transport'        => 'postMessage',
		'render_callback'  => '__return_empty_string',
		'fallback_refresh' => false,
	) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'mk_faq_customize_trigger_control', array(
		'label'    => '',
		'section'  => 'mk_faq_section',
		'settings' => 'mk_faq_customize_trigger',
		'type'     => 'hidden',
	) ) );
}

add_action( 'customize_register', 'mk_simple_faq_customization' );
