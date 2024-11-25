<?php

/**
 * Plugin Name: Mailbiz WooCommerce Tracker
 * Description: Handles configuration and insertion of scripts for the Mailbiz Tracker in WooCommerce applications.
 * Version: 0.0.1
 * Author: Mailbiz
 * Author URI: https://mailbiz.com.br
 * Text Domain: mailbiz-woocommerce-tracker
 * Requires Plugins: woocommerce
 * 
 * @package Mailbiz WooCommerce Tracker
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

// // Check for WooCommerce
// $activePlugins = get_option('active_plugins');
// $wooCommercePlugin = 'woocommerce/woocommerce.php';
// $isWooCommerceAcPluginActive = in_array($wooCommercePlugin, $activePlugins);
// if (!$isWooCommerceAcPluginActive) {
//   return;
//   // Print some warning in the admin dashboard?
// }

if (!defined('MAILBIZ_PLUGIN_DIR')) {
	define('MAILBIZ_PLUGIN_DIR', __DIR__);
}

if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
	require_once MAILBIZ_PLUGIN_DIR . '/admin/mailbiz-admin.php';
	add_action('init', ['Mailbiz_Admin', 'init']);
} else {
	require_once MAILBIZ_PLUGIN_DIR . '/public/mailbiz-public.php';
	add_action('init', ['Mailbiz_Public', 'init']);
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
