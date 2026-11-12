<?php
/**
 * Plugin Name:       Simple Customizable FAQ Accordion
 * Plugin URI:        https://github.com/marstamyan/simple-customizable-faq-accordion
 * Description:       Create FAQ groups with accessible accordions and customizable colors and sizes.
 * Version:           1.2.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Mamikon
 * Author URI:        https://linkedin.com/in/mamikon-arustamyan-3969301ab/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-customizable-faq-accordion
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SimpleCustomizableFAQAccordion' ) ) {
	class SimpleCustomizableFAQAccordion {
		public function __construct() {
			define( 'MK_FAQ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'MK_FAQ_VERSION', '1.2.2' );
			define( 'MK_FAQ_TEXT_DOMAIN', 'simple-customizable-faq-accordion' );
		}

		public function initialize() {
			require_once MK_FAQ_PLUGIN_PATH . 'includes/options-page.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/register-faq-post-type.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/faq-carbon-fields.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/mk-simple-faq-shortcode.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/mk-simple-faq-customization.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/mk-simple-faq-customization-css.php';
			require_once MK_FAQ_PLUGIN_PATH . 'includes/design-settings.php';

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_styles' ) );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'admin_enqueue_scripts_styles' ) );
			add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'ensure_customizer_dependencies' ), PHP_INT_MAX );
			add_action( 'admin_notices', array( $this, 'display_mk_simple_faq_message' ) );
			add_action( 'manage_mk-simple-faq_posts_custom_column', array( $this, 'display_shortcode_column_content' ), 10, 2 );
			add_filter( 'manage_edit-mk-simple-faq_columns', array( $this, 'add_shortcode_column' ) );
			add_filter( 'litespeed_can_optm', array( $this, 'allow_page_optimization' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		}

		public function plugin_action_links( $links ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=mk-simple-faq&page=mk-faq-settings' ) ) . '">' . esc_html__( 'Settings', 'simple-customizable-faq-accordion' ) . '</a>' );
			}
			return $links;
		}

		public function display_mk_simple_faq_message() {
			$screen = get_current_screen();
			$post   = get_post();
			if ( ! $screen || 'post' !== $screen->base || 'mk-simple-faq' !== $screen->post_type || ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				return;
			}
			printf(
				'<div class="notice notice-info"><p>%s <code>%s</code></p></div>',
				esc_html__( 'Use this shortcode to display this FAQ:', 'simple-customizable-faq-accordion' ),
				esc_html( '[mk-simple-faq id="' . absint( $post->ID ) . '"]' )
			);
		}

		public function add_shortcode_column( $columns ) {
			$result = array();
			foreach ( $columns as $key => $label ) {
				$result[ $key ] = $label;
				if ( 'title' === $key ) {
					$result['shortcode'] = esc_html__( 'Shortcode', 'simple-customizable-faq-accordion' );
				}
			}
			if ( ! isset( $result['shortcode'] ) ) {
				$result['shortcode'] = esc_html__( 'Shortcode', 'simple-customizable-faq-accordion' );
			}
			return $result;
		}

		public function display_shortcode_column_content( $column_name, $post_id ) {
			if ( 'shortcode' === $column_name ) {
				echo '<code>' . esc_html( '[mk-simple-faq id="' . absint( $post_id ) . '"]' ) . '</code>';
			}
		}

		public function admin_enqueue_scripts_styles() {
			$screen = get_current_screen();
			if ( $screen && 'customize' === $screen->base ) {
				wp_enqueue_script( 'mk-faq-customizer-controls', plugins_url( 'admin/js/customizer-controls.js', __FILE__ ), array( 'customize-controls' ), MK_FAQ_VERSION, true );
			}
			if ( ! $screen || ( 'mk-simple-faq' !== $screen->post_type && 'customize' !== $screen->base ) ) {
				return;
			}
			wp_enqueue_style( 'mk-faq-admin-styles', plugins_url( 'admin/css/simple-customizable-faq-accordion-admin.css', __FILE__ ), array(), MK_FAQ_VERSION );
		}

		public function enqueue_scripts_styles() {
			// Load here so shortcodes in widgets and template parts also have styles in the head.
			wp_enqueue_style( 'mk-faq-public-styles', plugins_url( 'public/css/simple-customizable-faq-accordion-public.css', __FILE__ ), array(), MK_FAQ_VERSION );
			wp_add_inline_style( 'mk-faq-public-styles', mk_simple_faq_customization_css() );
			wp_enqueue_script( 'mk-faq-scripts', plugins_url( 'public/js/simple-customizable-faq-accordion-public.js', __FILE__ ), array(), MK_FAQ_VERSION, true );
		}

		public function enqueue_customizer_preview() {
			// Load early; late or reordered footer assets must not delay the listeners.
			wp_enqueue_script( 'mk-faq-theme-customizer', plugins_url( 'public/js/mk-faq-theme-customizer.js', __FILE__ ), array( 'customize-preview' ), MK_FAQ_VERSION, false );
		}

		public function ensure_customizer_dependencies() {
			// Some custom themes remove the jquery alias, breaking WordPress's
			// customize-preview dependency chain and every dependent preview script.
			if ( is_customize_preview() && ! wp_script_is( 'jquery', 'registered' ) && wp_script_is( 'jquery-core', 'registered' ) && wp_script_is( 'jquery-migrate', 'registered' ) ) {
				wp_register_script( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), null );
			}
		}

		public function allow_page_optimization( $allowed ) {
			return is_customize_preview() ? false : $allowed;
		}
	}

	$MkSimpleFAQ = new SimpleCustomizableFAQAccordion();
	$MkSimpleFAQ->initialize();
}
