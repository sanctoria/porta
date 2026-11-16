<?php
/**
 * Login screen CSS and logo filters.
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Styles the wp-login.php screens.
 */
class Porta_Login_Styles {

	/**
	 * Hook output.
	 */
	public static function init() {
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'logo_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'logo_title' ) );
		add_filter( 'login_display_language_dropdown', array( __CLASS__, 'language_dropdown' ) );
		add_filter( 'login_errors', array( __CLASS__, 'filter_errors' ) );
	}

	/**
	 * Enqueue generated CSS on the login screen only.
	 */
	public static function enqueue() {
		$css = self::build_css();

		/**
		 * Filter the generated login CSS.
		 *
		 * @param string $css CSS.
		 */
		$css = apply_filters( 'porta_login_css', $css );

		wp_register_style( 'porta-login-login', false, array(), PORTA_LOGIN_VERSION );
		wp_enqueue_style( 'porta-login-login' );
		if ( $css ) {
			wp_add_inline_style( 'porta-login-login', $css );
		}

		$settings = Porta_Login_Settings::get();
		if ( ! empty( $settings['remember_me_default'] ) ) {
			wp_enqueue_script(
				'porta-login-login',
				PORTA_LOGIN_URL . 'admin/js/login-page.js',
				array(),
				PORTA_LOGIN_VERSION,
				true
			);
			wp_localize_script(
				'porta-login-login',
				'portaLoginLogin',
				array(
					'rememberMeDefault' => true,
				)
			);
		}
	}

	/**
	 * Build CSS from settings. Skips rules when the related value is empty.
	 *
	 * @return string
	 */
	public static function build_css() {
		$s   = Porta_Login_Settings::get();
		$css = '';

		if ( empty( $s['show_password'] ) ) {
			$css .= "body.login .wp-hide-pw { display:none !important; }
";
		}

		$bg = self::background_css( $s );
		if ( $bg ) {
			$css .= "body.login { {$bg} }\n";
		}

		$logo_css = self::logo_css( $s );
		if ( $logo_css ) {
			$css .= $logo_css;
		}

		$form = array();
		if ( ! empty( $s['form_bg'] ) ) {
			$form[] = 'background-color:' . $s['form_bg'];
		}
		if ( ! empty( $s['form_border_color'] ) || ! empty( $s['form_border_width'] ) ) {
			$w      = isset( $s['form_border_width'] ) ? (int) $s['form_border_width'] : 0;
			$c      = ! empty( $s['form_border_color'] ) ? $s['form_border_color'] : 'transparent';
			$form[] = 'border:' . $w . 'px solid ' . $c;
		}
		if ( ! empty( $s['form_radius'] ) ) {
			$form[] = 'border-radius:' . (int) $s['form_radius'] . 'px';
		}
		if ( ! empty( $s['form_padding'] ) && 24 !== (int) $s['form_padding'] ) {
			$form[] = 'padding:' . (int) $s['form_padding'] . 'px';
		}
		$shadow = self::shadow( $s['form_shadow'] ?? 'none' );
		if ( $shadow ) {
			$form[] = 'box-shadow:' . $shadow;
		}
		if ( $form ) {
			$css .= 'body.login #login form, body.login form { ' . implode( ';', $form ) . "; }\n";
		}

		if ( ! empty( $s['form_width'] ) && 320 !== (int) $s['form_width'] ) {
			$css .= 'body.login #login { width:' . (int) $s['form_width'] . "px; }\n";
			$css .= "body.login #login { padding-top:32px; }\n";
		}

		$input = array();
		if ( ! empty( $s['input_bg'] ) ) {
			$input[] = 'background-color:' . $s['input_bg'];
		}
		if ( ! empty( $s['input_text'] ) ) {
			$input[] = 'color:' . $s['input_text'];
		}
		if ( ! empty( $s['input_border'] ) ) {
			$input[] = 'border-color:' . $s['input_border'];
		}
		if ( $input ) {
			$css .= 'body.login form .input, body.login input[type="text"], body.login input[type="password"], body.login input[type="email"] { ' . implode( ';', $input ) . "; }\n";
		}
		if ( ! empty( $s['input_focus'] ) ) {
			$css .= 'body.login form .input:focus { border-color:' . $s['input_focus'] . '; box-shadow:0 0 0 1px ' . $s['input_focus'] . "; }\n";
		}

		$btn = array();
		if ( ! empty( $s['button_bg'] ) ) {
			$btn[] = 'background:' . $s['button_bg'];
			$btn[] = 'border-color:' . $s['button_bg'];
		}
		if ( ! empty( $s['button_text'] ) ) {
			$btn[] = 'color:' . $s['button_text'];
		}
		if ( $btn ) {
			$css .= 'body.login .button-primary { ' . implode( ';', $btn ) . "; text-shadow:none; box-shadow:none; }\n";
		}
		$hov = array();
		if ( ! empty( $s['button_hover_bg'] ) ) {
			$hov[] = 'background:' . $s['button_hover_bg'];
			$hov[] = 'border-color:' . $s['button_hover_bg'];
		}
		if ( ! empty( $s['button_hover_text'] ) ) {
			$hov[] = 'color:' . $s['button_hover_text'];
		}
		if ( $hov ) {
			$css .= 'body.login .button-primary:hover, body.login .button-primary:focus { ' . implode( ';', $hov ) . "; }\n";
		}

		if ( ! empty( $s['label_color'] ) ) {
			$css .= 'body.login form label, body.login .user-pass-wrap label { color:' . $s['label_color'] . "; }\n";
		}
		if ( ! empty( $s['link_color'] ) ) {
			$css .= 'body.login #nav, body.login #backtoblog, body.login .privacy-policy-page-link { color:' . $s['link_color'] . '; text-align:center; }' . "\n";
			$css .= 'body.login #nav a, body.login #nav a:visited, body.login #backtoblog a, body.login #backtoblog a:visited, body.login .privacy-policy-page-link a, body.login .privacy-policy-page-link a:visited { color:' . $s['link_color'] . "; }\n";
		}
		if ( ! empty( $s['link_hover'] ) ) {
			$css .= 'body.login #nav a:hover, body.login #nav a:focus, body.login #backtoblog a:hover, body.login #backtoblog a:focus, body.login .privacy-policy-page-link a:hover, body.login .privacy-policy-page-link a:focus { color:' . $s['link_hover'] . "; }\n";
		}

		if ( ! empty( $s['hide_back_to_blog'] ) ) {
			$css .= "body.login #backtoblog { display:none !important; }\n";
		}

		$custom = trim( (string) ( $s['custom_css'] ?? '' ) );
		if ( $custom ) {
			$css .= "\n" . $custom . "\n";
		}

		return $css;
	}

