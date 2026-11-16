<?php
/**
 * Login behaviour: identifier rules, honeypot, lockout, remember-me default.
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication helpers for the free plugin.
 */
class Porta_Login_Auth {

	/**
	 * Hook authentication behaviour.
	 */
	public static function init() {
		add_filter( 'authenticate', array( __CLASS__, 'honeypot_check' ), 5, 3 );
		add_filter( 'authenticate', array( __CLASS__, 'lockout_check' ), 20, 3 );
		add_filter( 'authenticate', array( __CLASS__, 'identifier_check' ), 25, 3 );
		add_action( 'wp_login_failed', array( __CLASS__, 'record_failure' ), 10, 2 );
		add_action( 'wp_login', array( __CLASS__, 'clear_failures' ), 10, 2 );
		add_action( 'login_form', array( __CLASS__, 'print_honeypot' ) );
		add_action( 'register_form', array( __CLASS__, 'print_honeypot' ) );
		add_action( 'lostpassword_form', array( __CLASS__, 'print_honeypot' ) );
	}

	/**
	 * Whether the emergency lockout bypass is set.
	 *
	 * @return bool
	 */
	public static function lockout_disabled() {
		return defined( 'PORTA_LOGIN_DISABLE_LOCKOUT' ) && PORTA_LOGIN_DISABLE_LOCKOUT;
	}

	/**
	 * Client IP, sanitized.
	 *
	 * @return string
	 */
	public static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip = sanitize_text_field( $ip );
		if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return '0.0.0.0';
		}
		return $ip;
	}

	/**
	 * Transient key for an IP.
	 *
	 * @param string $ip IP.
	 * @return string
	 */
	private static function fail_key( $ip ) {
		return 'porta_login_fail_' . md5( $ip );
	}

	/**
	 * Reject filled honeypot fields.
	 *
	 * @param WP_User|WP_Error|null $user     User.
	 * @param string                $username Username.
	 * @param string                $password Password.
	 * @return WP_User|WP_Error|null
	 */
	public static function honeypot_check( $user, $username, $password ) {
		unset( $username, $password );
		if ( ! Porta_Login_Settings::get_value( 'honeypot', 1 ) ) {
			return $user;
		}
		if ( empty( $_POST['porta_login_hp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $user;
		}
		return new WP_Error( 'porta_login_honeypot', __( 'We could not sign you in. Check your details and try again.', 'porta' ) );
	}

	/**
	 * Block a locked-out IP.
	 *
	 * @param WP_User|WP_Error|null $user     User.
	 * @param string                $username Username.
	 * @param string                $password Password.
	 * @return WP_User|WP_Error|null
	 */
	public static function lockout_check( $user, $username, $password ) {
		unset( $username, $password );
		if ( self::lockout_disabled() || ! Porta_Login_Settings::get_value( 'lockout_enabled', 1 ) ) {
			return $user;
		}

		/**
		 * Let Pro whitelist skip the lockout.
		 *
		 * @param bool $skip Whether to skip.
		 * @param string $ip IP address.
		 */
		$ip = self::client_ip();
		if ( apply_filters( 'porta_login_skip_lockout', false, $ip ) ) {
			return $user;
		}

		$data = get_transient( self::fail_key( $ip ) );
		if ( ! is_array( $data ) || empty( $data['locked_until'] ) ) {
			return $user;
		}
		if ( time() < (int) $data['locked_until'] ) {
			$mins = max( 1, (int) ceil( ( $data['locked_until'] - time() ) / 60 ) );
			return new WP_Error(
				'porta_login_locked',
				sprintf(
					/* translators: %d: minutes remaining. */
					_n( 'Too many failed sign-in attempts. Try again in %d minute.', 'Too many failed sign-in attempts. Try again in %d minutes.', $mins, 'porta' ),
					$mins
				)
			);
		}
		return $user;
	}

	/**
	 * Enforce username-only or email-only if selected.
	 *
	 * @param WP_User|WP_Error|null $user     User.
	 * @param string                $username Identifier.
	 * @param string                $password Password.
	 * @return WP_User|WP_Error|null
	 */
	public static function identifier_check( $user, $username, $password ) {
		unset( $password );
		if ( $user instanceof WP_Error && $user->has_errors() ) {
			return $user;
		}
		$mode = Porta_Login_Settings::get_value( 'login_with', 'username_or_email' );
		if ( 'username_or_email' === $mode || '' === $username ) {
			return $user;
		}
		$looks_email = is_email( $username );
		if ( 'email' === $mode && ! $looks_email ) {
			return new WP_Error( 'porta_login_email_only', __( 'Please sign in with your email address.', 'porta' ) );
		}
		if ( 'username' === $mode && $looks_email ) {
			return new WP_Error( 'porta_login_username_only', __( 'Please sign in with your username, not your email address.', 'porta' ) );
		}
		return $user;
	}

	/**
	 * Record a failed login and maybe lock the IP.
	 *
	 * @param string        $username Username.
	 * @param WP_Error|null $error    Error.
	 */
	public static function record_failure( $username, $error = null ) {
		unset( $username, $error );
		if ( self::lockout_disabled() || ! Porta_Login_Settings::get_value( 'lockout_enabled', 1 ) ) {
			return;
		}
		$ip = self::client_ip();
		if ( apply_filters( 'porta_login_skip_lockout', false, $ip ) ) {
			return;
		}

		$key      = self::fail_key( $ip );
		$limit    = (int) Porta_Login_Settings::get_value( 'lockout_limit', 5 );
		$minutes  = (int) Porta_Login_Settings::get_value( 'lockout_minutes', 15 );
		$data     = get_transient( $key );
		if ( ! is_array( $data ) ) {
			$data = array( 'count' => 0, 'locked_until' => 0 );
		}
		$data['count'] = (int) $data['count'] + 1;
		if ( $data['count'] >= $limit ) {
			$data['locked_until'] = time() + ( $minutes * MINUTE_IN_SECONDS );
			/**
			 * Fires when an IP is locked out.
			 *
			 * @param string $ip       IP.
			 * @param array  $data     Lock data.
			 * @param int    $minutes  Duration.
			 */
			do_action( 'porta_login_locked_out', $ip, $data, $minutes );
		}
		set_transient( $key, $data, max( HOUR_IN_SECONDS, $minutes * MINUTE_IN_SECONDS ) );
	}

	/**
	 * Clear failures after a successful login.
	 *
	 * @param string  $user_login Login.
	 * @param WP_User $user       User.
	 */
	public static function clear_failures( $user_login, $user ) {
		unset( $user_login, $user );
		delete_transient( self::fail_key( self::client_ip() ) );
	}

	/**
	 * Print a hidden honeypot field. JavaScript leaves it empty; bots often fill it.
	 */
	public static function print_honeypot() {
		if ( ! Porta_Login_Settings::get_value( 'honeypot', 1 ) ) {
			return;
		}
		echo '<p class="porta-login-hp" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">';
		echo '<label for="porta_login_hp">' . esc_html__( 'Leave this field empty', 'porta' ) . '</label>';
		echo '<input type="text" name="porta_login_hp" id="porta_login_hp" value="" tabindex="-1" autocomplete="off" />';
		echo '</p>';
	}
}
