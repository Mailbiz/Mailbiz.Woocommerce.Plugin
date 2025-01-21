<?php

class Mailbiz_Tracker
{
  private static $order_id = null;

  #region [generic]
  public static function get_category($product_id)
  {
    $categories = get_the_terms($product_id, 'product_cat');
    if (is_wp_error($categories) || !isset($categories[0])) {
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

  public static function get_coupons_string($coupons)
  {
    return implode(', ', array_map(function ($item) {
      return $item->get_code();
    }, $coupons));
  }

  private static function get_properties($is_variation, $data)
  {
    if ($is_variation) {
      $properties = $data->get_attributes();
    } else {
      $properties = self::get_product_simple_attributes($data->get_attributes());
    }
    return (object) $properties;
  }
  #endregion

  #region [cart.sync]
  public static function get_cart_items($wc_items)
  {
    $items = [];
    foreach ($wc_items as $item) {
      $data = $item['data'];
      $product_id = strval($item['product_id']);
      $id = strval($data->get_id());
      $is_variation = $product_id !== $id;
      $item_event = self::unset_null_values([
        'product_id' => $product_id,
        'sku' => self::compose_sku($product_id, $id),
        'name' => $data->get_name(),
        'category' => self::get_category($product_id),
        'brand' => self::get_brand($product_id),
        'price' => floatval($data->get_price()),
        'price_from' => floatval($data->get_regular_price()),
        'quantity' => $item['quantity'],
        'url' => $data->get_permalink(),
        'image_url' => self::get_image($data),
        'properties' => self::get_properties($is_variation, $data),
        'recovery_properties' => [
          'variation_id' => $id,
          'url' => wc_get_page_permalink('cart'),
        ]
      ]);

      $items[] = $item_event;
    }
    return $items;
  }

  public static function get_cart_delivery_address($shipping)
  {
    $delivery_address = [
      'postal_code' => $shipping['postcode'] ?: null,
      'address_line1' => $shipping['address_1'] ?: null,
      'address_line2' => $shipping['address_2'] ?: null,
      'city' => $shipping['city'] ?: null,
      'state' => $shipping['state'] ?: null,
      'country' => $shipping['country'] ?: null,
    ];
    $delivery_address = self::unset_null_values($delivery_address);
    if (count($delivery_address) === 0) {
      return null;
    }
    return $delivery_address;
  }

  public static function get_cart_sync_event()
  {
    if (self::$order_id) {
      return;
    }

    $cart = WC()->cart;
    $cart_sync = [
      'cart_id' => Mailbiz_Cart_Id::get_cart_id(),
      'items' => self::get_cart_items($cart->get_cart()),
      'subtotal' => floatval($cart->get_subtotal()),
      'freight' => floatval($cart->get_shipping_total()),
      'discounts' => floatval($cart->get_discount_total()),
      'tax' => floatval($cart->get_taxes_total()),
      'total' => floatval($cart->get_cart_contents_total()),
      'coupons' => self::get_coupons_string($cart->get_coupons()),
      'currency' => get_woocommerce_currency(),
      'delivery_address' => self::get_cart_delivery_address(WC()->customer->get_shipping()),
    ];

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
  public static function get_account_sync_event()
  {
    $customer = WC()->customer;
    $account_sync = [
      'email' => $customer->get_email() ?: $customer->get_billing_email() ?: null,
      'phone' => $customer->get_billing_phone() ?: $customer->get_shipping_phone() ?: null,
      'name' => self::get_name($customer),
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
  public static function get_variants($product_id, $wc_product)
  {
    if ($wc_product instanceof WC_Product_Variable) {
      return array_map(function ($v) use ($product_id) {
        $id = $v->get_id();
        return [
          'sku' => self::compose_sku($product_id, $id),
          'name' => $v->get_name(),
          'price' => floatval($v->get_price()),
          'price_from' => floatval($v->get_regular_price()),
          'image_url' => self::get_image($v),
          'url' => $v->get_permalink(),
          'properties' => self::get_properties(true, $v),
          'recovery_properties' => [
            'variation_id' => $id,
            'url' => wc_get_page_permalink('cart'),
          ],
        ];
      }, wc_get_products([
          'parent' => $product_id,
          'type' => 'variation',
          'limit' => 100,
        ]));
    }
    if ($wc_product instanceof WC_Product_Simple) {
      $id = $wc_product->get_id();
      return [
        [
          'sku' => self::compose_sku($product_id, $id),
          'name' => $wc_product->get_name(),
          'price' => floatval($wc_product->get_price()),
          'price_from' => floatval($wc_product->get_regular_price()),
          'image_url' => self::get_image($wc_product),
          'url' => $wc_product->get_permalink(),
          'properties' => self::get_properties(false, $wc_product),
          'recovery_properties' => [
            'variation_id' => $id,
            'url' => wc_get_page_permalink('cart'),
          ],
        ]
      ];
    }
    return null;
  }

  public static function get_product_view_event()
  {
    $post_id = get_the_ID();
    $wc_product = wc_get_product($post_id);
    if (!$wc_product) {
      return null;
    }

    $queried_object = get_queried_object();
    // This is to check if we are getting a single
    // product and not a list or search.
    if (!$queried_object instanceof WP_Post) {
      return null;
    }

    // A group of products. Isn't a real product, can't
    // ever be added to the cart. Doesn't have variants.
    if ($wc_product instanceof WC_Product_Grouped) {
      return null;
    }

    // External product that can't be sold, doesn't have
    // a price and can't ever be added to the cart.
    if ($wc_product instanceof WC_Product_External) {
      return null;
    }

    $product_id = $post_id;
    $product_view = [
      'product_id' => strval($product_id),
      'url' => $wc_product->get_permalink(),
      'category' => self::get_category($product_id),
      'brand' => self::get_brand($product_id),
      'variants' => self::get_variants($product_id, $wc_product),
    ];
    $product_view = self::unset_null_values($product_view);
    $product_view_event = ['product' => $product_view];
    return $product_view_event;
  }
  #endregion

  #region [order.complete]
  public static function get_order_items($wc_order_items)
  {
    $items = [];
    foreach ($wc_order_items as $order_item) {
      $product_id = strval($order_item->get_product_id());
      $product = $order_item->get_product();
      $id = strval($product->get_id());
      $is_variation = $product_id !== $id;
      $items[] = self::unset_null_values([
        'product_id' => $product_id,
        'category' => self::get_category($product_id),
        'brand' => self::get_brand($product_id),
        'quantity' => $order_item->get_quantity(),
        'sku' => self::compose_sku($product_id, $id),
        'name' => $order_item->get_name(),
        'price' => floatval($product->get_price()),
        'price_from' => floatval($product->get_regular_price()),
        'url' => $product->get_permalink(),
        'image_url' => self::get_image($product),
        'properties' => self::get_properties($is_variation, $product),
      ]);
    }
    return $items;
  }

  public static function get_order_delivery_address($order)
  {
    $delivery_address = [
      'postal_code' => $order->get_shipping_postcode() ?: null,
      'address_line1' => $order->get_shipping_address_1() ?: null,
      'address_line2' => $order->get_shipping_address_2() ?: null,
      'city' => $order->get_shipping_city() ?: null,
      'state' => $order->get_shipping_state() ?: null,
      'country' => $order->get_shipping_country() ?: null,
    ];
    $delivery_address = self::unset_null_values($delivery_address);
    if (count($delivery_address) === 0) {
      return null;
    }
    return $delivery_address;
  }

  public static function get_payment_methods($order)
  {
    $payment_method = [
      'type' => $order->get_payment_method_title() ?: 'Unknown',
      'amount' => floatval($order->get_total()),
    ];
    if (!$payment_method['amount']) {
      return null;
    }
    return [$payment_method];
  }

  public static function get_delivery_methods($shipping_methods)
  {
    $delivery_methods = [];
    foreach ($shipping_methods as $shipping_method) {
      $delivery_methods[] = [
        'type' => $shipping_method->get_name() ?: 'Unknown',
        'amount' => floatval($shipping_method->get_total()),
      ];
    }
    return $delivery_methods;
  }

  public static function set_order_id($order_id)
  {
    self::$order_id = $order_id;
  }
  public static function get_order_complete_event($order_id)
  {
    $order = wc_get_order($order_id);
    $order_complete = [
      'order_id' => $order_id,
      'cart_id' => Mailbiz_Cart_Id::get_cart_id(),
      'subtotal' => floatval($order->get_subtotal()),
      'freight' => floatval($order->get_shipping_total()),
      'tax' => floatval($order->get_total_tax()),
      'discounts' => floatval($order->get_total_discount()),
      'total' => floatval($order->get_total()),
      'coupons' => self::get_coupons_string($order->get_coupons()),
      'currency' => $order->get_currency(),
      'payment_methods' => self::get_payment_methods($order),
      'delivery_methods' => self::get_delivery_methods($order->get_shipping_methods()),
      'delivery_address' => self::get_order_delivery_address($order),
      'items' => self::get_order_items($order->get_items()),
    ];

    Mailbiz_Cart_Id::generate_new_cart_id();

    $order_complete = self::unset_null_values($order_complete);
    $order_complete_event = ['order' => $order_complete];
    return $order_complete_event;
  }
  #endregion

  #region [checkout.step]
  public static function get_checkout_step_event()
  {
    $is_cart = is_cart();
    $is_checkout = is_checkout();
    if (!$is_cart && !$is_checkout && !self::$order_id) {
      return null;
    }

    $checkout_step = [
      'total_steps' => 3,
      'cart_id' => Mailbiz_Cart_Id::get_cart_id()
    ];

    if ($is_cart) {
      $checkout_step['step'] = 1;
      $checkout_step['step_name'] = 'CART';
    }

    if ($is_checkout && !self::$order_id) {
      $checkout_step['step'] = 2;
      $checkout_step['step_name'] = 'CHECKOUT';
    }

    if (self::$order_id) {
      $checkout_step['step'] = 3;
      $checkout_step['step_name'] = 'COMPLETE';
    }

    $checkout_step_event = ['checkout' => $checkout_step];
    return $checkout_step_event;
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