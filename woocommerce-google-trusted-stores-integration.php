<?php
/*
 * Plugin Name: WooCommerce Google Trusted Stores Integration
 * Description: Integrates Google Trusted Stores with your WooCommerce store
 * Author: Enollo
 * Author URI: http://www.enollo.com
 * Version: 0.1.0-2
 * Domain Path: /languages/
 */

// Add the integration to WooCommerce
function wc_google_trusted_stores_add_integration( $integrations ) {

	include_once( 'includes/class-wc-google-trusted-stores-integration.php' );
	$integrations[] = 'WC_Google_Trusted_Stores';

	return $integrations;
}
add_filter( 'woocommerce_integrations', 'wc_google_trusted_stores_add_integration', 10 );

// Add internationalization l18n
  load_plugin_textdomain( 'wc_google_trusted_stores', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

 
add_action( 'plugins_loaded', 'load_plugin_textdomain' );