<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractShopModeStrategy.php');


class CheckoutStrategy extends AbstractShopModeStrategy
{
  public function process()
  {
    return new ShopModeResponse(ShopModeResponse::VIEW_MODE_CHECKOUT);
  }
}