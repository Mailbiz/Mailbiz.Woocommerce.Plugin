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

	public static function get_items($cart_items)
	{
		$items = [];
		foreach ($cart_items as $item) {
			$data = $item['data'];
			$items[] = self::unset_null_values([
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
				'properties' => $data->get_attributes(),
				// 'recovery_properties'
			]);
		}
		return $items;
	}

	public static function get_coupons_string($coupons)
	{
		$coupons_string = '';
		// Avoid implode to improve compatibility with PHP
		foreach ($coupons as $_ => $coupon) {
			$code = $coupon->get_code();
			if ($coupons_string) {
				$coupons_string .= ", $code";
			} else {
				$coupons_string .= $code;
			}
		}
		return $coupons_string;
	}

	public static function get_delivery_address($shipping)
	{
		$delivery_address = [];
		if ($shipping['postcode']) {
			$delivery_address['postal_code'] = $shipping['postcode'];
		}
		if ($shipping['address_1']) {
			$delivery_address['address_line1'] = $shipping['address_1'];
		}
		if ($shipping['address_2']) {
			$delivery_address['address_line2'] = $shipping['address_2'];
		}
		if ($shipping['city']) {
			$delivery_address['city'] = $shipping['city'];
		}
		if ($shipping['state']) {
			$delivery_address['state'] = $shipping['state'];
		}
		if ($shipping['country']) {
			$delivery_address['country'] = $shipping['country'];
		}
		if (count($delivery_address) > 0) {
			return $delivery_address;
		}
		return null;

		// address_number?: string;
	}

	public static function unset_null_values($array)
	{
		foreach ($array as $key => $value) {
			if (is_null($value)) {
				unset($array[$key]);
			}
		}
		return $array;
	}

	public static function woocommerce_cart_updated($arg1)
	{
		if (self::$count === 0) {
			self::$count = 1;
			return;
		}

		$cart_sync = [
			'cart_id' => WC()->cart->get_cart_hash(),
			'items' => self::get_items(WC()->cart->get_cart()),
			'subtotal' => floatval(WC()->cart->get_subtotal()),
			'freight' => floatval(WC()->cart->get_shipping_total()),
			'discounts' => floatval(WC()->cart->get_discount_total()),
			'tax' => floatval(WC()->cart->get_taxes_total()),
			'total' => floatval(WC()->cart->get_cart_contents_total()),
			'coupons' => self::get_coupons_string(WC()->cart->get_coupons()),
			'currency' => get_woocommerce_currency(),
			'delivery_address' => self::get_delivery_address(WC()->customer->get_shipping()),
		];
		$cart_sync_json = json_encode(self::unset_null_values($cart_sync), JSON_PARTIAL_OUTPUT_ON_ERROR);

		wp_add_inline_script('mailbiz-tracker', "mb_track('cartSync', { cart: $cart_sync_json });");
	}
}

// get_option()
// add_options_page()
// update_option()