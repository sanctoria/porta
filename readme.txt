=== Sanctoria Porta – Custom Login Page ===
Contributors: sanctoria
Tags: login, custom login, branding, white label, login page
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Brand the WordPress login screen with your logo, colors, and background. Live preview. Settings: Appearance > Sanctoria Porta.

== Description ==

Sanctoria Porta lets you turn the default WordPress login, registration, and lost-password screens into a branded entrance for your site — without writing code or connecting to an external service.

The free plugin is a complete visual customizer. It is useful on its own for a single site or a client hand-off.

= Design =

* Custom logo, size, link, and hover title
* Background color, gradient, or image from the Media Library
* Form colors, border, radius, padding, width, and shadow
* Input and button colors, including hover and focus states
* Label and link colors
* Custom CSS, applied only on the login screens
* Two optional starter palettes (Clean Light and Clean Dark)
* A live preview on the settings page, plus a link to the real login screen

= Sign-in options =

* Allow sign-in with username, email, or both
* WordPress’s built-in “show password” eye toggle (optional; can be turned off)
* Optionally tick “Remember me” by default
* A hidden honeypot field that stops many simple bots — no third-party captcha or API
* Local, IP-based limit on failed sign-in attempts, with a wp-config.php emergency bypass: `define( 'PORTA_LOGIN_DISABLE_LOCKOUT', true );`

= Messages =

* Optional single custom error message
* Hide the “Go to site” link or the language switcher

Nothing in Sanctoria Porta calls home. Images come from your Media Library. Styles are generated on your site.

A separate Pro add-on (sold independently, not required) adds a template library, passkey sign-in, a custom login URL with a safe recovery path, role-based redirects, and agency white-label tools. Paid licenses are a one-time purchase: Porta Pro ($20, one site, three years of updates), Porta Pro Trinity ($50, three sites, lifetime updates), or Porta Pro Infinity ($99, unlimited sites, lifetime updates). The free plugin does not lock any of its own features behind a license.

== Installation ==

1. Upload the `sanctoria-porta` folder to the `/wp-content/plugins/` directory, or install the plugin through the Plugins screen.
2. Activate Sanctoria Porta through the Plugins screen.
3. Go to Appearance > Sanctoria Porta to choose a starter palette or set your logo and colors.
4. Save changes, then open the login page to confirm the result.

== Frequently Asked Questions ==

= Does this hide or change my login URL? =

No. The free plugin only styles the existing login screens and adds optional sign-in behavior. It does not change `wp-login.php`. A custom login URL is part of the optional Pro add-on, and that add-on includes a wp-config.php recovery constant so you cannot be locked out.

= What if the attempt limiter locks me out? =

Add `define( 'PORTA_LOGIN_DISABLE_LOCKOUT', true );` to `wp-config.php`, save the file, and sign in. You can then raise the limit or turn the limiter off.

= Does this load fonts or scripts from other websites? =

No. The free plugin does not load Google Fonts, analytics, or any other remote asset on your login page.

= Will this work with my page builder? =

Sanctoria Porta styles `wp-login.php` (and the registration and lost-password forms on that screen). It does not need a page builder.


= Is there a Pro version? =

Yes. The optional add-on is sold separately as a one-time purchase: Porta Pro ($20, one site, three years of updates), Porta Pro Trinity ($50, three sites, lifetime updates), or Porta Pro Infinity ($99, unlimited sites, lifetime updates). The free Sanctoria Porta plugin stays complete and does not require a license key.

= How do I remove all traces of the plugin? =

Deactivate and delete the plugin. Uninstall removes its settings. Media Library images you uploaded are left in the library.

== Screenshots ==

1. Settings page with the live preview beside the design controls.
2. A branded login page using the Porta Ivory starter (Sanctoria navy, gold, and ivory).
3. The same site using the Porta Night starter.

== Changelog ==

= 1.0.1 =
* Renamed the plugin to Sanctoria Porta (slug `sanctoria-porta`) so the WordPress.org listing is clearly distinguished from other products named Porta. Settings, option names and the Appearance menu slug are unchanged, so an existing install keeps its settings after you activate the new folder.
* The settings screen and plugin list now use the Sanctoria Porta name. Starter palettes (Porta Ivory, Porta Night) and paid add-on plan names are unchanged.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Renamed to Sanctoria Porta (slug sanctoria-porta) after a WordPress.org review note. Activate the new folder; your settings are kept.

= 1.0.0 =
Initial release of Sanctoria Porta.
