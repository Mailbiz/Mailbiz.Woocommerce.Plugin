<?php

class Mailbiz_Admin
{
	public static function init()
	{
		self::init_hooks();
	}

	public static function init_hooks()
	{
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
}

// get_option()
// add_options_page()