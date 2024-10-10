<?php

class Mailbiz_Admin
{

	const NONCE = 'mailbiz-update-integration-key';

	private static $hooks_initialized = false;

	public static function init()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'set-integration-key') {
			self::set_integration_key();
		}

		if (!self::$hooks_initialized) {
			self::init_hooks();
		}
	}

	public static function init_hooks()
	{
		self::$hooks_initialized = true;

		add_action('admin_menu', ['Mailbiz_Admin', 'admin_menu']); # 
		add_action('admin_enqueue_scripts', ['Mailbiz_Admin', 'load_resources']);
	}

	public static function admin_menu()
	{
		add_options_page('Mailbiz WooCommerce Tracker', 'Mailbiz', 'manage_options', 'mailbiz-woocommerce-tracker', ['Mailbiz_Admin', 'add_options_page']);
	}

	public static function add_options_page()
	{
		$file = __DIR__ . '/views/' . 'config' . '.php';

		include($file);
	}

	public static function load_resources()
	{
		wp_register_style('mailbiz-config-css', __DIR__ . 'assets/config.css', [], '0.0.1');
		wp_enqueue_style('mailbiz-config-css');
	}

	public static function set_integration_key()
	{

		if (!wp_verify_nonce($_POST['_wpnonce'], self::NONCE))
			return false;

		$integration_key = $_POST['integration-key'];

		if (empty($integration_key)) {
			return;
		}
		if (strlen($integration_key) !== 24) {
			return;
		}

		// TODO: alerta de configurações salvas com sucesso
		// TODO: alerta de erro caso haja algum erro

		update_option('mailbiz_integration_key', $integration_key);
	}
}

// get_option()
// add_options_page()
// update_option()