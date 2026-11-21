<?php
/**
 * Settings storage and sanitization.
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings helper.
 */
class Porta_Login_Settings {

	const OPTION = 'porta_login_settings';

	/**
	 * Register the option.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register setting.
	 */
	public static function register() {
		register_setting(
			'porta_login',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Default settings. Empty visual fields leave the core WordPress login unchanged.
	 *
	 * @return array
	 */
	public static function defaults() {
		$defaults = array(
			'logo_id'              => 0,
			'logo_id_dark'         => 0,
			'logo_width'           => 84,
			'logo_height'          => 84,
			'logo_url'             => '',
			'logo_title'           => '',
			'bg_type'              => 'color',
			'bg_color'             => '',
			'bg_color_2'           => '',
			'bg_gradient_angle'    => 160,
			'bg_image_id'          => 0,
			'form_bg'              => '',
			'form_border_color'    => '',
			'form_border_width'    => 0,
			'form_radius'          => 0,
			'form_padding'         => 24,
			'form_shadow'          => 'none',
			'form_width'           => 320,
			'input_bg'             => '',
			'input_text'           => '',
			'input_border'         => '',
			'input_focus'          => '',
			'button_bg'            => '',
			'button_text'          => '',
			'button_hover_bg'      => '',
			'button_hover_text'    => '',
			'label_color'          => '',
			'link_color'           => '',
			'link_hover'           => '',
			'hide_back_to_blog'    => 0,
			'hide_language'        => 0,
			'custom_error'         => 0,
			'custom_error_message' => '',
			'custom_css'           => '',
			'login_with'           => 'username_or_email',
			'show_password'        => 1,
			'remember_me_default'  => 0,
			'honeypot'             => 1,
			'lockout_enabled'      => 1,
			'lockout_limit'        => 5,
			'lockout_minutes'      => 15,
		);

		/**
		 * Filter default Sanctoria Porta settings.
		 *
		 * @param array $defaults Defaults.
		 */
		return apply_filters( 'porta_login_default_settings', $defaults );
	}

	/**
	 * Get merged settings.
	 *
	 * @return array
	 */
	public static function get() {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		$settings = array_merge( self::defaults(), $stored );

		/**
		 * Filter saved Sanctoria Porta settings.
		 *
		 * @param array $settings Settings.
		 */
		return apply_filters( 'porta_login_settings', $settings );
	}

	/**
	 * Get one setting.
	 *
	 * @param string $key     Key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	public static function get_value( $key, $fallback = '' ) {
		$settings = self::get();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $fallback;
	}

	/**
	 * Sanitize the settings array.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$out = $defaults;

		$out['logo_id']           = absint( $input['logo_id'] ?? 0 );
		$out['logo_id_dark']      = absint( $input['logo_id_dark'] ?? 0 );
		$out['logo_width']        = self::int_range( $input['logo_width'] ?? 84, 32, 400, 84 );
		$out['logo_height']       = self::int_range( $input['logo_height'] ?? 84, 32, 400, 84 );
		$out['logo_url']          = esc_url_raw( $input['logo_url'] ?? '' );
		$out['logo_title']        = sanitize_text_field( $input['logo_title'] ?? '' );
		$out['bg_type']           = in_array( $input['bg_type'] ?? 'color', array( 'color', 'gradient', 'image' ), true ) ? $input['bg_type'] : 'color';
		$out['bg_color']          = self::sanitize_hex( $input['bg_color'] ?? '' );
		$out['bg_color_2']        = self::sanitize_hex( $input['bg_color_2'] ?? '' );
		$out['bg_gradient_angle'] = self::int_range( $input['bg_gradient_angle'] ?? 160, 0, 360, 160 );
		$out['bg_image_id']       = absint( $input['bg_image_id'] ?? 0 );
		$out['form_bg']           = self::sanitize_hex( $input['form_bg'] ?? '' );
		$out['form_border_color'] = self::sanitize_hex( $input['form_border_color'] ?? '' );
		$out['form_border_width'] = self::int_range( $input['form_border_width'] ?? 0, 0, 12, 0 );
		$out['form_radius']       = self::int_range( $input['form_radius'] ?? 0, 0, 40, 0 );
		$out['form_padding']      = self::int_range( $input['form_padding'] ?? 24, 8, 64, 24 );
		$out['form_shadow']       = in_array( $input['form_shadow'] ?? 'none', array( 'none', 'light', 'medium', 'heavy' ), true ) ? $input['form_shadow'] : 'none';
		$out['form_width']        = self::int_range( $input['form_width'] ?? 320, 240, 560, 320 );
		$out['input_bg']          = self::sanitize_hex( $input['input_bg'] ?? '' );
		$out['input_text']        = self::sanitize_hex( $input['input_text'] ?? '' );
		$out['input_border']      = self::sanitize_hex( $input['input_border'] ?? '' );
		$out['input_focus']       = self::sanitize_hex( $input['input_focus'] ?? '' );
		$out['button_bg']         = self::sanitize_hex( $input['button_bg'] ?? '' );
		$out['button_text']       = self::sanitize_hex( $input['button_text'] ?? '' );
		$out['button_hover_bg']   = self::sanitize_hex( $input['button_hover_bg'] ?? '' );
		$out['button_hover_text'] = self::sanitize_hex( $input['button_hover_text'] ?? '' );
		$out['label_color']       = self::sanitize_hex( $input['label_color'] ?? '' );
		$out['link_color']        = self::sanitize_hex( $input['link_color'] ?? '' );
		$out['link_hover']        = self::sanitize_hex( $input['link_hover'] ?? '' );
		$out['hide_back_to_blog'] = empty( $input['hide_back_to_blog'] ) ? 0 : 1;
		$out['hide_language']     = empty( $input['hide_language'] ) ? 0 : 1;
		$out['custom_error']      = empty( $input['custom_error'] ) ? 0 : 1;
		$out['custom_error_message'] = sanitize_text_field( $input['custom_error_message'] ?? '' );
		$out['custom_css']        = self::sanitize_css( $input['custom_css'] ?? '' );

		$login_with = $input['login_with'] ?? 'username_or_email';
		$out['login_with'] = in_array( $login_with, array( 'username_or_email', 'username', 'email' ), true ) ? $login_with : 'username_or_email';
		$out['show_password']       = empty( $input['show_password'] ) ? 0 : 1;
		$out['remember_me_default'] = empty( $input['remember_me_default'] ) ? 0 : 1;
		$out['honeypot']            = empty( $input['honeypot'] ) ? 0 : 1;
		$out['lockout_enabled']     = empty( $input['lockout_enabled'] ) ? 0 : 1;
		$out['lockout_limit']       = self::int_range( $input['lockout_limit'] ?? 5, 2, 20, 5 );
		$out['lockout_minutes']     = self::int_range( $input['lockout_minutes'] ?? 15, 1, 1440, 15 );

		/**
		 * Filter sanitized settings (Pro may add keys).
		 *
		 * @param array $out   Sanitized.
		 * @param array $input Raw input.
		 */
		return apply_filters( 'porta_login_sanitize_settings', $out, $input );
	}

	/**
	 * Clamp an integer.
	 *
	 * @param mixed $value   Value.
	 * @param int   $min     Min.
	 * @param int   $max     Max.
	 * @param int   $default Default.
	 * @return int
	 */
	public static function int_range( $value, $min, $max, $default ) {
		if ( ! is_numeric( $value ) ) {
			return $default;
		}
		$value = (int) $value;
		if ( $value < $min ) {
			return $min;
		}
		if ( $value > $max ) {
			return $max;
		}
		return $value;
	}

	/**
	 * Sanitize a #RRGGBB or #RGB color, or empty.
	 *
	 * @param string $color Color.
	 * @return string
	 */
	public static function sanitize_hex( $color ) {
		$color = trim( (string) $color );
		if ( '' === $color ) {
			return '';
		}
		$sanitized = sanitize_hex_color( $color );
		return $sanitized ? $sanitized : '';
	}

	/**
	 * Very conservative CSS sanitizer: strip tags, null bytes, and @import / expression.
	 *
	 * @param string $css CSS.
	 * @return string
	 */
	public static function sanitize_css( $css ) {
		$css = (string) $css;
		$css = wp_check_invalid_utf8( $css );
		$css = wp_strip_all_tags( $css );
		$css = str_replace( "\0", '', $css );
		$css = preg_replace( '/@import\b/i', '', $css );
		$css = preg_replace( '/expression\s*\(/i', '', $css );
		$css = preg_replace( '/javascript\s*:/i', '', $css );
		$css = preg_replace( '/behavior\s*:/i', '', $css );
		return $css;
	}

	/**
	 * Two built-in starter palettes (applied only when the user clicks them).
	 *
	 * @return array
	 */
	public static function starters() {
		return array(
			'clean_light' => array(
				'label'             => __( 'Clean Light', 'sanctoria-porta' ),
				'bg_type'           => 'color',
				'bg_color'          => '#f3f1ec',
				'form_bg'           => '#ffffff',
				'form_border_color' => '#e4dfd6',
				'form_border_width' => 1,
				'form_radius'       => 12,
				'form_padding'      => 32,
				'form_shadow'       => 'light',
				'form_width'        => 360,
				'input_bg'          => '#ffffff',
				'input_text'        => '#1c1917',
				'input_border'      => '#d6d3d1',
				'input_focus'       => '#1d4e4a',
				'button_bg'         => '#1d4e4a',
				'button_text'       => '#ffffff',
				'button_hover_bg'   => '#163d3a',
				'button_hover_text' => '#ffffff',
				'label_color'       => '#44403c',
				'link_color'        => '#1d4e4a',
				'link_hover'        => '#163d3a',
			),
			'clean_dark'  => array(
				'label'             => __( 'Clean Dark', 'sanctoria-porta' ),
				'bg_type'           => 'gradient',
				'bg_color'          => '#0f1720',
				'bg_color_2'        => '#1d4e4a',
				'bg_gradient_angle' => 165,
				'form_bg'           => '#18202a',
				'form_border_color' => '#2a3440',
				'form_border_width' => 1,
				'form_radius'       => 12,
				'form_padding'      => 32,
				'form_shadow'       => 'medium',
				'form_width'        => 360,
				'input_bg'          => '#11181f',
				'input_text'        => '#f5f5f4',
				'input_border'      => '#3f4b57',
				'input_focus'       => '#2dd4bf',
				'button_bg'         => '#2dd4bf',
				'button_text'       => '#0f1720',
				'button_hover_bg'   => '#5eead4',
				'button_hover_text' => '#0f1720',
				'label_color'       => '#e7e5e4',
				'link_color'        => '#5eead4',
				'link_hover'        => '#99f6e4',
			),
			'porta_ivory' => array(
				'label'             => __( 'Porta Ivory', 'sanctoria-porta' ),
				'bg_type'           => 'color',
				'bg_color'          => '#FBF8F1',
				'form_bg'           => '#FBF8F1',
				'form_border_color' => '#C9A24A',
				'form_border_width' => 1,
				'form_radius'       => 2,
				'form_padding'      => 32,
				'form_shadow'       => 'none',
				'form_width'        => 360,
				'input_bg'          => '#FFFFFF',
				'input_text'        => '#2B2B2B',
				'input_border'      => '#E3C77A',
				'input_focus'       => '#C9A24A',
				'button_bg'         => '#1B2A4A',
				'button_text'       => '#C9A24A',
				'button_hover_bg'   => '#C9A24A',
				'button_hover_text' => '#1B2A4A',
				'label_color'       => '#2B2B2B',
				'link_color'        => '#7A5C20',
				'link_hover'        => '#1B2A4A',
			),
			'porta_night' => array(
				'label'             => __( 'Porta Night', 'sanctoria-porta' ),
				'bg_type'           => 'color',
				'bg_color'          => '#0F1A30',
				'form_bg'           => '#1B2A4A',
				'form_border_color' => '#C9A24A',
				'form_border_width' => 1,
				'form_radius'       => 2,
				'form_padding'      => 32,
				'form_shadow'       => 'none',
				'form_width'        => 360,
				'input_bg'          => '#0F1A30',
				'input_text'        => '#FBF8F1',
				'input_border'      => '#9C7A2E',
				'input_focus'       => '#E3C77A',
				'button_bg'         => '#C9A24A',
				'button_text'       => '#0F1A30',
				'button_hover_bg'   => '#E3C77A',
				'button_hover_text' => '#0F1A30',
				'label_color'       => '#F5EFE2',
				'link_color'        => '#E3C77A',
				'link_hover'        => '#C9A24A',
			),

		);
	}
	/**
	 * Relative luminance of a #RRGGBB color (0–1), or null if invalid.
	 *
	 * @param string $hex Hex color.
	 * @return float|null
	 */
	public static function relative_luminance( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
			return null;
		}
		$chan = array();
		foreach ( array( 0, 2, 4 ) as $i ) {
			$c = hexdec( substr( $hex, $i, 2 ) ) / 255;
			$chan[] = ( $c <= 0.04045 ) ? ( $c / 12.92 ) : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		}
		return 0.2126 * $chan[0] + 0.7152 * $chan[1] + 0.0722 * $chan[2];
	}

