<?php

namespace Mailbiz;

class Recovery
{
  private static $initialized = false;
  public static function init()
  {
    if (self::$initialized) {
      return;
    }

    self::$initialized = true;

    if (
      get_option('mailbiz_integration_enable') !== 'yes' ||
      get_option('mailbiz_journey_enable') !== 'yes'
    ) {
      return;
    }

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
      return;
    }

    self::maybe_recover_cart();
  }

  public static function maybe_recover_cart(): void
  {
    $_mb_cr_ = isset($_GET['_mb_cr_']) ? sanitize_text_field(wp_unslash($_GET['_mb_cr_'])) : null;
    $utm_source = isset($_GET['utm_source']) ? sanitize_text_field(wp_unslash($_GET['utm_source'])) : null;

    if (!$_mb_cr_ || !$utm_source) {
      return;
    }

    if ($utm_source !== 'mailbiz') {
      return;
    }

    if (WC()->cart->get_cart_contents_count() > 0) {
      return;
    }

    $recovery = json_decode(base64_decode($_mb_cr_), true);
    if (
      !$recovery ||
      !isset($recovery['c']) ||
      !isset($recovery['t']) ||
      !isset($recovery['u']) ||
      !isset($recovery['its']) ||
      !(count($recovery['its']) > 0)
    ) {
      return;
    }

    foreach ($recovery['its'] as $item) {
      $quantity_str = trim(sanitize_text_field(wp_unslash($item[0])));
      $quantity = is_numeric($quantity_str) ? (int) $quantity_str : null;

      $product_id_str = trim(sanitize_text_field(wp_unslash($item[1])));
      $product_id = is_numeric($product_id_str) ? (int) $product_id_str : null;

      // $sku = $item[2];

      $properties_json = trim(sanitize_text_field(wp_unslash($item[3])));

      $properties = json_decode($properties_json, true);
      $properties_variation_id_str = isset($properties['variation_id']) ? sanitize_text_field(wp_unslash($properties['variation_id'])) : null;

      $valid_variation_id = isset($properties_variation_id_str) && $properties_variation_id_str !== $product_id_str;
      $variation_id_str = $valid_variation_id ? $properties_variation_id_str : 0;

      $variation_id = is_numeric($variation_id_str) ? (int) $variation_id_str : null;

      if ($quantity === null || $product_id === null || $variation_id === null) {
        continue;
      }

      WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
    }
  }
}