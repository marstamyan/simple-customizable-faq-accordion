<?php

function mk_faq_options_page() {
	add_menu_page( __( 'Simple FAQ', MK_FAQ_TEXT_DOMAIN ), __( 'Simple FAQ', MK_FAQ_TEXT_DOMAIN ), 'manage_options', 'mk-faq-options', 'mk_faq_options_page_html' );
}

add_action( 'admin_menu', 'mk_faq_options_page' );

function mk_faq_options_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
    <div class="wrap">
        <div class="mk-faq-instruction">
            <h3>Instruction for FAQ</h3>
            <div class="mk-faq-video-container">
                <iframe width="800" height="500" src="https://www.youtube.com/embed/Tiqec_swMGY" title="Simple Customizable FAQ Accordion for WordPress plugin" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <h3><a target="_blank" href="https://github.com/marstamyan/simple-customizable-faq-accordion" class="mk-faq-link">Github link</a></h3>
        </div>
    </div>
	<?php
}