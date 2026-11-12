<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'carbon_fields_register_fields', 'mk_faq_register_fields' );
add_action( 'after_setup_theme', 'mk_faq_load_carbon_fields' );

function mk_faq_register_fields() {
	Container::make( 'post_meta', 'mk_simple_faq_fields', __( 'FAQ Fields', 'simple-customizable-faq-accordion' ) )
		->where( 'post_type', '=', 'mk-simple-faq' )
		->add_fields( array(
			Field::make( 'complex', 'mk_simple_faq_complex', '' )->set_required()->add_fields( array(
				Field::make( 'text', 'mk_simple_faq_title', __( 'FAQ Title', 'simple-customizable-faq-accordion' ) )->set_required(),
				Field::make( 'textarea', 'mk_simple_faq_answer', __( 'FAQ Answer', 'simple-customizable-faq-accordion' ) )->set_required(),
			) ),
			Field::make( 'checkbox', 'mk_simple_faq_show_title', __( 'Show main title', 'simple-customizable-faq-accordion' ) )
				->set_option_value( 'yes' )->set_default_value( 'yes' )
				->set_help_text( __( 'Check this box to display the FAQ group title.', 'simple-customizable-faq-accordion' ) ),
		) );
}

function mk_faq_load_carbon_fields() {
	// Reuse an already loaded Carbon Fields installation from another plugin or theme.
	if ( ! class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
		$autoload = MK_FAQ_PLUGIN_PATH . 'vendor/autoload.php';
		if ( ! is_readable( $autoload ) ) {
			add_action( 'admin_notices', 'mk_faq_missing_dependency_notice' );
			return;
		}
		require_once $autoload;
	}
	if ( class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
		\Carbon_Fields\Carbon_Fields::boot();
	} else {
		add_action( 'admin_notices', 'mk_faq_missing_dependency_notice' );
	}
}

function mk_faq_missing_dependency_notice() {
	if ( current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Simple Customizable FAQ Accordion is missing its Carbon Fields library. Reinstall the complete plugin package, or run composer install in the plugin directory.', 'simple-customizable-faq-accordion' ) . '</p></div>';
	}
}
