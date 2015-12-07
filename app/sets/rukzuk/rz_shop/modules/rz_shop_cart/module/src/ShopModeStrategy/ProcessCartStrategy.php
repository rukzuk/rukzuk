<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractShopModeStrategy.php');

use \rz_shop_cart\CartProcessor;
use \rz_shop_cart\CartWithShipping;


class ProcessCartStrategy extends AbstractShopModeStrategy
{
  /**
   * @var CartProcessor
   */
  private $cartProcessor;
  /**
   * @var CartWithShipping
   */
  private $cart;

  /**
   * @param CartProcessor    $cartProcessor
   * @param CartWithShipping $cart
   */
  public function __construct($cartProcessor, $cart)
  {
    $this->cartProcessor = $cartProcessor;
    $this->cart = $cart;
  }

  public function process()
  {
    // update cart with post data
    $this->cartProcessor->handlePOST($this->cart);
    $this->cart->save();
    return new ShopModeResponse();
  }
}