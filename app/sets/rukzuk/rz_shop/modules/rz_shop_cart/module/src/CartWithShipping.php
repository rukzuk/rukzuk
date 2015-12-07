<?php
namespace rz_shop_cart;

use \Cart\Cart;
use \Cart\CartRestoreException;
use \Cart\Storage\Store;

/**
 * Class CartWithShipping
 *
 * @package rz_shop_cart
 */
class CartWithShipping extends Cart
{

  /**
   * @var float
   */
  private $shippingCosts;
  /**
   * @var float
   */
  private $shippingTax;

  /**
   * @param string $id
   * @param Store  $store
   * @param float  $shippingCosts
   * @param float  $shippingTax
   */
  public function __construct($id, Store $store, $shippingCosts, $shippingTax)
  {
    $this->shippingCosts = $shippingCosts;
    $this->shippingTax = $shippingTax;
    parent::__construct($id, $store);
  }

  /**
   * Full total of the cart including shipping costs
   *
   * @return float
   */
  public function totalWithShipping()
  {
    return $this->total() + $this->shipping();
  }

  /**
   * All tax - with the tax for shipping
   *
   * @return float
   */
  public function taxWithShipping()
  {
    return $this->tax() + $this->shippingTax;
  }

  /**
   * Shipping Costs (including tax)
   *
   * @return float
   */
  public function shipping()
  {
    return $this->shippingCosts;
  }

  /**
   * Restore without exception on error
   */
  public function restoreSilent()
  {
    // try to restore
    try {
      $this->restore();
    } catch (CartRestoreException $e) {
      // ignore
    }
  }
}
