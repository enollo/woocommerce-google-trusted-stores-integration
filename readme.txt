=== WooCommerce Google Trusted Stores Integration ===
Contributors: tamarazuk, enollo, s-a-s-k-i-a
Tags: woocommerce, google trusted stores
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 0.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provides integration between Google Trusted Stores and WooCommerce.

== Description ==

This plugin provides the integration between [Google Trusted Stores](https://www.google.com/trustedstores) and the WooCommerce plugin.
A Google Trusted Stores account is required.

== Installation ==

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation’s wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

Or use the automatic installation wizard through your admin panel, just search for this plugins name.

== Frequently Asked Questions ==

= Where can I find the setting for this plugin? =

This plugin will add the settings to the Integration tab, to be found in the WooCommerce > Settings menu.

== Changelog ==

= 0.1.1 - 2014.12.01 =
 * Add - german .po and .mo files

= 0.1.0-2 - 2014.nn.nn =
 * Fix - Add the estimated delivery date
 * Tweak - Allow use of non-US Google Trusted Stores JavaScript

= 0.1.0 - 2014.10.05 =
 * Tweak - general cleanup
 * Fix - Use `$order->get_order_number() for `gts-o-id` - props to @WOWstyleshop
 * Localization - localized and added filter on languages

= 0.0.1 - 2014.05.08 =
 * Initial release
