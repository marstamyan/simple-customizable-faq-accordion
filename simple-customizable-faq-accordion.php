<?php

/**
 * Plugin Name:       Simple Customizable FAQ Accordion
 * Plugin URI:        https://mamikonars.github.io/personal/
 * Description:       Simple Customizable FAQ Accordion - an easy and convenient solution for creating a Frequently Asked Questions section on your WordPress website. Customize styles and accordion lists for optimal display of your answers. Enhance user experience and make navigation through your FAQ more intuitive with this plugin.
 * Version:           1.0.0
 * Author:            Mamikon
 * Author URI:        https://linkedin.com/in/mamikon-arustamyan-3969301ab?/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-customizable-faq-accordion
 * Domain Path:       /languages
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SimpleCustomizableFAQAccordion' ) ) {
	class SimpleCustomizableFAQAccordion {
		public function __construct() {
			define( 'MK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'MK_FAQ_TEXT_DOMAIN', 'simple-customizable-faq-accordion' );
		}

		public function initialize() {
			include_once( MK_PLUGIN_PATH . 'includes/utilities.php' );
			include_once( MK_PLUGIN_PATH . 'includes/options-page.php' );
			require_once( MK_PLUGIN_PATH . 'includes/register-faq-post-type.php' );
			require_once( MK_PLUGIN_PATH . 'includes/faq-carbon-fields.php' );
			require_once( MK_PLUGIN_PATH . 'includes/mk-simple-faq-shortcode.php' );
			require_once( MK_PLUGIN_PATH . 'includes/mk-simple-faq-customization.php' );

			add_action( 'carbon_fields_register_fields', 'crb_attach_theme_options' );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_styles' ) );
			add_action( 'admin_notices', array( $this, 'display_mk_simple_faq_message' ) );
			add_action( 'customize_register', array( $this, 'mk_simple_faq_customization' ) );

			add_action( 'manage_mk-simple-faq_posts_custom_column', array(
				$this,
				'display_shortcode_column_content'
			), 10, 2 );

			add_filter( 'manage_edit-mk-simple-faq_columns', array( $this, 'custom_columns_order' ) );
			add_filter( 'manage_edit-mk-simple-faq_columns', array( $this, 'add_shortcode_column' ) );
		}

		function display_mk_simple_faq_message() {
			global $pagenow;

			if ( $pagenow == 'post.php' && isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'mk-simple-faq' ) {
				echo '<div class="notice notice-success is-dismissible"><p>Use this shortcode to display FAQ: <span>[mk-simple-faq id="' . $_GET['post'] . '"]</span></p></div>';
			}
		}

		function add_shortcode_column( $columns ) {
			$columns['shortcode'] = esc_html__( 'Shortcode', MK_FAQ_TEXT_DOMAIN );

			return $columns;
		}

		function display_shortcode_column_content( $column_name, $post_id ) {
			if ( $column_name === 'shortcode' ) {
				echo '[mk-simple-faq id="' . $post_id . '"]';
			}
		}

		function custom_columns_order( $columns ) {
			$new_order = array(
				'cb'        => $columns['cb'],
				'title'     => $columns['title'],
				'shortcode' => 'Shortcode',
				'date'      => $columns['date']
			);

			return $new_order;
		}

		public function admin_enqueue_scripts_styles() {
			wp_enqueue_style( 'mk-faq-admin-styles', plugins_url( '/admin/css/simple-customizable-faq-accordion-admin.css', __FILE__ ), array(), '1.0.0' );
		}
		public function enqueue_scripts_styles() {
			wp_enqueue_style( 'mk-faq-public-styles', plugins_url( '/public/css/simple-customizable-faq-accordion-public.css', __FILE__ ), array(), '1.0.0' );
			wp_enqueue_style( 'mk-faq-customizer-styles', plugins_url( '/includes/mk-simple-faq-customization-css.php', __FILE__ ), array(), '0.1.0', 'all' );
			wp_enqueue_script('mk-faq-theme-customizer', plugins_url( '/public/js/mk-faq-theme-customizer.js', __FILE__ ), array( 'jquery', 'customize-preview' ),'1.0.0',true );
			wp_enqueue_script( 'mk-faq-scripts', plugins_url( '/public/js/simple-customizable-faq-accordion-public.js', __FILE__ ), array(), '1.0.0', true );
		}
	}

	$MkSimpleFAQ = new SimpleCustomizableFAQAccordion;
	$MkSimpleFAQ->initialize();

}
