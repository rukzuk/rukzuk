<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractShopModeStrategy.php');
require_once(__DIR__ . '/../Payment.php');

use \Rukzuk\Modules\Translator;
use \rz_shop_cart\Payment;
use \rz_shop_cart\Payment\AbstractGateway;
use \rz_shop_cart\ShopSettings;
use \rz_shop_cart\CheckoutFrom;
use \rz_shop_cart\CartWithShipping;


abstract class AbstractPaymentStrategy extends AbstractShopModeStrategy
{
  /**
   * @var ShopSettings
   */
  protected $settings;
  /**
   * @var CheckoutFrom
   */
  protected $checkout;
  /**
   * @var CartWithShipping
   */
  protected $cart;
  /**
   * @var Translator
   */
  protected $translator;

  /**
   * @param ShopSettings     $settings
   * @param CheckoutFrom     $checkout
   * @param CartWithShipping $cart
   * @param Translator       $translator
   */
  public function __construct($settings, $checkout, $cart, $translator)
  {
    $this->settings = $settings;
    $this->checkout = $checkout;
    $this->cart = $cart;
    $this->translator = $translator;
  }

  /**
   * @return AbstractGateway
   */
  protected function createPayment()
  {
    $gateway = $this->checkout->getPaymentMethod();
    return Payment::create($gateway, $this->settings, $this->checkout, $this->cart, $this->translator);
  }

  /**
   * @param array $params
   *
   * @return string
   */
  protected function getCurrentAbsoluteUrl(array $params)
  {
    return $this->settings->getCurrentAbsoluteUrl($params);
  }
}