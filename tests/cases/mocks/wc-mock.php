<?php

namespace MailbizTest;

class WcMock
{
    public $cart;
    public $customer;

    public function __construct($data = [])
    {
        $this->cart = new WcCartMock($data['cart']);
        $this->customer = new WcCustomerMock($data['customer']);
    }
    public function _setData($data)
    {
        $this->_setCartData($data['cart']);
        $this->_setCustomerData($data['customer']);
    }
    public function _getData() {
        return [
            'cart' => $this->cart->_getData(),
            'customer' => $this->customer->_getData()
        ];
    }

    public function _setCartData($data)
    {
        $this->cart->_setData($data);
    }

    public function _setCustomerData($data)
    {
        $this->customer->_setData($data);
    }
}

class WcCartMock
{
    public $_data = [];
    public function __construct($data)
    {
        $this->_setData($data);
    }
    public function _setData($data)
    {
        $this->_data = $data ?: $this->_data ?: [];
        $this->_processItemData();
    }
    public function _getData()
    {
        return $this->_data;
    }
    public function _processItemData()
    {
        foreach ($this->_data['cart'] as $key => $item) {
            $this->_data['cart'][$key]['data'] = new WcItemDataMock($item['data']);
        }
    }
    public function get_cart()
    {
        return $this->_data['cart'];
    }
    public function get_subtotal()
    {
        return $this->_data['subtotal'];
    }
    public function get_shipping_total()
    {
        return $this->_data['shipping_total'];
    }
    public function get_discount_total()
    {
        return $this->_data['discount_total'];
    }
    public function get_taxes_total()
    {
        return $this->_data['taxes_total'];
    }
    public function get_cart_contents_total()
    {
        return $this->_data['cart_contents_total'];
    }
    public function get_coupons()
    {
        return $this->_data['coupons'];
    }
}

class WcCustomerMock
{
    public $_data = [];
    public function __construct($data)
    {
        $this->_setData($data);
    }
    public function _setData($data)
    {
        $this->_data = $data ?: $this->_data ?: [];
    }
    public function _getData()
    {
        return $this->_data;
    }
    public function get_shipping()
    {
        return array_merge(
            [
                'first_name' => '',
                'last_name' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'postcode' => '',
                'country' => '',
                'state' => '',
                'phone' => ''
            ],
            $this->_data['shipping']
        );
    }
}

class WcItemDataMock
{
    public $_data = [];
    public function __construct($data)
    {
        $this->_setData($data);
    }
    public function _setData($data)
    {
        $this->_data = $data ?: $this->_data ?: [];
    }
    public function _getData()
    {
        return $this->_data;
    }
    private function _getProperty($key) {
        return $this->_data[$key] ?: $this->_data['data'][$key];
    }
    public function get_id()
    {
        return $this->_getProperty('id');
    }
    public function get_name()
    {
        return $this->_getProperty('name');
    }
    public function get_price()
    {
        return $this->_getProperty('price');
    }
    public function get_regular_price()
    {
        return $this->_getProperty('regular_price');
    }
    public function get_permalink()
    {
        return 'https://store.com.br?product=' . $this->_getProperty('slug');
    }
    
}

class WcItemDataAttributeMock {
    public $_data = [];
    public function __construct($data)
    {
        $this->_setData($data);
    }
    public function _setData($data)
    {
        $this->_data = $data ?: $this->_data ?: [];
    }
    public function _getData()
    {
        return $this->_data;
    }
    public function get_options()
    {
        return $this->_data['options'];
    }
    public function get_position()
    {
        return $this->_data['position'];
    }
}