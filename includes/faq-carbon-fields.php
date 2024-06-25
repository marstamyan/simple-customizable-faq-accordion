<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'crb_attach_mk_simple_faq_fields' );
add_action( 'after_setup_theme', 'crb_load' );
function crb_attach_mk_simple_faq_fields() {
	Container::make( 'post_meta', 'mk_simple_faq_fields', __( 'FAQ Fields' ) )->where( 'post_type', '=', 'mk-simple-faq' )->add_fields( [
			Field::make( 'complex', 'mk_simple_faq_complex', '' )->set_required()->add_fields( [
					Field::make( 'text', 'mk_simple_faq_title', __( 'FAQ Title' ) )->set_required(),
					Field::make( 'textarea', 'mk_simple_faq_answer', __( 'FAQ Answer' ) )->set_required(),
				] ),
			Field::make( 'checkbox', 'mk_simple_faq_show_title', __( 'Show main title' ) )->set_option_value( 'yes' )->set_default_value( 'yes' )->set_help_text( 'Check this box to display FAQ main title' )
		] );
}

function crb_load() {
	require_once( MK_PLUGIN_PATH . '/vendor/autoload.php' );
	\Carbon_Fields\Carbon_Fields::boot();
}
