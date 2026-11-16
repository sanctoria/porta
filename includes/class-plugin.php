<?php
/**
 * Core plugin bootstrap.
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class Porta_Login_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Porta_Login_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the instance.
	 *
	 * @return Porta_Login_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook everything up.
	 */
	private function __construct() {
		Porta_Login_Settings::init();
		Porta_Login_Styles::init();
		Porta_Login_Auth::init();

		if ( is_admin() ) {
			Porta_Login_Admin::init();
		}
	}
}
