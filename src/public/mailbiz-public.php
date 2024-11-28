<?php

class Mailbiz_Public
{

	private static $hooks_initialized = false;
	private static $count = 0; // TODO: remove
	private static $priority = [
		'default' => 10,
		'low' => 11
	];

	public static function init()
	{
		if (!self::$hooks_initialized) {
			self::init_hooks();
		}
	}

	public static function init_hooks()
	{
		self::$hooks_initialized = true;

		if (get_option('mailbiz_integration_enable') !== 'yes') {
			return;
		}

		add_filter('script_loader_tag', array('Mailbiz_Public', 'filter_set_script_integration_key'), 10, 3);
		add_action('wp_enqueue_scripts', ['Mailbiz_Public', 'register_and_enqueue_integration_hub']);

		if (get_option('mailbiz_journey_enable') !== 'yes') {
			return;
		}

		require_once MAILBIZ_PLUGIN_DIR . '/tracker/mailbiz-tracker.php';

		self::register_tracker();

		add_action('woocommerce_add_to_cart', ['Mailbiz_Public', 'woocommerce_add_to_cart']);
		add_action('wp_login', ['Mailbiz_Public', 'wp_login']);

		add_action('wp_footer', ['Mailbiz_Public', 'account_sync_event']);
		add_action('wp_footer', ['Mailbiz_Public', 'cart_sync_event']);
		add_action('wp_footer', ['Mailbiz_Public', 'product_view_event']);
		add_action('wp_footer', ['Mailbiz_Public', 'order_complete_event']);

		add_action('wp_footer', ['Mailbiz_Public', 'enqueue_tracker'], self::$priority['low']);

		// woocommerce_before_shop_loop

		// Esses eventos aparentemente só ocorrem quano temos um relod dá página.
		// Como eu posso fazer com que essas coisas aconteçam quando temos requests AJAX?
		// woocommerce_add_to_cart // OCORRE AO ADICIONAR NO CARRINHO
		// woocommerce_update_cart_action_cart_updated // ?? nunca ocorreu
		// woocommerce_remove_cart_item // ?? não consegui fazer ocorrer
		// woocommerce_cart_updated // OCORRE 2 VEZES sempre que houver um carrinho
		// woocommerce_new_order // 
		// wp_login // 

		// CART - adicionar no cart
		// CART - remover do cart
		// ACCOUNT - login
		// ACCOUNT - quando já logado
		// ORDER - pedido finalizado
		// PRODUCT - visualização de produto

		// $data->add_to_cart_url -- pode ser util para adicionar produtos no carrinho
		// $data->get_slug -- pode ser útil para pegar a URL do produto (?product=${slug})
		// $data->get_image -- pode ser útil para pegar a URL da imagem do produto
		// $data->get_attributes -- pode ser útil para pegar as properties do produto

		// ?? $data->get_permalink -- pode ser útil para pegar a URL do produto

		// WC()->cart->get_cart_url() -- pegar a URL do carrinho
	}

	public static function filter_set_script_integration_key($tag, $handle, $src)
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

	public static function woocommerce_add_to_cart()
	{
		wp_add_inline_script('mailbiz-tracker', 'console.log("MAILBIZ:woocommerce_add_to_cart")');
	}

	public static function wp_login()
	{
		wp_add_inline_script('mailbiz-tracker', 'console.log("MAILBIZ:wp_login")');
	}
	public static function woocommerce_new_order()
	{
		wp_add_inline_script('mailbiz-tracker', 'console.log("MAILBIZ:woocommerce_new_order")');
	}

	public static function cart_sync_event()
	{
		$cart_sync = Mailbiz_Tracker::get_cart_sync_event();
		if (!$cart_sync) {
			return;
		}

		$cart_sync_json = json_encode($cart_sync, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('cartSync', $cart_sync_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}

	public static function account_sync_event(): void
	{
		$account_sync = Mailbiz_Tracker::get_account_sync_event();
		if (!$account_sync) {
			return;
		}

		$cart_sync_json = json_encode($account_sync, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('accountSync', $cart_sync_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}

	public static function product_view_event(): void
	{
		$product_view = Mailbiz_Tracker::get_product_view_event();
		if (!$product_view) {
			return;
		}

		$product_view_json = json_encode($product_view, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('productView', $product_view_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);

	}

	public static function order_complete_event(): void
	{
		$order_id = $_GET['order-received'];
		if (!$order_id) {
			return;
		}

		$order_complete = Mailbiz_Tracker::get_order_complete_event($order_id);
		if (!$order_complete) {
			return;
		}

		$order_complete_json = json_encode($order_complete, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('orderComplete', $order_complete_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}

	public static function process_order_complete_event($order_id): void
	{
		$order_complete = Mailbiz_Tracker::get_order_complete_event($order_id);
		if (!$order_complete) {
			return;
		}

		$order_complete_json = json_encode($order_complete, JSON_PARTIAL_OUTPUT_ON_ERROR);
		$js_code = "mb_track('orderComplete', $order_complete_json);";

		wp_add_inline_script('mailbiz-tracker', $js_code);
	}
}

// get_option()
// add_options_page()
// update_option()