	/**
	 * Whether the login page background should use the dark-background logo.
	 *
	 * @param array|null $s Settings.
	 * @return bool
	 */
	public static function background_is_dark( $s = null ) {
		if ( ! is_array( $s ) ) {
			$s = self::get();
		}
		$type = $s['bg_type'] ?? 'color';
		if ( 'image' === $type && ! empty( $s['bg_image_id'] ) ) {
			return true;
		}
		$l1 = self::relative_luminance( $s['bg_color'] ?? '' );
		if ( null === $l1 ) {
			return false;
		}
		if ( 'gradient' === $type && ! empty( $s['bg_color_2'] ) ) {
			$l2 = self::relative_luminance( $s['bg_color_2'] );
			if ( null !== $l2 ) {
				return ( ( $l1 + $l2 ) / 2 ) < 0.45;
			}
		}
		return $l1 < 0.45;
	}

	/**
	 * Attachment ID of the logo that suits the current background.
	 *
	 * @param array|null $s Settings.
	 * @return int
	 */
	public static function logo_attachment_id( $s = null ) {
		if ( ! is_array( $s ) ) {
			$s = self::get();
		}
		$light = (int) ( $s['logo_id'] ?? 0 );
		$dark  = (int) ( $s['logo_id_dark'] ?? 0 );
		if ( $dark && self::background_is_dark( $s ) ) {
			return $dark;
		}
		return $light;
	}

	/**
	 * Full-size URL of the logo that suits the current background.
	 *
	 * @param array|null $s Settings.
	 * @return string
	 */
	public static function logo_url( $s = null ) {
		$id = self::logo_attachment_id( $s );
		if ( ! $id ) {
			return '';
		}
		$url = wp_get_attachment_image_url( $id, 'full' );
		return $url ? $url : '';
	}

}
