<?php
/**
 * Isolated Customizer validation and CSS regression checks.
 * Run: php tests/customizer-regression.php
 * These checks use small WordPress stubs; the integration suite tests real WP.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

error_reporting( E_ALL );
set_error_handler( function ( $severity, $message, $file, $line ) {
	throw new ErrorException( $message, 0, $severity, $file, $line );
} );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
$mk_faq_test_mods = array();
$mk_faq_test_hooks = array();
$mk_faq_test_count = 0;

function __( $text, $domain = '' ) {
	return $text;
}
function add_action( $hook, $callback ) {
	global $mk_faq_test_hooks;
	$mk_faq_test_hooks[ $hook ][] = $callback;
}
function sanitize_hex_color( $color ) {
	if ( '' === $color ) {
		return '';
	}
	return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : null;
}
function get_theme_mod( $id, $default ) {
	global $mk_faq_test_mods;
	return array_key_exists( $id, $mk_faq_test_mods ) ? $mk_faq_test_mods[ $id ] : $default;
}
function mk_faq_test_same( $expected, $actual, $message ) {
	global $mk_faq_test_count;
	++$mk_faq_test_count;
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . ': expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
	}
}

class WP_Customize_Control {
	public $id;
	public $args;
	public function __construct( $manager, $id, $args ) {
		$this->id = $id;
		$this->args = $args;
	}
}
class WP_Customize_Color_Control extends WP_Customize_Control {}
class MK_FAQ_Test_Selective_Refresh {
	public $partials = array();
	public function add_partial( $id, $args ) {
		$this->partials[ $id ] = $args;
	}
}
class MK_FAQ_Test_Customizer {
	public $sections = array();
	public $settings = array();
	public $controls = array();
	public $selective_refresh;
	public function __construct() {
		$this->selective_refresh = new MK_FAQ_Test_Selective_Refresh();
	}
	public function add_section( $id, $args ) {
		$this->sections[ $id ] = $args;
	}
	public function add_setting( $id, $args ) {
		$this->settings[ $id ] = $args;
	}
	public function add_control( $control ) {
		$this->controls[ $control->id ] = $control;
	}
}

try {
	require dirname( __DIR__ ) . '/includes/mk-simple-faq-customization.php';
	ob_start();
	require dirname( __DIR__ ) . '/includes/mk-simple-faq-customization-css.php';
	mk_faq_test_same( '', ob_get_clean(), 'Including the CSS generator must not emit an HTTP response' );
	mk_faq_test_same( array( 'mk_simple_faq_customization' ), $mk_faq_test_hooks['customize_register'], 'Customizer callback is registered once' );

	$cases = array(
		array( 'mk_faq_title_size', 'bogus', 18 ),
		array( 'mk_faq_title_size', array( 'bad' ), 18 ),
		array( 'mk_faq_title_size', null, 18 ),
		array( 'mk_faq_title_size', -10, 12 ),
		array( 'mk_faq_title_size', '99999999999999999999999999999', 48 ),
		array( 'mk_faq_text_size', '24', 24 ),
		array( 'mk_faq_text_size', '24.5', 16 ),
		array( 'mk_faq_block_size', '480;}</style><script>alert(1)</script>', 480 ),
		array( 'mk_faq_block_size', '1', 320 ),
		array( 'mk_faq_block_size', '1201', 1200 ),
		array( 'mk_faq_title_color', array( 'bad' ), '#0f0f0f' ),
		array( 'mk_faq_title_color', null, '#0f0f0f' ),
		array( 'mk_faq_title_color', '', '#0f0f0f' ),
		array( 'mk_faq_title_color', 'red;}</style><script>alert(1)</script>', '#0f0f0f' ),
		array( 'mk_faq_title_color', '#abc', '#abc' ),
		array( 'mk_faq_title_color', '#ABCDEF', '#ABCDEF' ),
		array( 'mk_faq_title_color', '#abcd', '#0f0f0f' ),
		array( 'unknown', '1', null ),
	);
	foreach ( $cases as $case ) {
		mk_faq_test_same( $case[2], mk_simple_faq_sanitize_design_value( $case[1], $case[0] ), 'Validate ' . $case[0] );
	}
	mk_faq_test_same( 48, mk_simple_faq_sanitize_customizer_value( '999', (object) array( 'id' => 'mk_faq_title_size' ) ), 'Customizer callback uses the supplied setting ID' );

	$schema = mk_simple_faq_customizer_settings();
	$manager = new MK_FAQ_Test_Customizer();
	mk_simple_faq_customization( $manager );
	mk_faq_test_same( 9, count( $manager->settings ), 'All nine existing design settings are registered' );
	foreach ( $schema as $id => $setting ) {
		mk_faq_test_same( $setting['default'], $manager->settings[ $id ]['default'], 'Control and stylesheet defaults match for ' . $id );
		mk_faq_test_same( 'mk_simple_faq_sanitize_customizer_value', $manager->settings[ $id ]['sanitize_callback'], 'Setting has the shared sanitizer: ' . $id );
		mk_faq_test_same( 'postMessage', $manager->settings[ $id ]['transport'], 'Setting uses live preview: ' . $id );
		mk_faq_test_same( 'theme_mod', $manager->settings[ $id ]['type'], 'Existing theme modifications stay compatible: ' . $id );
	}
	mk_faq_test_same( false, isset( $manager->selective_refresh->partials['mk_faq_customize_trigger_partial'] ), 'Preview supplies a shortcut without replacing FAQ markup' );

	$mk_faq_test_mods = array(
		'mk_faq_title_color' => '#abc',
		'mk_faq_text_size' => '24',
		'mk_faq_block_size' => '999;}</style>',
	);
	$css = mk_simple_faq_customization_css();
	mk_faq_test_same( true, 0 === strpos( $css, '.mk-faq-container{' ), 'Generated styles stay scoped to FAQ containers' );
	mk_faq_test_same( true, false !== strpos( $css, '--mk-faq-title-color:#abc;' ), 'Valid saved color is emitted' );
	mk_faq_test_same( true, false !== strpos( $css, '--mk-faq-text-font-size:24px;' ), 'Valid saved font size is emitted' );
	mk_faq_test_same( true, false !== strpos( $css, '--mk-faq-accordion-max-width:480px;' ), 'Unsafe stored width falls back to a number' );
	mk_faq_test_same( false, false !== strpos( $css, '<' ), 'Styles cannot contain HTML markup' );
	mk_faq_test_same( 9, substr_count( $css, '--mk-faq-' ), 'Every design variable is emitted once' );
	fwrite( STDOUT, 'PASS: ' . $mk_faq_test_count . ' Customizer validation/CSS checks.' . PHP_EOL );
} catch ( Throwable $error ) {
	fwrite( STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL );
	exit( 1 );
}
