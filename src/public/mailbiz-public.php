<?php

class Mailbiz_Public
{

	private static $hooks_initialized = false;
	private static $count = 0; // TODO: remove

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

		self::register_tracker();
		add_action('wp_enqueue_scripts', ['Mailbiz_Public', 'enqueue_tracker']);
		add_action('woocommerce_add_to_cart', ['Mailbiz_Public', 'woocommerce_add_to_cart']);
		add_action('wp_login', ['Mailbiz_Public', 'wp_login']);
		add_action('woocommerce_new_order', ['Mailbiz_Public', 'woocommerce_new_order']);

		add_action('woocommerce_cart_updated', ['Mailbiz_Public', 'woocommerce_cart_updated']);

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
		wp_register_script('mailbiz-tracker', plugin_dir_url(__FILE__) . 'scripts/tracker.js', [], false, false);
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
	public static function get_image($data)
	{
		$image_id = $data->get_image_id();
		if (!isset($image_id)) {
			return null;
		}

		$image = wp_get_attachment_image_src($image_id, 'large');
		if (!isset($image[0])) {
			return null;
		}

		return $image[0];
	}

	public static function get_category($product_id)
	{
		$categories = get_the_terms($product_id, 'product_cat');
		if (!is_wp_error($categories) && !isset($categories[0])) {
			return null;
		}
		$category = $categories[0];
		if (!isset($category->name)) {
			return null;
		}
		return $category->name;
	}

	public static function get_brand($product_id)
	{
		$possible_brand_taxonomies = ['product_brand', 'yith_product_brand', 'pa_brand'];
		foreach ($possible_brand_taxonomies as $taxonomy) {
			$brands = get_the_terms($product_id, $taxonomy);
			if (!is_wp_error($brands) && isset($brands[0])) {
				return $brands[0];
			}
		}
		return null;
	}

	public static function woocommerce_cart_updated($arg1)
	{
		if (self::$count === 0) {
			self::$count = 1;
			return;
		}

		$mailbiz_cart = array_map(function ($item) {
			$data = $item['data'];
			return [
				'product_id' => strval($item['product_id']),
				'sku' => $item['product_id'] . "_" . $data->get_id(),
				'name' => $data->get_name(),
				'category' => self::get_category($item['product_id']),
				'brand' => self::get_brand($item['product_id']),
				'price' => floatval($data->get_price()),
				'price_from' => floatval($data->get_regular_price()),
				'quantity' => $item['quantity'],
				'url' => get_permalink($data->get_id()),
				'image_url' => self::get_image($data),
			];
		}, WC()->cart->get_cart());

		wp_add_inline_script('mailbiz-tracker', 'var WOO = ' . json_encode($mailbiz_cart, JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR, 8192) . ';');
	}
}

// get_option()
// add_options_page()
// update_option()