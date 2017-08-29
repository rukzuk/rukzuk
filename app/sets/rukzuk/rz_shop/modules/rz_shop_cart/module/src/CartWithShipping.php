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
  public function __construct($id, Store $store, $shippingCosts, $shippingTax, $shippingScalePriceData)
  {
    $this->shippingCosts = $shippingCosts;
    $this->shippingScalePriceData = $shippingScalePriceData;
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
    $vat = (100 / (($this->total() - $this->tax())/$this->tax())) / 100;
    $shippingTax = $this->shipping() - $this->shipping() / (1+$vat);
    return $this->tax() + $shippingTax;
  }

  /**
   * Shipping Costs (including tax)
   *
   * @return float
   */
  public function shipping()
  {
    $shippingCosts = $this->shippingCosts;

    if ($this->shippingScalePriceData['shippingCostScalePrice']) {
      if ($this->total() > $this->shippingScalePriceData['shippingCostScalePrice_from']) {
        $shippingCosts = $this->shippingScalePriceData['shippingCostScalePrice_value'];
      }
    }

    if ($this->shippingScalePriceData['shippingCostFree']) {
      if ($this->total() > $this->shippingScalePriceData['shippingCostFree_value']) {
        $shippingCosts = 0;
      }
    }
    return $shippingCosts;
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
