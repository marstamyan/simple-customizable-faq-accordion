<?php

add_shortcode( 'mk-simple-faq', 'mk_simple_faq_shortcode' );

function mk_simple_faq_shortcode( $atts, $content = '' ) {
	if ( is_admin() ) {
		return '';
	}
	$atts = shortcode_atts( array( 'id' => null ), $atts );

	$faq_id = intval( $atts['id'] );

	if ( $faq_id <= 0 ) {
		return esc_html__( 'FAQ ID is not specified or invalid', MK_FAQ_TEXT_DOMAIN );
	}

	$post = get_post( $faq_id );
	if ( ! $post || $post->post_type !== 'mk-simple-faq' ) {
		return esc_html__( 'FAQ not found', MK_FAQ_TEXT_DOMAIN );
	}

	if ( ! isset( $atts['id'] ) ) {
		return esc_html_e( 'FAQ ID is not specified', MK_FAQ_TEXT_DOMAIN );
	}

	$query = new WP_Query( array(
			'post_type' => 'mk-simple-faq',
			'p'         => intval( $atts['id'] )
		) );

	if ( $query->have_posts() ) {
		$current_faq = get_post( $atts['id'] );
		$faq_title = esc_attr( $current_faq->post_title );
		$output = '<div class="mk-faq-container" id="mk-faq-container">';

		$show_mk_faq_title = carbon_get_post_meta( $atts['id'], 'mk_simple_faq_show_title' );
		if ( $show_mk_faq_title == '1' ) {
			$output .= '<h3 class="mk-faq-heading-title">' . $faq_title . $show_mk_faq_title . '</h3>';
		}

		$output .= '<div class="mk-faq-accordion"><dl>';

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			$faq_complex_fields = carbon_get_post_meta( $post_id, 'mk_simple_faq_complex' );

			if ( $faq_complex_fields ) {
				foreach ( $faq_complex_fields as $faq_complex_field ) {
					$faq_title = esc_attr( $faq_complex_field['mk_simple_faq_title'] );
					$faq_answer = wp_kses_post( $faq_complex_field['mk_simple_faq_answer'] );
					$output .= '<dt><a href="#accordion' . $post_id . '" aria-expanded="false" aria-controls="accordion' . $post_id . '" class="mk-faq-accordion-title mk-faq-accordionTitle js-accordionTrigger">' . $faq_title . '</a></dt>';

					$output .= '<dd class="mk-faq-accordion-content mk-faq-accordionItem is-collapsed" id="accordion' . $post_id . '" aria-hidden="true"><p>' . $faq_answer . '</p></dd>';
				}
			}
		}

		wp_reset_postdata();
		$output .= '</dl></div></div>';

		return $output;
	} else {
		return esc_html_e( 'FAQ not found', MK_FAQ_TEXT_DOMAIN );
	}
}
