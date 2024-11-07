<?php

class Mailbiz_Admin
{

	const NONCE = 'mailbiz-update-integration-key';

	private static $hooks_initialized = false;

	public static function init()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'mailbiz-update-admin-config') {
			self::update_admin_config();
		}

		if (!self::$hooks_initialized) {
			self::init_hooks();
		}
	}

	public static function init_hooks()
	{
		self::$hooks_initialized = true;

		add_action('admin_menu', ['Mailbiz_Admin', 'admin_menu']);
		add_action('admin_enqueue_scripts', ['Mailbiz_Admin', 'load_resources']);

	}

	public static function admin_menu()
	{
		add_options_page('Mailbiz WooCommerce Tracker', 'Mailbiz', 'manage_options', 'mailbiz-woocommerce-tracker', ['Mailbiz_Admin', 'load_options_page']);
	}

	public static function load_options_page()
	{
		$file = __DIR__ . '/views/' . 'config' . '.php';

		include($file);
	}

	public static function load_success_notice() {
		$file = __DIR__ . '/views/' . 'notice-success' . '.php';

		include($file);
	}

	public static function load_error_notice() {
		$file = __DIR__ . '/views/' . 'notice-error' . '.php';

		include($file);
	}

	public static function load_resources()
	{
		wp_register_style('mailbiz-config-css', __DIR__ . 'assets/config.css', [], '0.0.1');
		wp_enqueue_style('mailbiz-config-css');
	}

	public static function update_admin_config()
	{
		if (!wp_verify_nonce($_POST['_wpnonce'], self::NONCE)) {
			return;
		}

		$get_boolean_arg = function ($option) {
			return isset($_POST[$option]) && $_POST[$option] == 'on' ? 'yes' : 'no';
		};

		$integration_enable = $get_boolean_arg('integration-enable');
		$integration_key = $_POST['integration-key'];
		$journey_enable = $get_boolean_arg('journey-enable');

		$is_integration_key_valid = (function ($integration_key) {
			if (empty($integration_key)) {
				return false;
			}
			if (strlen($integration_key) !== 24) {
				return false;
			}
			return true;
		})($integration_key);

		if (!$is_integration_key_valid) {
			$integration_enable = 'no';
			add_action('admin_notices', ['Mailbiz_Admin', 'load_error_notice']);
		} else {
			add_action('admin_notices', ['Mailbiz_Admin', 'load_success_notice']);
		}

		update_option('mailbiz_integration_key', $integration_key);
		update_option('mailbiz_integration_enable', $integration_enable);
		update_option('mailbiz_journey_enable', $journey_enable);
	}
}

// get_option()
// add_options_page()
// update_option()