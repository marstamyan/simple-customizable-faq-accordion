<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'mk-simple-faq', 'mk_simple_faq_shortcode' );

/**
 * Render one published FAQ without changing the global WordPress post.
 */
function mk_simple_faq_shortcode( $atts, $content = '' ) {
	if ( is_admin() ) {
		return '';
	}

	$atts   = shortcode_atts( array( 'id' => 0 ), $atts, 'mk-simple-faq' );
	$id     = is_scalar( $atts['id'] ) ? ltrim( trim( (string) $atts['id'] ), '0' ) : '';
	$faq_id = filter_var( $id, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );

	if ( false === $faq_id ) {
		return esc_html__( 'FAQ ID is not specified or invalid', MK_FAQ_TEXT_DOMAIN );
	}

	$faq = get_post( $faq_id );
	if ( ! $faq || 'mk-simple-faq' !== $faq->post_type || 'publish' !== $faq->post_status || post_password_required( $faq ) ) {
		return esc_html__( 'FAQ not found', MK_FAQ_TEXT_DOMAIN );
	}

	if ( ! function_exists( 'carbon_get_post_meta' ) ) {
		return '';
	}

	$fields = carbon_get_post_meta( $faq_id, 'mk_simple_faq_complex' );
	if ( ! is_array( $fields ) ) {
		return '';
	}

	// Ignore incomplete or malformed rows instead of emitting PHP warnings.
	$items = array();
	foreach ( $fields as $field ) {
		if ( ! is_array( $field ) || ! isset( $field['mk_simple_faq_title'], $field['mk_simple_faq_answer'] ) || ! is_scalar( $field['mk_simple_faq_title'] ) || ! is_scalar( $field['mk_simple_faq_answer'] ) ) {
			continue;
		}
		if ( '' === trim( (string) $field['mk_simple_faq_title'] ) || '' === trim( (string) $field['mk_simple_faq_answer'] ) ) {
			continue;
		}
		$items[] = $field;
	}

	if ( ! $items ) {
		return '';
	}

	static $instance = 0;
	++$instance;
	$prefix     = 'mk-faq-' . $faq_id . '-' . $instance;
	$show_title = carbon_get_post_meta( $faq_id, 'mk_simple_faq_show_title' );
	$show_title = in_array( $show_title, array( 'yes', '1', 1, true ), true );
	$heading    = $show_title ? 'h4' : 'h3';
	$output     = '<div class="mk-faq-container" id="' . esc_attr( $prefix ) . '">';

	if ( $show_title ) {
		$output .= '<h3 class="mk-faq-heading-title">' . esc_html( $faq->post_title ) . '</h3>';
	}

	$output .= '<div class="mk-faq-accordion">';
	foreach ( $items as $index => $item ) {
		$question_id = $prefix . '-question-' . $index;
		$answer_id   = $prefix . '-answer-' . $index;

		// Answers remain readable if JavaScript is unavailable. The script
		// collapses panels only after their controls have been initialized.
		$output .= '<' . $heading . ' class="mk-faq-question-heading"><button type="button" id="' . esc_attr( $question_id ) . '" aria-expanded="true" aria-controls="' . esc_attr( $answer_id ) . '" class="mk-faq-accordion-title mk-faq-accordionTitle js-accordionTrigger is-expanded">';
		$output .= '<span class="mk-faq-question-text">' . esc_html( $item['mk_simple_faq_title'] ) . '</span><span class="mk-faq-accordion-icon" aria-hidden="true"></span></button></' . $heading . '>';
		$output .= '<div class="mk-faq-accordion-content mk-faq-accordionItem is-expanded" id="' . esc_attr( $answer_id ) . '" aria-hidden="false"><div class="mk-faq-answer-body">' . wpautop( wp_kses_post( $item['mk_simple_faq_answer'] ) ) . '</div></div>';
	}

	return $output . '</div></div>';
}
