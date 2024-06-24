<?php
function mk_faq_register_post_type() {
	$labels = array(
		'name'               => esc_html__( 'FAQ', MK_FAQ_TEXT_DOMAIN ),
		'singular_name'      => esc_html__( 'FAQ', MK_FAQ_TEXT_DOMAIN ),
		'menu_name'          => esc_html__( 'FAQ', MK_FAQ_TEXT_DOMAIN ),
		'name_admin_bar'     => esc_html__( 'FAQ', MK_FAQ_TEXT_DOMAIN ),
		'add_new'            => esc_html__( 'Add New', MK_FAQ_TEXT_DOMAIN ),
		'add_new_item'       => esc_html__( 'Add New FAQ', MK_FAQ_TEXT_DOMAIN ),
		'new_item'           => esc_html__( 'New FAQ', MK_FAQ_TEXT_DOMAIN ),
		'edit_item'          => esc_html__( 'Edit FAQ', MK_FAQ_TEXT_DOMAIN ),
		'view_item'          => esc_html__( 'View FAQ', MK_FAQ_TEXT_DOMAIN ),
		'all_items'          => esc_html__( 'All FAQ', MK_FAQ_TEXT_DOMAIN ),
		'search_items'       => esc_html__( 'Search FAQ', MK_FAQ_TEXT_DOMAIN ),
		'parent_item_colon'  => esc_html__( 'Parent FAQ:', MK_FAQ_TEXT_DOMAIN ),
		'not_found'          => esc_html__( 'No FAQ found.', MK_FAQ_TEXT_DOMAIN ),
		'not_found_in_trash' => esc_html__( 'No FAQ found in trash.', MK_FAQ_TEXT_DOMAIN )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'mk-simple-faq' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'mk-simple-faq', $args );
}

add_action( 'init', 'mk_faq_register_post_type' );