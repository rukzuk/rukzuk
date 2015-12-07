<?php
namespace rz_shop_cart\Payment;

use \Rukzuk\Modules\Translator;
use \rz_shop_cart\ShopSettings;
use \rz_shop_cart\CheckoutFrom;
use \rz_shop_cart\CartWithShipping;

/**
 * Abstract gateway class
 *
 * @package rz_shop_cart
 */
abstract class AbstractGateway
{
  /**
   * @var Translator
   */
  private $i18n;
  /**
   * @var ShopSettings
   */
  private $settings;
  /**
   * @var CheckoutFrom
   */
  private $checkout;
  /**
   * @var CartWithShipping
   */
  private $cart;
  /**
   * @var bool
   */
  private $successful = false;
  /**
   * @var string
   */
  private $redirectUrl;

  /**
   * @param Translator       $i18n
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   */
  public function __construct($settings, $checkout, $cart, $i18n)
  {
    $this->i18n = $i18n;
    $this->settings = $settings;
    $this->checkout = $checkout;
    $this->cart = $cart;
  }

  /**
   * @param array $params
   */
  abstract public function purchase(array $params);

  /**
   * @param array $params
   */
  abstract public function completePurchase(array $params = array());

  /**
   * @return bool
   */
  public function isSuccessful()
  {
    return $this->successful;
  }

  /**
   * @param boolean $successful
   */
  protected function setSuccessful($successful)
  {
    $this->successful = (bool)$successful;
  }

  /**
   * @return bool
   */
  public function isRedirect()
  {
    return !empty($this->redirectUrl);
  }

  /**
   * @return string
   */
  public function getRedirectUrl()
  {
    return $this->redirectUrl;
  }

  /**
   * @param string|null $redirectUrl
   */
  protected function setRedirectUrl($redirectUrl = null)
  {
    $this->redirectUrl = $redirectUrl;
  }

  /**
   * @return Translator
   */
  protected function getI18n()
  {
    return $this->i18n;
  }

  /**
   * @return ShopSettings
   */
  protected function getSettings()
  {
    return $this->settings;
  }

  /**
   * @return CheckoutFrom
   */
  protected function getCheckout()
  {
    return $this->checkout;
  }

  /**
   * @return CartWithShipping
   */
  protected function getCart()
  {
    return $this->cart;
  }
}
