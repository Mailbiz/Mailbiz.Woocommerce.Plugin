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

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
      return;
    }

    if (
      get_option('mailbiz_integration_enable') !== 'yes' ||
      get_option('mailbiz_journey_enable') !== 'yes'
    ) {
      return;
    }

    self::maybe_recover_cart();
  }

  private static function maybe_recover_cart(): void
  {
    if (!isset($_GET['_mb_cr_']) || !isset($_GET['utm_source'])) {
      return;
    }

    if ($_GET['utm_source'] !== 'mailbiz') {
      return;
    }

    if (WC()->cart->get_cart_contents_count() > 0) {
      return;
    }

    $recovery = json_decode(base64_decode($_GET['_mb_cr_']), true);
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
      list($quantity, $product_id, $sku, $properties_json) = $item;
      $properties = json_decode($properties_json, true);

      $valid_variation_id = isset($properties['variation_id']) && $properties['variation_id'] !== $product_id;
      $variation_id = $valid_variation_id ? $properties['variation_id'] : 0;

      $quantity = (int) $quantity;
      $product_id = (int) $product_id;
      $variation_id = (int) $variation_id;

      WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
    }
  }
}