<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'mk_faq_design_menu' );
add_action( 'admin_post_mk_faq_save_design', 'mk_faq_save_design' );

function mk_faq_design_menu() {
	add_submenu_page( 'edit.php?post_type=mk-simple-faq', __( 'FAQ Settings', 'simple-customizable-faq-accordion' ), __( 'Settings', 'simple-customizable-faq-accordion' ), 'edit_theme_options', 'mk-faq-settings', 'mk_faq_design_page' );
}

function mk_faq_save_design() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change these settings.', 'simple-customizable-faq-accordion' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'mk_faq_save_design' );
	$input = isset( $_POST['mk_faq_design'] ) && is_array( $_POST['mk_faq_design'] ) ? wp_unslash( $_POST['mk_faq_design'] ) : array();
	$original = isset( $_POST['mk_faq_design_original'] ) && is_array( $_POST['mk_faq_design_original'] ) ? wp_unslash( $_POST['mk_faq_design_original'] ) : array();
	foreach ( mk_simple_faq_customizer_settings() as $id => $setting ) {
		if ( array_key_exists( $id, $input ) ) {
			$value = mk_simple_faq_sanitize_design_value( $input[ $id ], $id );
			// Leave untouched fields alone if another interface saved them meanwhile.
			if ( array_key_exists( $id, $original ) && $value === mk_simple_faq_sanitize_design_value( $original[ $id ], $id ) ) {
				continue;
			}
			// Both interfaces use the same theme mods and the same validation.
			set_theme_mod( $id, $value );
		}
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=mk-simple-faq&page=mk-faq-settings&updated=1' ) );
	exit;
}

function mk_faq_design_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Settings', 'simple-customizable-faq-accordion' ); ?></h1>
		<p><?php esc_html_e( 'These settings apply to all FAQ groups in the active theme. The Customizer edits the same values. Reload any already open Customizer before making further changes there.', 'simple-customizable-faq-accordion' ); ?></p>
		<?php if ( isset( $_GET['updated'] ) && '1' === $_GET['updated'] ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'FAQ settings saved.', 'simple-customizable-faq-accordion' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="mk_faq_save_design">
			<?php wp_nonce_field( 'mk_faq_save_design' ); ?>
			<table class="form-table" role="presentation">
			<?php foreach ( mk_simple_faq_customizer_settings() as $id => $setting ) :
				$value = mk_simple_faq_sanitize_design_value( get_theme_mod( $id, $setting['default'] ), $id );
				if ( 'color' === $setting['type'] && 4 === strlen( $value ) ) {
					$value = '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
				}
				?>
				<tr><th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $setting['label'] ); ?></label></th><td>
				<input type="hidden" name="mk_faq_design_original[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>">
				<input id="<?php echo esc_attr( $id ); ?>" name="mk_faq_design[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" type="<?php echo 'color' === $setting['type'] ? 'color' : 'number'; ?>" required
				<?php if ( 'range' === $setting['type'] ) : ?> min="<?php echo (int) $setting['min']; ?>" max="<?php echo (int) $setting['max']; ?>" step="1"<?php endif; ?>>
				<?php if ( 'range' === $setting['type'] ) : ?> px<?php endif; ?>
				</td></tr>
			<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
