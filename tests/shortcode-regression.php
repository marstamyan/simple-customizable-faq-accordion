<?php
/**
 * Regression checks against a disposable, bootstrapped WordPress database.
 * Run with: wp eval-file tests/shortcode-regression.php
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'carbon_set_post_meta' ) || ! function_exists( 'mk_simple_faq_shortcode' ) ) {
	throw new RuntimeException( 'Load WordPress with the FAQ plugin active before running this test.' );
}

$mk_faq_test_ids        = array();
$mk_faq_test_assertions = 0;
$mk_faq_previous_user   = get_current_user_id();
$mk_faq_had_global_post = array_key_exists( 'post', $GLOBALS );
$mk_faq_previous_post   = $mk_faq_had_global_post ? $GLOBALS['post'] : null;
$mk_faq_assert          = function ( $condition, $message ) use ( &$mk_faq_test_assertions ) {
	++$mk_faq_test_assertions;
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
};
$mk_faq_create          = function ( $overrides = array() ) use ( &$mk_faq_test_ids ) {
	$id = wp_insert_post( array_merge( array(
		'post_type'   => 'mk-simple-faq',
		'post_status' => 'publish',
		'post_title'  => 'FAQ & regression fixture',
	), $overrides ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	$mk_faq_test_ids[] = $id;
	if ( 'mk-simple-faq' !== get_post_type( $id ) ) {
		return $id;
	}
	carbon_set_post_meta( $id, 'mk_simple_faq_show_title', 'yes' );
	carbon_set_post_meta( $id, 'mk_simple_faq_complex', array(
		array( 'mk_simple_faq_title' => 'Question <em>one</em>', 'mk_simple_faq_answer' => "First paragraph.\n\nSecond paragraph. <a href=\"javascript:alert(1)\" onclick=\"alert(1)\">Link</a><script>alert(1)</script>" ),
		array( 'mk_simple_faq_title' => 'Question two', 'mk_simple_faq_answer' => str_repeat( 'Long answer content. ', 400 ) ),
	) );
	return $id;
};

try {
	wp_set_current_user( 0 );
	$mk_faq_id = $mk_faq_create();
	$mk_faq_first = mk_simple_faq_shortcode( array( 'id' => $mk_faq_id ) );
	$mk_faq_second = do_shortcode( '[mk-simple-faq id="' . $mk_faq_id . '"]' );

	$mk_faq_assert( false !== strpos( $mk_faq_first, 'FAQ &amp; regression fixture</h3>' ), 'The enabled main title must render without a checkbox value suffix.' );
	$mk_faq_assert( false !== strpos( $mk_faq_first, 'Question &lt;em&gt;one&lt;/em&gt;' ), 'Question text must be escaped.' );
	$mk_faq_assert( false === strpos( $mk_faq_first, '<script' ) && false === strpos( $mk_faq_first, 'onclick=' ) && false === strpos( $mk_faq_first, 'javascript:' ), 'Answer HTML must strip executable markup.' );
	$mk_faq_assert( false !== strpos( $mk_faq_first, '<p>First paragraph.</p>' ) && false !== strpos( $mk_faq_first, '<p>Second paragraph.' ), 'Blank lines must become separate paragraphs.' );
	$mk_faq_assert( false !== strpos( $mk_faq_first, str_repeat( 'Long answer content. ', 400 ) ), 'Long answers must remain complete.' );
	$mk_faq_assert( 2 === substr_count( $mk_faq_first, '<button type="button"' ), 'Each FAQ row needs a native keyboard-accessible button.' );
	$mk_faq_assert( false === strpos( $mk_faq_first, ' hidden' ), 'Answers must remain readable when JavaScript is unavailable.' );

	preg_match_all( '/\bid="([^"]+)"/', $mk_faq_first . $mk_faq_second, $mk_faq_ids );
	preg_match_all( '/aria-controls="([^"]+)"/', $mk_faq_first . $mk_faq_second, $mk_faq_controls );
	$mk_faq_assert( 10 === count( $mk_faq_ids[1] ) && count( $mk_faq_ids[1] ) === count( array_unique( $mk_faq_ids[1] ) ), 'Every container, question and panel ID must be unique, including repeated shortcodes.' );
	$mk_faq_assert( 4 === count( $mk_faq_controls[1] ) && ! array_diff( $mk_faq_controls[1], $mk_faq_ids[1] ), 'Every control must reference its own existing answer panel.' );
	$mk_faq_assert( ( $mk_faq_had_global_post ? $mk_faq_previous_post : null ) === ( isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null ), 'Shortcode rendering must not replace the global post.' );
	$mk_faq_assert( false !== strpos( mk_simple_faq_shortcode( array( 'id' => '000' . $mk_faq_id ) ), 'mk-faq-container' ), 'Leading zeroes on an otherwise valid numeric ID remain compatible.' );

	foreach ( array( '', 0, -1, '12invalid', array( $mk_faq_id ), null ) as $mk_faq_invalid_id ) {
		$mk_faq_assert( false === strpos( mk_simple_faq_shortcode( array( 'id' => $mk_faq_invalid_id ) ), 'mk-faq-container' ), 'Invalid IDs must not render an FAQ.' );
	}
	foreach ( array( 'draft', 'private', 'pending', 'future', 'trash' ) as $mk_faq_status ) {
		$mk_faq_hidden_id = $mk_faq_create( array( 'post_status' => $mk_faq_status, 'post_date' => 'future' === $mk_faq_status ? gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ) : current_time( 'mysql' ) ) );
		$mk_faq_assert( false === strpos( mk_simple_faq_shortcode( array( 'id' => $mk_faq_hidden_id ) ), 'mk-faq-container' ), 'Unpublished FAQs must not be exposed: ' . $mk_faq_status );
	}
	$mk_faq_protected = $mk_faq_create( array( 'post_password' => wp_generate_password() ) );
	$mk_faq_assert( false === strpos( mk_simple_faq_shortcode( array( 'id' => $mk_faq_protected ) ), 'mk-faq-container' ), 'A password-protected FAQ must not expose its content.' );
	$mk_faq_other = $mk_faq_create( array( 'post_type' => 'post' ) );
	$mk_faq_assert( false === strpos( mk_simple_faq_shortcode( array( 'id' => $mk_faq_other ) ), 'mk-faq-container' ), 'The shortcode must reject other post types.' );
	$mk_faq_empty = $mk_faq_create();
	carbon_set_post_meta( $mk_faq_empty, 'mk_simple_faq_complex', array() );
	$mk_faq_assert( '' === mk_simple_faq_shortcode( array( 'id' => $mk_faq_empty ) ), 'An empty FAQ must not render an empty accordion.' );
	carbon_set_post_meta( $mk_faq_id, 'mk_simple_faq_show_title', '' );
	$mk_faq_assert( false === strpos( mk_simple_faq_shortcode( array( 'id' => $mk_faq_id ) ), 'mk-faq-heading-title' ), 'Disabling the main title must hide it.' );

	echo 'Shortcode regression: ' . $mk_faq_test_assertions . " assertions passed.\n";
} finally {
	foreach ( $mk_faq_test_ids as $mk_faq_test_id ) {
		wp_delete_post( $mk_faq_test_id, true );
	}
	wp_set_current_user( $mk_faq_previous_user );
	if ( $mk_faq_had_global_post ) {
		$GLOBALS['post'] = $mk_faq_previous_post;
	} else {
		unset( $GLOBALS['post'] );
	}
}
