<?php

/**
 * @package Mailbiz WooCommerce Tracker
 * @version 0.0.1
 * Plugin Name: Mailbiz WooCommerce Tracker
 * Description: Handles configuration and insertion of scripts for the Mailbiz Tracker in WooCommerce applications.
 * Author: Mailbiz
 * Author URI: https://mailbiz.com.br
 * Text Domain: mailbiz-woocommerce-tracker
 * Requires Plugins: woocommerce
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Check for WooCommerce
$activePlugins = get_option('active_plugins');
$wooCommercePlugin = 'woocommerce/woocommerce.php';
$isWooCommerceAcPluginActive = in_array($wooCommercePlugin, $activePlugins);
if (!$isWooCommerceAcPluginActive) {
  return;
  // Print some warning in the admin dashboard?
}

// Check if configuration is set

// Render configuration page


// plugin API https://developer.wordpress.org/reference/
// register_activation_hook()
// register_deactivation_hook()
// register_uninstall_hook()
// ---
// do_action()
// remove_action()

// save data https://developer.wordpress.org/apis/options/
// make requests https://developer.wordpress.org/plugins/http-api/
