<?php
header( "Content-type: text/css" );

require_once( rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) . '/wp-load.php' );

$title_background_color = get_theme_mod( 'mk_faq_title_background_color', '#38cc70' );
$faq_title_background_color_hover = get_theme_mod( 'mk_faq_title_background_color_hover', '#2ba659' );
$faq_text_background_color = get_theme_mod( 'mk_faq_text_background_color', '#fff' );
$mk_faq_title_color = get_theme_mod( 'mk_faq_title_color', '#fff' );
$mk_faq_text_color = get_theme_mod( 'mk_faq_text_color', '#0f0f0f' );
$mk_faq_border_line = get_theme_mod( 'mk_faq_border_line', '#2caf5d' );
$mk_faq_title_size = get_theme_mod( 'mk_faq_title_size', '18' );
$mk_faq_text_size = get_theme_mod( 'mk_faq_text_size', '16' );

?>

.mk-faq-accordionTitleActive, .mk-faq-accordionTitle.is-expanded,
.mk-faq-accordion-title {
    background-color: <?php echo $title_background_color; ?>;
}

.mk-faq-accordion-title:hover {
    background-color: <?php echo $faq_title_background_color_hover; ?>;
}

.mk-faq-accordion,
.mk-faq-accordion dd, .mk-faq-accordion__panel,
.mk-faq-accordion dd, .mk-faq-accordion__panel p{
    background-color: <?php echo $faq_text_background_color; ?>;
}

.mk-faq-accordion-title, .mk-faq-accordion__Heading {
    border-bottom: 1px solid <?php echo $mk_faq_border_line; ?>;
    color: <?php echo $mk_faq_title_color; ?>;
}

dt a.mk-faq-accordion-title:hover,
dt a.mk-faq-accordion-title:focus,
dt a.mk-faq-accordion-title:active,
dt a.mk-faq-accordion-title:visited {
    color: <?php echo $mk_faq_title_color; ?>;
    text-decoration: none;
}

.mk-faq-accordion-content p {
    color: <?php echo $mk_faq_text_color; ?>;
}

a.mk-faq-accordion-title {
    font-size: <?php echo $mk_faq_title_size; ?>px;
}

.mk-faq-accordion dd, .mk-faq-accordion__panel {
    font-size: <?php echo $mk_faq_text_size; ?>px;
}