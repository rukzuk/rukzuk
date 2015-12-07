<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractShopModeStrategy.php');

use \rz_shop_cart\CheckoutFrom;


class BackToCartStrategy extends AbstractShopModeStrategy
{
  /**
   * @var CheckoutFrom
   */
  private $checkout;

  /**
   * @param CheckoutFrom $checkout
   */
  public function __construct($checkout)
  {
    $this->checkout = $checkout;
  }

  public function process()
  {
    $this->checkout->loadFromGlobals();
    $this->checkout->save();
    return new ShopModeResponse();
  }
}