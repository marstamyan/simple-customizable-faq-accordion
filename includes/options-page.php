<?php

function mk_faq_options_page() {
	add_menu_page(
		__( 'FAQ Options', MK_FAQ_TEXT_DOMAIN ),
		__( 'FAQ Options', MK_FAQ_TEXT_DOMAIN ),
		'manage_options',
		'mk-faq-options',
		'mk_faq_options_page_html'
	);
}
add_action( 'admin_menu', 'mk_faq_options_page' );

function mk_faq_options_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<div class="instruction">
			<h3>Instruction for FAQ</h3>
			<div class="video-container">
				<iframe width="700" height="500" src="https://www.youtube.com/embed/u5Rz06tMw5g" title="The Fear (11+ Hours Lovecraftian Dark Ambient)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
			</div>
		</div>
	</div>
	<?php
}