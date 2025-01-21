<?php

class Mailbiz_Cart_Id
{

  private static $session_key = '_mbz_cart_id';
  private static $cart_id = null;
  public static function get_cart_id()
  {
    if (!self::_get()) {
      self::generate_new_cart_id();
    }

    return self::_get();
  }

  private static function _get()
  {
    if (!self::$cart_id) {
      self::$cart_id = WC()->session->get(self::$session_key, null);
    }
    return self::$cart_id;
  }

  public static function generate_new_cart_id()
  {
    self::$cart_id = self::generate_uuid_v4();
    WC()->session->set(self::$session_key, self::$cart_id);
  }

  // https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
  // And use WordPress included class (since 2.5.0) the way that WC uses it.
  private static function generate_uuid_v4()
  {
    try {
      require_once ABSPATH . 'wp-includes/class-phpass.php';
      $hasher = new PasswordHash(8, false);
      $data = $hasher->get_random_bytes(16);

      $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    } catch (e) {
      return 'ID-GENERATION-FAILED';
    }
  }
}