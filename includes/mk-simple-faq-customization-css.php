<?php
/**
 * Generate safe inline styles after WordPress has loaded.
 *
 * This file is deliberately not a standalone CSS endpoint: WordPress can live
 * in a subdirectory and must not be bootstrapped from DOCUMENT_ROOT here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the plugin's scoped CSS custom properties for wp_add_inline_style().
 *
 * @return string
 */
function mk_simple_faq_customization_css() {
	$declarations = array();
	foreach ( mk_simple_faq_customizer_settings() as $id => $setting ) {
		$value = mk_simple_faq_sanitize_design_value( get_theme_mod( $id, $setting['default'] ), $id );
		if ( 'range' === $setting['type'] ) {
			$value .= 'px';
		}
		$declarations[] = $setting['property'] . ':' . $value . ';';
	}

	return '.mk-faq-container{' . implode( '', $declarations ) . '}';
}
