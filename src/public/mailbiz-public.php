<?php

class Mailbiz_Public
{

	private static $hooks_initialized = false;

	public static function init()
	{
		if (!self::$hooks_initialized) {
			self::init_hooks();
		}
	}

	public static function init_hooks()
	{
		self::$hooks_initialized = true;

		add_action('wp_enqueue_scripts', ['Mailbiz_Public', 'load_resources']);
	}

	public static function load_resources()
	{
		wp_register_script('mailbiz-integration-hub', plugin_dir_url(__FILE__) . 'scripts/integration-hub.js', [], false, false);
		wp_enqueue_script('mailbiz-integration-hub');
	}
}

// get_option()
// add_options_page()
// update_option()