<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/Exceptions/CheckoutInvalidException.php');


abstract class AbstractShopModeStrategy
{
  abstract public function process();
}