	/**
	 * Background declaration.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private static function background_css( $s ) {
		$type = $s['bg_type'] ?? 'color';
		if ( 'image' === $type && ! empty( $s['bg_image_id'] ) ) {
			$url = wp_get_attachment_image_url( (int) $s['bg_image_id'], 'full' );
			if ( $url ) {
				$color = ! empty( $s['bg_color'] ) ? $s['bg_color'] : '#f0f0f1';
				return 'background-color:' . $color . ';background-image:url(' . esc_url( $url ) . ');background-size:cover;background-position:center;background-repeat:no-repeat;';
			}
		}
		if ( 'gradient' === $type && ! empty( $s['bg_color'] ) && ! empty( $s['bg_color_2'] ) ) {
			$angle = isset( $s['bg_gradient_angle'] ) ? (int) $s['bg_gradient_angle'] : 160;
			return 'background:' . $s['bg_color'] . ';background:linear-gradient(' . $angle . 'deg,' . $s['bg_color'] . ',' . $s['bg_color_2'] . ');';
		}
		if ( ! empty( $s['bg_color'] ) ) {
			return 'background-color:' . $s['bg_color'] . ';';
		}
		return '';
	}

	/**
	 * Logo CSS.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private static function logo_css( $s ) {
		$url = Porta_Login_Settings::logo_url( $s );
		if ( ! $url ) {
			return '';
		}
		$w = isset( $s['logo_width'] ) ? (int) $s['logo_width'] : 84;
		$h = isset( $s['logo_height'] ) ? (int) $s['logo_height'] : 84;
		return 'body.login h1 a { background-color:transparent !important; background-image:url(' . esc_url( $url ) . ') !important; background-size:contain; background-position:center; background-repeat:no-repeat; width:' . $w . 'px; height:' . $h . 'px; box-shadow:none; }' . "\n";
	}

	/**
	 * Box shadow value.
	 *
	 * @param string $level Level.
	 * @return string
	 */
	private static function shadow( $level ) {
		$map = array(
			'light'  => '0 8px 24px rgba(15,23,32,.08)',
			'medium' => '0 16px 40px rgba(15,23,32,.16)',
			'heavy'  => '0 24px 60px rgba(15,23,32,.28)',
		);
		return $map[ $level ] ?? '';
	}

	/**
	 * Logo link.
	 *
	 * @param string $url Default URL.
	 * @return string
	 */
	public static function logo_url( $url ) {
		$custom = Porta_Login_Settings::get_value( 'logo_url', '' );
		if ( $custom ) {
			return $custom;
		}
		return home_url( '/' );
	}

	/**
	 * Logo title text.
	 *
	 * @param string $title Default title.
	 * @return string
	 */
	public static function logo_title( $title ) {
		$custom = Porta_Login_Settings::get_value( 'logo_title', '' );
		if ( $custom ) {
			return $custom;
		}
		return get_bloginfo( 'name', 'display' );
	}

	/**
	 * Maybe hide the language switcher.
	 *
	 * @param bool $show Whether to show.
	 * @return bool
	 */
	public static function language_dropdown( $show ) {
		if ( Porta_Login_Settings::get_value( 'hide_language', 0 ) ) {
			return false;
		}
		return $show;
	}

	/**
	 * Optional unified error message (does not change lockout or honeypot messages already set).
	 *
	 * @param string $errors Errors HTML.
	 * @return string
	 */
	public static function filter_errors( $errors ) {
		if ( ! Porta_Login_Settings::get_value( 'custom_error', 0 ) ) {
			return $errors;
		}
		$message = Porta_Login_Settings::get_value( 'custom_error_message', '' );
		if ( ! $message ) {
			$message = __( 'We could not sign you in. Check your details and try again.', 'porta' );
		}
		return $message;
	}
}
