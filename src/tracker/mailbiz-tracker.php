<?php

class Mailbiz_Tracker
{
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

  public static function get_cart_sync() {
    $cart = WC()->cart;
    $cart_sync = [
			'cart_id' => $cart->get_cart_hash(),
			'items' => self::get_items($cart->get_cart()),
			'subtotal' => floatval($cart->get_subtotal()),
			'freight' => floatval($cart->get_shipping_total()),
			'discounts' => floatval($cart->get_discount_total()),
			'tax' => floatval($cart->get_taxes_total()),
			'total' => floatval($cart->get_cart_contents_total()),
			'coupons' => self::get_coupons_string($cart->get_coupons()),
			'currency' => get_woocommerce_currency(),
			'delivery_address' => self::get_delivery_address(WC()->customer->get_shipping()),
		];
    if ($cart_sync['cart_id'] === '') {
      return null;
    }

		$cart_sync = self::unset_null_values($cart_sync);
    $cart_sync_event = ['cart' => $cart_sync];
    return $cart_sync_event;
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
}