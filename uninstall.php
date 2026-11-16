<?php
/**
 * Uninstall Porta.
 *
 * @package Porta_Login
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'porta_login_settings' );
delete_option( 'porta_login_lockouts' );
delete_metadata( 'user', 0, 'porta_login_upsell_dismissed', '', true );

if ( is_multisite() ) {
	delete_site_option( 'porta_login_settings' );
}
