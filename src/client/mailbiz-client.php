<?php

namespace Mailbiz;

use Mailbiz\Tracker;
use Mailbiz\Order_Id;

class Client
{

	private static $hooks_initialized = false;
	private static $priority = [
		'default' => 10,
		'low' => 11
	];

	public static function init()
	{
		if (self::$hooks_initialized) {
			return;
		}

		self::$hooks_initialized = true;

		self::init_hooks();
	}

	#region [hooks]
	public static function init_hooks()
	{
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			return;
		}

		if (get_option('mailbiz_integration_enable') !== 'yes') {
			return;
		}

		add_filter('script_loader_tag', ['Mailbiz\\Client', 'filter_set_integration_hub_key'], self::$priority['default'], 3);
		add_action('wp_enqueue_scripts', ['Mailbiz\\Client', 'register_and_enqueue_integration_hub']);

		if (get_option('mailbiz_journey_enable') !== 'yes') {
			return;
		}

		require_once MAILBIZ_PLUGIN_DIR . '/tracker/mailbiz-tracker.php';
		require_once MAILBIZ_PLUGIN_DIR . '/tracker/mailbiz-cart-id.php';
		require_once MAILBIZ_PLUGIN_DIR . '/tracker/mailbiz-order-id.php';

		self::register_tracker();

		add_action('woocommerce_new_order', ['Mailbiz\\Client', 'queue_order_complete_event']);

		add_action('wp_footer', ['Mailbiz\\Client', 'account_sync_event']);
		add_action('wp_footer', ['Mailbiz\\Client', 'cart_sync_event']);
		add_action('wp_footer', ['Mailbiz\\Client', 'product_view_event']);
		add_action('wp_footer', ['Mailbiz\\Client', 'checkout_step_event']);
		add_action('wp_footer', ['Mailbiz\\Client', 'order_complete_event']);

		add_action('wp_footer', ['Mailbiz\\Client', 'enqueue_tracker'], self::$priority['low']);
	}
	#endregion

	#region [scripts]
	public static function filter_set_integration_hub_key($tag, $handle, $src)
	{
		if ($handle === 'mailbiz-integration-hub') {
			$src = esc_url($src);
			$id = 'mailbiz-integration-hub-script';
			$key = get_option('mailbiz_integration_key');
			$tag = "<script type=\"text/javascript\" src=\"$src\" id=\"$id\" data-integration-key=\"$key\"></script>";
		}
		return $tag;
	}
	public static function register_and_enqueue_integration_hub()
	{
		wp_register_script('mailbiz-integration-hub', plugin_dir_url(__FILE__) . 'scripts/integration-hub.js', [], false, false);
		wp_enqueue_script('mailbiz-integration-hub');
	}
	public static function register_tracker()
	{
		wp_register_script('mailbiz-tracker', plugin_dir_url(__FILE__) . 'scripts/mb_track.js', [], false, false);
	}
	public static function enqueue_tracker()
	{
		wp_enqueue_script('mailbiz-tracker');
	}
	#endregion

	#region [cart.sync]
	public static function cart_sync_event()
	{
		if (Order_Id::get()) {
			return;
		}

		$cart_sync = Tracker::get_cart_sync_event();
		if (!$cart_sync) {
			return;
		}

		$cart_sync_json = json_encode($cart_sync, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('cartSync', $cart_sync_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}
	#endregion

	#region [account.sync]
	public static function account_sync_event(): void
	{
		$account_sync = Tracker::get_account_sync_event();
		if (!$account_sync) {
			return;
		}

		$cart_sync_json = json_encode($account_sync, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('accountSync', $cart_sync_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}

	#region [product.view]
	public static function product_view_event(): void
	{
		$product_view = Tracker::get_product_view_event();
		if (!$product_view) {
			return;
		}

		$product_view_json = json_encode($product_view, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('productView', $product_view_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);

	}
	#endregion

	#region [order.complete]
	public static function queue_order_complete_event($order_id): void
	{
		Order_Id::set($order_id);
	}

	public static function order_complete_event(): void
	{
		$order_id = Order_Id::get();
		if (!$order_id) {
			return;
		}

		$order_complete = Tracker::get_order_complete_event($order_id);
		Order_Id::remove();

		if (!$order_complete) {
			return;
		}

		$order_complete_json = json_encode($order_complete, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('orderComplete', $order_complete_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}
	#endregion

	#region [checkout.step]
	public static function checkout_step_event(): void
	{
		$checkout_step = Tracker::get_checkout_step_event();
		if (!$checkout_step) {
			return;
		}

		$checkout_step_json = json_encode($checkout_step, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('checkoutStep', $checkout_step_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}
	#endregion
}