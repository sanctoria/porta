<?php
/**
 * Plugin Name:       Sanctoria Porta – Custom Login Page
 * Plugin URI:        https://wordpress.org/plugins/sanctoria-porta/
 * Description:       Brand the WordPress login, registration, and lost-password screens with your logo, colors, and background. Includes a live preview, optional login-with-email, a show-password toggle, a honeypot, and local login-attempt limiting — no accounts or external services required. Settings: Appearance > Sanctoria Porta.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Sanctoria
 * Author URI:        https://sanctoria.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sanctoria-porta
 * Domain Path:       /languages
 *
 * @package Porta_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PORTA_LOGIN_VERSION', '1.0.1' );
define( 'PORTA_LOGIN_FILE', __FILE__ );
define( 'PORTA_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PORTA_LOGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PORTA_LOGIN_DIR . 'includes/class-settings.php';
require_once PORTA_LOGIN_DIR . 'includes/class-styles.php';
require_once PORTA_LOGIN_DIR . 'includes/class-auth.php';
require_once PORTA_LOGIN_DIR . 'includes/class-admin.php';
require_once PORTA_LOGIN_DIR . 'includes/class-plugin.php';

/**
 * Boot the plugin.
 *
 * @return Porta_Login_Plugin
 */
function porta_login() {
	return Porta_Login_Plugin::instance();
}

porta_login();

