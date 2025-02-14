<?php

namespace Mailbiz;

/**
 * Plugin Name: Mailbiz Tracker for WooCommerce
 * Description: Handles configuration and insertion of the Mailbiz Tracker in WooCommerce applications.
 * Version: 1.0.3
 * Author: Mailbiz
 * Author URI: https://mailbiz.com.br
 * Text Domain: mailbiz-tracker-for-woocommerce
 * Requires Plugins: woocommerce
 * Requires at least: 4.1
 * Requires PHP: 5.3
 * License: GPLv2 or later
 * 
 * @package Mailbiz Tracker for WooCommerce
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

if (defined('WP_CLI') && WP_CLI) {
	return;
}

if (defined('MAILBIZ_PLUGIN_LOADED')) {
	return;
}
define('MAILBIZ_PLUGIN_LOADED', true);

if (!defined('MAILBIZ_PLUGIN_DIR')) {
	define('MAILBIZ_PLUGIN_DIR', __DIR__);
}

if (!defined('MAILBIZ_PLUGIN_SLUG')) {
	$dir_exploded = explode('/', MAILBIZ_PLUGIN_DIR) ;
	define('MAILBIZ_PLUGIN_SLUG', end($dir_exploded) ?: 'mailbiz-tracker-for-woocommerce');
}

if (!defined('MAILBIZ_PLUGIN_URL')) {
	define('MAILBIZ_PLUGIN_URL', plugins_url('/' . MAILBIZ_PLUGIN_SLUG));
}

if (is_admin()) {
	require_once MAILBIZ_PLUGIN_DIR . '/admin/mailbiz-admin.php';
	add_action('init', ['Mailbiz\\Admin', 'init']);
} else {
	require_once MAILBIZ_PLUGIN_DIR . '/recovery/mailbiz-recovery.php';
	add_action('init', ['Mailbiz\\Recovery', 'init']);

	require_once MAILBIZ_PLUGIN_DIR . '/client/mailbiz-client.php';
	add_action('init', ['Mailbiz\\Client', 'init']);
}
