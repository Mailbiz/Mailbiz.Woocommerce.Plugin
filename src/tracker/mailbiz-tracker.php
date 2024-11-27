<?php

class Mailbiz_Tracker
{
  #region [generic]
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

  public static function get_image($object_with_get_image_id)
  {
    if (!method_exists($object_with_get_image_id, 'get_image_id')) {
      return null;
    }

    $image_id = $object_with_get_image_id->get_image_id();
    if (!isset($image_id)) {
      return null;
    }

    $image = wp_get_attachment_image_src($image_id, 'large');
    if (!isset($image[0])) {
      return null;
    }

    return $image[0];
  }

  public static function compose_sku($product_id, $id)
  {
    return $product_id . '_' . $id;
  }
  #endregion

  #region [cart.sync]
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
        'sku' => self::compose_sku($item['product_id'], $data->get_id()),
        'name' => $data->get_name(),
        'category' => self::get_category($item['product_id']),
        'brand' => self::get_brand($item['product_id']),
        'price' => floatval($data->get_price()),
        'price_from' => floatval($data->get_regular_price()),
        'quantity' => $item['quantity'],
        'url' => get_permalink($data->get_id()),
        'image_url' => self::get_image($data),
        'properties' => $data->get_attributes(),
        // 'recovery_properties' => [],
      ]);
    }
    return $items;
  }

  public static function get_coupons_string($coupons)
  {
    return implode(', ', array_map(function ($item) {
      return $item->get_code();
    }, $coupons));
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
  }

  public static function get_cart_sync()
  {
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
  #endregion

  #region [account.sync]
  public static function get_name($customer)
  {
    $name = trim(implode(' ', [$customer->get_first_name(), $customer->get_last_name()]));
    $billing_name = trim(implode(' ', [$customer->get_billing_first_name(), $customer->get_billing_last_name()]));
    $shipping_name = trim(implode(' ', [$customer->get_shipping_first_name(), $customer->get_shipping_last_name()]));
    return $name ?: $billing_name ?: $shipping_name ?: null;
  }
  public static function get_account_sync()
  {
    $customer = WC()->customer;
    $account_sync = [
      'email' => $customer->get_email() ?: $customer->get_billing_email() ?: null,
      'phone' => $customer->get_billing_phone() ?: $customer->get_shipping_phone() ?: null,
      'name' => self::get_name($customer),
      'created_at' => $customer->get_date_created(),
    ];
    if (!$account_sync['email']) {
      return null;
    }

    $account_sync = self::unset_null_values($account_sync);
    $account_sync_event = ['user' => $account_sync];
    return $account_sync_event;
  }
  #endregion

  #region [product.view]
  public static function get_product_simple_attributes($attributes)
  {
    return array_map(function ($a) {
      return $a->get_options()[$a->get_position()];
    }, $attributes);
  }
  public static function get_variants($product_id, $product)
  {
    if ($product instanceof WC_Product_Variable) {
      return array_map(function ($v) use ($product_id) {
        return [
          'sku' => self::compose_sku($product_id, $v->get_id()),
          'name' => $v->get_name(),
          'price' => floatval($v->get_price()),
          'price_from' => floatval($v->get_regular_price()),
          'image_url' => self::get_image($v),
          'url' => $v->get_permalink(),
          'properties' => $v->get_attributes(),
          // 'recovery_properties' => [],
        ];
      }, wc_get_products([
          'parent' => $product_id,
          'type' => 'variation',
          'limit' => 100,
        ]));
    }
    if ($product instanceof WC_Product_Simple) {
      return [
        [
          'sku' => self::compose_sku($product_id, $product->get_id()),
          'name' => $product->get_name(),
          'price' => floatval($product->get_price()),
          'price_from' => floatval($product->get_regular_price()),
          'image_url' => self::get_image($product),
          'url' => $product->get_permalink(),
          'properties' => self::get_product_simple_attributes($product->get_attributes()),
          // 'recovery_properties' => [],
        ]
      ];
    }
    return null;
  }

  public static function get_product_view()
  {
    $post_id = get_the_ID();
    $product = wc_get_product($post_id);
    if (!$product) {
      return null;
    }

    $product_id = $post_id;
    $product_view = [
      'product_id' => strval($product_id),
      'url' => $product->get_permalink(),
      'category' => self::get_category($product_id),
      'variants' => self::get_variants($product_id, $product),
    ];
    $product_view = self::unset_null_values($product_view);
    $product_view_event = ['product' => $product_view];
    return $product_view_event;
  }
  #endregion

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