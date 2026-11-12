<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mk_faq_options_page() {
	add_submenu_page( 'edit.php?post_type=mk-simple-faq', __( 'FAQ Help', 'simple-customizable-faq-accordion' ), __( 'Help', 'simple-customizable-faq-accordion' ), 'manage_options', 'mk-faq-options', 'mk_faq_options_page_html' );
}
add_action( 'admin_menu', 'mk_faq_options_page' );

function mk_faq_options_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Help', 'simple-customizable-faq-accordion' ); ?></h1>
		<ol>
			<li><?php esc_html_e( 'Open FAQ → Add New, enter a group title and add questions and answers.', 'simple-customizable-faq-accordion' ); ?></li>
			<li><?php esc_html_e( 'Publish the FAQ group and copy its shortcode from the FAQ list or editor.', 'simple-customizable-faq-accordion' ); ?></li>
			<li><?php esc_html_e( 'Paste the shortcode into a Shortcode block on any page or post.', 'simple-customizable-faq-accordion' ); ?> <code>[mk-simple-faq id="123"]</code></li>
			<li><?php esc_html_e( 'Use FAQ Customization in the Customizer to change colors and sizes. Settings are saved for the active theme.', 'simple-customizable-faq-accordion' ); ?></li>
		</ol>
		<p><a href="<?php echo esc_url( add_query_arg( 'autofocus[section]', 'mk_faq_section', admin_url( 'customize.php' ) ) ); ?>"><?php esc_html_e( 'Open FAQ customization', 'simple-customizable-faq-accordion' ); ?></a></p>
		<p><a href="https://www.youtube.com/watch?v=Tiqec_swMGY" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Watch the video tutorial on YouTube', 'simple-customizable-faq-accordion' ); ?></a></p>
		<p><a href="https://github.com/marstamyan/simple-customizable-faq-accordion" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Source code and support on GitHub', 'simple-customizable-faq-accordion' ); ?></a></p>
	</div>
	<?php
}
