<?php
/**
 * Real WordPress integration checks. Run only against a disposable test site.
 * WordPress and the plugin must already be loaded before including this file.
 */
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'carbon_set_post_meta' ) ) {
	throw new RuntimeException( 'Load WordPress with the FAQ plugin active before running these tests.' );
}

$mk_faq_test_results = array();
function mk_faq_integration_assert( $condition, $message ) {
	global $mk_faq_test_results;
	$mk_faq_test_results[] = array( 'pass' => (bool) $condition, 'check' => $message );
}

wp_set_current_user( 1 );
mk_faq_integration_assert( post_type_exists( 'mk-simple-faq' ), 'FAQ post type registered' );
mk_faq_integration_assert( shortcode_exists( 'mk-simple-faq' ), 'Public shortcode registered' );
mk_faq_integration_assert( did_action( 'carbon_fields_register_fields' ) > 0, 'Carbon Fields registration ran during WordPress boot' );

$faq_id = wp_insert_post( array(
	'post_type' => 'mk-simple-faq', 'post_status' => 'publish',
	'post_title' => 'Integration FAQ <b>title</b>',
) );
$items = array(
	array( 'mk_simple_faq_title' => 'Question <img src=x onerror=alert(1)>', 'mk_simple_faq_answer' => "First paragraph.\n\nSecond paragraph with <strong>safe markup</strong>.<script>alert(1)</script>" ),
	array( 'mk_simple_faq_title' => 'Long answer', 'mk_simple_faq_answer' => str_repeat( "A long answer must stay fully readable.\n\n", 90 ) ),
);
carbon_set_post_meta( $faq_id, 'mk_simple_faq_complex', $items );
carbon_set_post_meta( $faq_id, 'mk_simple_faq_show_title', true );
$stored = carbon_get_post_meta( $faq_id, 'mk_simple_faq_complex' );
mk_faq_integration_assert( is_array( $stored ) && count( $stored ) === 2, 'FAQ questions survive a real database round trip' );
mk_faq_integration_assert( carbon_get_post_meta( $faq_id, 'mk_simple_faq_show_title' ) === true, 'Carbon checkbox returns the enabled value' );

$old_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
ob_start();
$html = do_shortcode( '[mk-simple-faq id="' . $faq_id . '"]' );
$echoed = ob_get_clean();
mk_faq_integration_assert( $echoed === '', 'Shortcode returns output without echoing it' );
mk_faq_integration_assert( ( isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null ) === $old_post, 'Shortcode preserves the surrounding post' );
mk_faq_integration_assert( substr_count( $html, '<button' ) === 2, 'Every question has a native button' );
mk_faq_integration_assert( strpos( $html, '&lt;img' ) !== false && strpos( $html, '<img' ) === false, 'Question HTML is escaped' );
mk_faq_integration_assert( strpos( $html, '<script' ) === false && strpos( $html, '<strong>safe markup</strong>' ) !== false, 'Answers retain allowed formatting and strip executable HTML' );
mk_faq_integration_assert( strpos( $html, 'Second paragraph' ) !== false, 'Multiline answers render' );
mk_faq_integration_assert( strpos( $html, 'mk-faq-heading-title' ) !== false, 'Enabled group title is rendered' );
mk_faq_integration_assert( strpos( $html, 'title&lt;/b&gt;1' ) === false, 'Checkbox value does not leak into heading text' );

$twice = $html . do_shortcode( '[mk-simple-faq id="' . $faq_id . '"]' );
preg_match_all( '/\bid="([^"]+)"/', $twice, $ids );
mk_faq_integration_assert( count( $ids[1] ) === count( array_unique( $ids[1] ) ), 'Multiple shortcode instances have unique HTML IDs' );
preg_match_all( '/aria-controls="([^"]+)"/', $twice, $controls );
mk_faq_integration_assert( count( $controls[1] ) === 4 && ! array_diff( $controls[1], $ids[1] ), 'Every ARIA control targets its own existing panel' );

