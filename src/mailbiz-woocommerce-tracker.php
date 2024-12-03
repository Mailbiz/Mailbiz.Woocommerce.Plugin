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

if (!defined('MAILBIZ_PLUGIN_DIR')) {
	define('MAILBIZ_PLUGIN_DIR', __DIR__);
}

if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
	require_once MAILBIZ_PLUGIN_DIR . '/admin/mailbiz-admin.php';
	add_action('init', ['Mailbiz_Admin', 'init']);
} else {
	require_once MAILBIZ_PLUGIN_DIR . '/recovery/mailbiz-recovery.php';
	add_action('init', ['Mailbiz_Recovery', 'init']);

	require_once MAILBIZ_PLUGIN_DIR . '/public/mailbiz-public.php';
	add_action('init', ['Mailbiz_Public', 'init']);
}
