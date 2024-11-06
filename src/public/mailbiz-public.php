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

		add_filter('script_loader_tag', array('Mailbiz_Public', 'set_script_integration_key'), 10, 3);

		if (get_option('mailbiz_integration_enable') == 'true') {
			add_action('wp_enqueue_scripts', ['Mailbiz_Public', 'load_integration_hub']);
		}
	}

	public static function set_script_integration_key($tag, $handle, $src)
	{
		if ($handle === 'mailbiz-integration-hub') {
			$src = esc_url($src);
			$id = 'mailbiz-integration-hub-script';
			$key = get_option('mailbiz_integration_key');
			$tag = '<script type="text/javascript" src="' . $src . '" id="' . $id . '" data-integration-key="' . $key . '"></script>';
		}
		return $tag;
	}
	public static function load_integration_hub()
	{
		wp_register_script('mailbiz-integration-hub', plugin_dir_url(__FILE__) . 'scripts/integration-hub.js', [], false, false);
		wp_enqueue_script('mailbiz-integration-hub');
	}
}

// get_option()
// add_options_page()
// update_option()