carbon_set_post_meta( $faq_id, 'mk_simple_faq_show_title', false );
mk_faq_integration_assert( strpos( do_shortcode( '[mk-simple-faq id="' . $faq_id . '"]' ), 'mk-faq-heading-title' ) === false, 'Disabled group title stays hidden' );
carbon_set_post_meta( $faq_id, 'mk_simple_faq_show_title', true );

wp_set_current_user( 0 );
foreach ( array( 'draft', 'private', 'future', 'trash' ) as $status ) {
	$restricted = wp_insert_post( array( 'post_type' => 'mk-simple-faq', 'post_status' => $status, 'post_title' => 'Restricted FAQ', 'post_date' => '2030-01-01 12:00:00' ) );
	carbon_set_post_meta( $restricted, 'mk_simple_faq_complex', $items );
	$restricted_html = do_shortcode( '[mk-simple-faq id="' . $restricted . '"]' );
	mk_faq_integration_assert( strpos( $restricted_html, 'Second paragraph' ) === false && strpos( $restricted_html, 'Question' ) === false, 'Anonymous visitors cannot read ' . $status . ' FAQ answers' );
	wp_delete_post( $restricted, true );
}
$protected = wp_insert_post( array( 'post_type' => 'mk-simple-faq', 'post_status' => 'publish', 'post_password' => 'integration-secret', 'post_title' => 'Protected FAQ' ) );
carbon_set_post_meta( $protected, 'mk_simple_faq_complex', $items );
mk_faq_integration_assert( strpos( do_shortcode( '[mk-simple-faq id="' . $protected . '"]' ), 'Second paragraph' ) === false, 'Password protection is respected for anonymous visitors' );
wp_delete_post( $protected, true );

ob_start();
do_shortcode( '[mk-simple-faq]' );
do_shortcode( '[mk-simple-faq id="not-a-number"]' );
do_shortcode( '[mk-simple-faq id="99999999"]' );
mk_faq_integration_assert( ob_get_clean() === '', 'Invalid shortcode IDs do not echo error messages' );

do_action( 'wp_enqueue_scripts' );
mk_faq_integration_assert( ! wp_script_is( 'mk-faq-theme-customizer', 'enqueued' ), 'Customizer preview JavaScript is absent on ordinary pages' );
mk_faq_integration_assert( ! wp_script_is( 'customize-preview', 'enqueued' ), 'Ordinary pages do not enqueue the WordPress preview runtime' );
$styles = wp_styles();
mk_faq_integration_assert( empty( $styles->registered['mk-faq-customizer-styles'] ), 'No direct PHP stylesheet endpoint is enqueued' );

wp_set_current_user( 1 );
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
$wp_customize = new WP_Customize_Manager();
do_action( 'customize_register', $wp_customize );
$setting = $wp_customize->get_setting( 'mk_faq_title_size' );
mk_faq_integration_assert( $setting !== null && is_callable( $setting->sanitize_callback ), 'Customizer font size has a valid sanitizer' );
$color_setting = $wp_customize->get_setting( 'mk_faq_title_color' );
mk_faq_integration_assert( $color_setting !== null && $color_setting->sanitize( '#123456' ) === '#123456', 'Customizer accepts valid hex colors' );
mk_faq_integration_assert( $color_setting->sanitize( '</style><script>alert(1)</script>' ) !== '</style><script>alert(1)</script>', 'Customizer rejects injected CSS/HTML colors' );

$page_id = wp_insert_post( array(
	'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'FAQ integration fixture',
	'post_content' => '[mk-simple-faq id="' . $faq_id . '"][mk-simple-faq id="' . $faq_id . '"]',
) );
wp_set_current_user( 0 );
$failed = array_filter( $mk_faq_test_results, function ( $check ) { return ! $check['pass']; } );
echo wp_json_encode( array(
	'wordpress' => get_bloginfo( 'version' ), 'php' => PHP_VERSION,
	'checks' => $mk_faq_test_results, 'passed' => count( $mk_faq_test_results ) - count( $failed ),
	'failed' => count( $failed ), 'fixture_url' => get_permalink( $page_id ),
	'fixture_faq_id' => $faq_id, 'fixture_page_id' => $page_id,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
if ( $failed ) {
	http_response_code( 500 );
	throw new RuntimeException( count( $failed ) . ' WordPress integration checks failed.' );
}
