<?php
/**
 * Settings screen with live preview.
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin UI.
 */
class Porta_Login_Admin {

	/**
	 * Hook the menu and assets.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'wp_redirect', array( __CLASS__, 'preserve_tab_on_redirect' ) );
		add_action( 'admin_post_porta_login_apply_starter', array( __CLASS__, 'apply_starter' ) );
		add_action( 'wp_ajax_porta_login_dismiss_upsell', array( __CLASS__, 'dismiss_upsell' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( PORTA_LOGIN_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
	}

	/**
	 * Add Appearance → Sanctoria Porta.
	 */
	public static function menu() {
		add_theme_page(
			__( 'Sanctoria Porta', 'sanctoria-porta' ),
			__( 'Sanctoria Porta', 'sanctoria-porta' ),
			'manage_options',
			'porta',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Settings URL for Appearance → Sanctoria Porta.
	 *
	 * @return string
	 */
	public static function settings_url() {
		return admin_url( 'themes.php?page=porta' );
	}

	/**
	 * Add a Settings link under the plugin name, before Deactivate.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public static function action_links( $links ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}
		$settings = '<a href="' . esc_url( self::settings_url() ) . '">' . esc_html__( 'Settings', 'sanctoria-porta' ) . '</a>';
		array_unshift( $links, $settings );
		return $links;
	}

	/**
	 * Add a Settings link on the plugin row meta line.
	 *
	 * @param array  $links Row meta links.
	 * @param string $file  Plugin basename.
	 * @return array
	 */
	public static function row_meta( $links, $file ) {
		if ( plugin_basename( PORTA_LOGIN_FILE ) !== $file ) {
			return $links;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}
		$links[] = '<a href="' . esc_url( self::settings_url() ) . '">' . esc_html__( 'Settings', 'sanctoria-porta' ) . '</a>';
		return $links;
	}

	/**
	 * Enqueue settings assets.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ) {
		if ( 'appearance_page_porta' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		$css = PORTA_LOGIN_DIR . 'admin/css/admin.css';
		$js  = PORTA_LOGIN_DIR . 'admin/js/admin.js';
		wp_enqueue_style(
			'porta-login-admin',
			PORTA_LOGIN_URL . 'admin/css/admin.css',
			array( 'wp-color-picker' ),
			file_exists( $css ) ? (string) filemtime( $css ) : PORTA_LOGIN_VERSION
		);
		wp_enqueue_script(
			'porta-login-admin',
			PORTA_LOGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			file_exists( $js ) ? (string) filemtime( $js ) : PORTA_LOGIN_VERSION,
			true
		);
		$settings = Porta_Login_Settings::get();
		$logo     = $settings['logo_id'] ? wp_get_attachment_image_url( (int) $settings['logo_id'], 'full' ) : '';
		$logo_d   = ! empty( $settings['logo_id_dark'] ) ? wp_get_attachment_image_url( (int) $settings['logo_id_dark'], 'full' ) : '';
		$bg       = $settings['bg_image_id'] ? wp_get_attachment_image_url( (int) $settings['bg_image_id'], 'full' ) : '';
		wp_localize_script(
			'porta-login-admin',
			'portaLoginAdmin',
			array(
				'settings'  => $settings,
				'logoUrl'     => $logo ? $logo : '',
				'logoUrlDark' => $logo_d ? $logo_d : '',
				'bgUrl'     => $bg ? $bg : '',
				'defaultLogo' => admin_url( 'images/wordpress-logo.svg?ver=20131107' ),
				'mediaTitle'  => __( 'Select image', 'sanctoria-porta' ),
				'mediaButton' => __( 'Use this image', 'sanctoria-porta' ),
			)
		);
	}

	/**
	 * Apply a built-in starter palette.
	 */
	public static function apply_starter() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'sanctoria-porta' ) );
		}
		check_admin_referer( 'porta_login_apply_starter' );
		$key      = isset( $_GET['starter'] ) ? sanitize_key( wp_unslash( $_GET['starter'] ) ) : '';
		$starters = Porta_Login_Settings::starters();
		if ( ! isset( $starters[ $key ] ) ) {
			wp_safe_redirect( self::page_url() );
			exit;
		}
		$current = Porta_Login_Settings::get();
		$next    = array_merge( $current, $starters[ $key ] );
		unset( $next['label'] );
		update_option( Porta_Login_Settings::OPTION, Porta_Login_Settings::sanitize( $next ) );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'porta',
					'porta_login_msg' => 'starter',
				),
				admin_url( 'themes.php' )
			)
		);
		exit;
	}

	/**
	 * Settings page URL.
	 *
	 * @return string
	 */
	public static function page_url() {
		return add_query_arg( 'page', 'porta', admin_url( 'themes.php' ) );
	}

	/**
	 * Dismiss the Pro note (per user).
	 */
	public static function dismiss_upsell() {
		check_ajax_referer( 'porta_login_dismiss_upsell', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		update_user_meta( get_current_user_id(), 'porta_login_upsell_dismissed', 1 );
		wp_send_json_success();
	}

	/**
	 * Render the settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s        = Porta_Login_Settings::get();
		$starters = Porta_Login_Settings::starters();
		$tabs     = array(
			'design'  => __( 'Design', 'sanctoria-porta' ),
			'messages'=> __( 'Messages & visibility', 'sanctoria-porta' ),
			'login'   => __( 'Sign-in options', 'sanctoria-porta' ),
		);
		/**
		 * Filter settings tabs. Pro can add more.
		 *
		 * @param array $tabs Tabs.
		 */
		$tabs = apply_filters( 'porta_login_settings_tabs', $tabs );
		$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'design'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $tabs[ $active ] ) ) {
			$active = 'design';
		}
		?>
		<div class="wrap porta-login-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php if ( isset( $_GET['porta_login_msg'] ) && 'starter' === $_GET['porta_login_msg'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Starter style applied. You can keep editing below.', 'sanctoria-porta' ); ?></p></div>
			<?php endif; ?>
			<p class="porta-login-lead"><?php esc_html_e( 'Brand the WordPress login, registration, and lost-password screens. Changes stay on this site — nothing is sent to an external service.', 'sanctoria-porta' ); ?></p>

			<div class="porta-login-starters">
				<span><?php esc_html_e( 'Start from a palette:', 'sanctoria-porta' ); ?></span>
				<?php foreach ( $starters as $key => $starter ) : ?>
					<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'porta_login_apply_starter', 'starter' => $key ), admin_url( 'admin-post.php' ) ), 'porta_login_apply_starter' ) ); ?>">
						<?php echo esc_html( $starter['label'] ); ?>
					</a>
				<?php endforeach; ?>
				<?php do_action( 'porta_login_after_starters' ); ?>
			</div>

			<div class="porta-login-layout">
				<div class="porta-login-main">
					<nav class="porta-login-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'sanctoria-porta' ); ?>">
						<?php foreach ( $tabs as $id => $label ) : ?>
							<a href="#porta-login-tab-<?php echo esc_attr( $id ); ?>" class="porta-login-tab<?php echo $active === $id ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php endforeach; ?>
					</nav>
				<form method="post" action="options.php" class="porta-login-form">
					<?php settings_fields( 'porta_login' ); ?>
					<input type="hidden" name="porta_login_active_tab" class="porta-login-active-tab" value="<?php echo esc_attr( $active ); ?>" />

					<div id="porta-login-tab-design" class="porta-login-panel<?php echo 'design' === $active ? ' is-active' : ''; ?>">
						<?php self::field_media( 'logo_id', __( 'Logo (for light backgrounds)', 'sanctoria-porta' ), $s['logo_id'] ); ?>
						<?php self::field_media( 'logo_id_dark', __( 'Logo (for dark backgrounds)', 'sanctoria-porta' ), $s['logo_id_dark'] ?? 0 ); ?>
						<p class="description" style="margin:-8px 0 16px 0;"><?php esc_html_e( 'Use a transparent PNG. Sanctoria Porta picks the dark-background logo automatically when the login page background is dark.', 'sanctoria-porta' ); ?></p>
						<?php self::field_number( 'logo_width', __( 'Logo width (px)', 'sanctoria-porta' ), $s['logo_width'], 32, 400 ); ?>
						<?php self::field_number( 'logo_height', __( 'Logo height (px)', 'sanctoria-porta' ), $s['logo_height'], 32, 400 ); ?>
						<?php self::field_text( 'logo_url', __( 'Logo link URL', 'sanctoria-porta' ), $s['logo_url'], 'https://' ); ?>
						<?php self::field_text( 'logo_title', __( 'Logo hover title', 'sanctoria-porta' ), $s['logo_title'] ); ?>

						<?php self::field_select( 'bg_type', __( 'Background type', 'sanctoria-porta' ), $s['bg_type'], array( 'color' => __( 'Solid color', 'sanctoria-porta' ), 'gradient' => __( 'Gradient', 'sanctoria-porta' ), 'image' => __( 'Image', 'sanctoria-porta' ) ) ); ?>
						<?php self::field_color( 'bg_color', __( 'Background color', 'sanctoria-porta' ), $s['bg_color'] ); ?>
						<?php self::field_color( 'bg_color_2', __( 'Gradient second color', 'sanctoria-porta' ), $s['bg_color_2'] ); ?>
						<?php self::field_number( 'bg_gradient_angle', __( 'Gradient angle', 'sanctoria-porta' ), $s['bg_gradient_angle'], 0, 360 ); ?>
						<?php self::field_media( 'bg_image_id', __( 'Background image', 'sanctoria-porta' ), $s['bg_image_id'] ); ?>

						<?php self::field_color( 'form_bg', __( 'Form background', 'sanctoria-porta' ), $s['form_bg'] ); ?>
						<?php self::field_color( 'form_border_color', __( 'Form border color', 'sanctoria-porta' ), $s['form_border_color'] ); ?>
						<?php self::field_number( 'form_border_width', __( 'Form border width (px)', 'sanctoria-porta' ), $s['form_border_width'], 0, 12 ); ?>
						<?php self::field_number( 'form_radius', __( 'Form corner radius (px)', 'sanctoria-porta' ), $s['form_radius'], 0, 40 ); ?>
						<?php self::field_number( 'form_padding', __( 'Form padding (px)', 'sanctoria-porta' ), $s['form_padding'], 8, 64 ); ?>
						<?php self::field_select( 'form_shadow', __( 'Form shadow', 'sanctoria-porta' ), $s['form_shadow'], array( 'none' => __( 'None', 'sanctoria-porta' ), 'light' => __( 'Light', 'sanctoria-porta' ), 'medium' => __( 'Medium', 'sanctoria-porta' ), 'heavy' => __( 'Heavy', 'sanctoria-porta' ) ) ); ?>
						<?php self::field_number( 'form_width', __( 'Form width (px)', 'sanctoria-porta' ), $s['form_width'], 240, 560 ); ?>

						<?php self::field_color( 'input_bg', __( 'Input background', 'sanctoria-porta' ), $s['input_bg'] ); ?>
						<?php self::field_color( 'input_text', __( 'Input text', 'sanctoria-porta' ), $s['input_text'] ); ?>
						<?php self::field_color( 'input_border', __( 'Input border', 'sanctoria-porta' ), $s['input_border'] ); ?>
						<?php self::field_color( 'input_focus', __( 'Input focus color', 'sanctoria-porta' ), $s['input_focus'] ); ?>
						<?php self::field_color( 'button_bg', __( 'Button color', 'sanctoria-porta' ), $s['button_bg'] ); ?>
						<?php self::field_color( 'button_text', __( 'Button text', 'sanctoria-porta' ), $s['button_text'] ); ?>
						<?php self::field_color( 'button_hover_bg', __( 'Button hover color', 'sanctoria-porta' ), $s['button_hover_bg'] ); ?>
						<?php self::field_color( 'button_hover_text', __( 'Button hover text', 'sanctoria-porta' ), $s['button_hover_text'] ); ?>
						<?php self::field_color( 'label_color', __( 'Label color', 'sanctoria-porta' ), $s['label_color'] ); ?>
						<?php self::field_color( 'link_color', __( 'Link color', 'sanctoria-porta' ), $s['link_color'] ); ?>
						<?php self::field_color( 'link_hover', __( 'Link hover color', 'sanctoria-porta' ), $s['link_hover'] ); ?>

						<?php self::field_textarea( 'custom_css', __( 'Custom CSS', 'sanctoria-porta' ), $s['custom_css'], __( 'Added only on the login screens. Do not paste scripts here.', 'sanctoria-porta' ) ); ?>
					</div>

					<div id="porta-login-tab-messages" class="porta-login-panel<?php echo 'messages' === $active ? ' is-active' : ''; ?>">
						<?php self::field_checkbox( 'hide_back_to_blog', __( 'Hide the “Go to site” link', 'sanctoria-porta' ), $s['hide_back_to_blog'] ); ?>
						<?php self::field_checkbox( 'hide_language', __( 'Hide the language switcher', 'sanctoria-porta' ), $s['hide_language'] ); ?>
						<?php self::field_checkbox( 'custom_error', __( 'Use a single custom error message', 'sanctoria-porta' ), $s['custom_error'] ); ?>
						<?php self::field_text( 'custom_error_message', __( 'Custom error message', 'sanctoria-porta' ), $s['custom_error_message'], __( 'We could not sign you in. Check your details and try again.', 'sanctoria-porta' ) ); ?>
					</div>

					<div id="porta-login-tab-login" class="porta-login-panel<?php echo 'login' === $active ? ' is-active' : ''; ?>">
						<?php self::field_select( 'login_with', __( 'Sign in with', 'sanctoria-porta' ), $s['login_with'], array( 'username_or_email' => __( 'Username or email', 'sanctoria-porta' ), 'username' => __( 'Username only', 'sanctoria-porta' ), 'email' => __( 'Email only', 'sanctoria-porta' ) ) ); ?>
						<?php self::field_checkbox( 'show_password', __( 'Keep WordPress’s built-in “show password” eye toggle', 'sanctoria-porta' ), $s['show_password'] ); ?>
						<?php self::field_checkbox( 'remember_me_default', __( 'Tick “Remember me” by default', 'sanctoria-porta' ), $s['remember_me_default'] ); ?>
						<?php self::field_checkbox( 'honeypot', __( 'Add a hidden honeypot field to deter simple bots (no third-party captcha)', 'sanctoria-porta' ), $s['honeypot'] ); ?>
						<?php self::field_checkbox( 'lockout_enabled', __( 'Limit failed sign-in attempts by IP', 'sanctoria-porta' ), $s['lockout_enabled'] ); ?>
						<?php self::field_number( 'lockout_limit', __( 'Failed attempts before lockout', 'sanctoria-porta' ), $s['lockout_limit'], 2, 20 ); ?>
						<?php self::field_number( 'lockout_minutes', __( 'Lockout duration (minutes)', 'sanctoria-porta' ), $s['lockout_minutes'], 1, 1440 ); ?>
						<p class="description"><?php esc_html_e( 'If you ever need to bypass a lockout, add this line to wp-config.php: define( \'PORTA_LOGIN_DISABLE_LOCKOUT\', true );', 'sanctoria-porta' ); ?></p>
					</div>

					<?php submit_button( __( 'Save changes', 'sanctoria-porta' ) ); ?>
				</form>

				<?php
				// Pro panels (License, Passkeys, etc.) render after this form so they
				// can include their own <form> tags. Nested forms are invalid HTML and
				// caused the browser to close this form early, hiding those panels.
				do_action( 'porta_login_settings_panels', $s );
				?>
				</div>

				<div class="porta-login-preview-col">
					<div class="porta-login-preview-toolbar">
						<strong><?php esc_html_e( 'Live preview', 'sanctoria-porta' ); ?></strong>
						<a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open the real login page', 'sanctoria-porta' ); ?></a>
					</div>
					<div class="porta-login-preview" id="porta-login-preview">
						<div class="lf-preview-frame" id="lf-preview-frame">
							<h1 class="lf-preview-logo"><a href="#" id="lf-preview-logo-link"><span class="screen-reader-text" id="lf-preview-logo-title"><?php bloginfo( 'name' ); ?></span></a></h1>
							<form class="lf-preview-form" action="#" method="post" onsubmit="return false;">
								<p>
									<label id="lf-preview-user-label"><?php esc_html_e( 'Username or Email Address', 'sanctoria-porta' ); ?></label>
									<input type="text" class="lf-preview-input" value="" readonly />
								</p>
								<p>
									<label><?php esc_html_e( 'Password', 'sanctoria-porta' ); ?></label>
									<input type="password" class="lf-preview-input" value="••••••••" readonly />
								</p>
								<p class="lf-preview-forget"><label><input type="checkbox" disabled /> <?php esc_html_e( 'Remember Me', 'sanctoria-porta' ); ?></label></p>
								<p class="lf-preview-submit"><button type="button" class="lf-preview-button"><?php esc_html_e( 'Log In', 'sanctoria-porta' ); ?></button></p>
							</form>
							<p class="lf-preview-nav"><a href="#"><?php esc_html_e( 'Lost your password?', 'sanctoria-porta' ); ?></a></p>
							<p class="lf-preview-back" id="lf-preview-back"><a href="#"><?php esc_html_e( '&larr; Go to site', 'sanctoria-porta' ); ?></a></p>
						</div>
					</div>
				</div>
			</div>

			<?php
			$dismissed = get_user_meta( get_current_user_id(), 'porta_login_upsell_dismissed', true );
			if ( ! $dismissed && ! defined( 'PORTA_LOGIN_PRO_VERSION' ) ) :
				?>
				<div class="porta-login-upsell" id="porta-login-upsell">
					<p><?php esc_html_e( 'Sanctoria Porta Pro adds a template library, passkey (Windows Hello / Face ID / Touch ID) sign-in, a custom login URL with a safe recovery path, role-based redirects, white-label admin branding, and tools for agencies managing many sites. It is a one-time purchase: Porta Pro ($20, one site, three years of updates), Porta Pro Trinity ($50, three sites, lifetime updates), or Porta Pro Infinity ($99, unlimited sites, lifetime updates). The free plugin stays fully usable on its own.', 'sanctoria-porta' ); ?></p>
					<button type="button" class="button-link" id="porta-login-dismiss-upsell" data-nonce="<?php echo esc_attr( wp_create_nonce( 'porta_login_dismiss_upsell' ) ); ?>"><?php esc_html_e( 'Dismiss', 'sanctoria-porta' ); ?></button>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Keep the open settings tab in the URL after the free form is saved.
	 *
	 * @param string $location Redirect location.
	 * @return string
	 */
	public static function preserve_tab_on_redirect( $location ) {
		if ( ! is_string( $location ) || false === strpos( $location, 'page=porta' ) ) {
			return $location;
		}
		if ( empty( $_POST['porta_login_active_tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $location;
		}
		$tab = sanitize_key( wp_unslash( $_POST['porta_login_active_tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $tab && false === strpos( $location, '#porta-login-tab-' ) ) {
			$location .= '#porta-login-tab-' . $tab;
		}
		return $location;
	}

	/**
	 * Color field.
	 *
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param string $value Value.
	 */
	private static function field_color( $name, $label, $value ) {
		self::row(
			$label,
			sprintf(
				'<input type="text" class="porta-login-color" name="%1$s[%2$s]" id="%2$s" value="%3$s" data-default-color="" />',
				esc_attr( Porta_Login_Settings::OPTION ),
				esc_attr( $name ),
				esc_attr( $value )
			)
		);
	}

	/**
	 * Text field.
	 *
	 * @param string $name        Name.
	 * @param string $label       Label.
	 * @param string $value       Value.
	 * @param string $placeholder Placeholder.
	 */
	private static function field_text( $name, $label, $value, $placeholder = '' ) {
		self::row(
			$label,
			sprintf(
				'<input type="text" class="regular-text" name="%1$s[%2$s]" id="%2$s" value="%3$s" placeholder="%4$s" />',
				esc_attr( Porta_Login_Settings::OPTION ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $placeholder )
			)
		);
	}

	/**
	 * Number field.
	 *
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param mixed  $value Value.
	 * @param int    $min   Min.
	 * @param int    $max   Max.
	 */
	private static function field_number( $name, $label, $value, $min, $max ) {
		self::row(
			$label,
			sprintf(
				'<input type="number" class="small-text" name="%1$s[%2$s]" id="%2$s" value="%3$s" min="%4$d" max="%5$d" />',
				esc_attr( Porta_Login_Settings::OPTION ),
				esc_attr( $name ),
				esc_attr( $value ),
				$min,
				$max
			)
		);
	}

	/**
	 * Checkbox field.
	 *
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param mixed  $value Value.
	 */
	private static function field_checkbox( $name, $label, $value ) {
		printf(
			'<p class="porta-login-check"><label><input type="checkbox" name="%1$s[%2$s]" id="%2$s" value="1" %3$s /> %4$s</label></p>',
			esc_attr( Porta_Login_Settings::OPTION ),
			esc_attr( $name ),
			checked( 1, (int) $value, false ),
			esc_html( $label )
		);
	}

	/**
	 * Select field.
	 *
	 * @param string $name    Name.
	 * @param string $label   Label.
	 * @param string $value   Value.
	 * @param array  $options Options.
	 */
	private static function field_select( $name, $label, $value, $options ) {
		$html = sprintf(
			'<select name="%1$s[%2$s]" id="%2$s">',
			esc_attr( Porta_Login_Settings::OPTION ),
			esc_attr( $name )
		);
		foreach ( $options as $k => $lab ) {
			$html .= '<option value="' . esc_attr( $k ) . '" ' . selected( $value, $k, false ) . '>' . esc_html( $lab ) . '</option>';
		}
		$html .= '</select>';
		self::row( $label, $html );
	}

	/**
	 * Textarea.
	 *
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param string $help  Help.
	 */
	private static function field_textarea( $name, $label, $value, $help = '' ) {
		$html = sprintf(
			'<textarea name="%1$s[%2$s]" id="%2$s" class="large-text code" rows="6">%3$s</textarea>',
			esc_attr( Porta_Login_Settings::OPTION ),
			esc_attr( $name ),
			esc_textarea( $value )
		);
		if ( $help ) {
			$html .= '<p class="description">' . esc_html( $help ) . '</p>';
		}
		self::row( $label, $html );
	}

	/**
	 * Media picker.
	 *
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param int    $value Attachment ID.
	 */
	private static function field_media( $name, $label, $value ) {
		$url  = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
		$full = $value ? wp_get_attachment_image_url( (int) $value, 'full' ) : '';
		$html = sprintf(
			'<div class="porta-login-media" data-target="%1$s"%4$s><input type="hidden" name="%2$s[%1$s]" id="%1$s" value="%3$s" />',
			esc_attr( $name ),
			esc_attr( Porta_Login_Settings::OPTION ),
			esc_attr( $value ),
			$full ? ' data-url="' . esc_url( $full ) . '"' : ''
		);
		$html .= '<div class="porta-login-media-preview">';
		if ( $url ) {
			$html .= '<img src="' . esc_url( $url ) . '" alt="" />';
		}
		$html .= '</div>';
		$html .= '<button type="button" class="button porta-login-media-select">' . esc_html__( 'Choose image', 'sanctoria-porta' ) . '</button> ';
		$html .= '<button type="button" class="button-link porta-login-media-clear">' . esc_html__( 'Remove', 'sanctoria-porta' ) . '</button></div>';
		self::row( $label, $html );
	}

	/**
	 * Settings row.
	 *
	 * @param string $label Label.
	 * @param string $field Field HTML (already escaped by callers).
	 */
	private static function row( $label, $field ) {
		echo '<div class="porta-login-row"><div class="porta-login-row-label"><label>' . esc_html( $label ) . '</label></div><div class="porta-login-row-field">' . $field . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
