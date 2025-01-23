<?php

namespace Mailbiz;

class Order_Id
{

  private static $session_key = '_mbz_order_id';
  private static $order_id = null;
  private static $is_session_updated = false;
  public static function get()
  {
    if (!self::$is_session_updated) {
      self::$order_id = WC()->session->get(self::$session_key, null);
      self::$is_session_updated = true;
    }
    return self::$order_id;
  }

  public static function set($order_id)
  {
    self::$order_id = $order_id;
    WC()->session->set(self::$session_key, self::$order_id);
  }

  public static function remove()
  {
    self::$order_id = null;
    WC()->session->set(self::$session_key, self::$order_id);
  }
}