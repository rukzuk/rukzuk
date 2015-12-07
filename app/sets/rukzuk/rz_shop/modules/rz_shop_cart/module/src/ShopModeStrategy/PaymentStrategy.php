<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractPaymentStrategy.php');

use \Rukzuk\Modules\Translator;
use \rz_shop_cart\Payment;
use \rz_shop_cart\ShopModeStrategy\Exceptions\CheckoutInvalidException;


class PaymentStrategy extends AbstractPaymentStrategy
{
  public function process()
  {
    $this->handleCheckout();
    $redirect = $this->handlePayment();
    $shopModeResponse = new ShopModeResponse(ShopModeResponse::VIEW_MODE_CHECKOUT);
    if (!empty($redirect)) {
      $shopModeResponse->setRedirectUrl($redirect);
    }
    return $shopModeResponse;
  }

  protected function handleCheckout()
  {
    $this->checkout->loadFromGlobals();
    $this->checkout->save();
    if (!$this->checkout->isValid()) {
      throw new CheckoutInvalidException();
    }
  }

  /**
   * @return string
   */
  protected function handlePayment()
  {
    $payment = $this->createPayment();
    $payment->purchase(array(
      'returnUrl' => $this->getCurrentAbsoluteUrl(array('mode' => 'afterPayment')),
      'cancelUrl' => $this->getCurrentAbsoluteUrl(array('mode' => 'checkout')),
    ));

    if ($payment->isSuccessful()) {
      return $this->getCurrentAbsoluteUrl(array('mode' => 'complete'));
    } elseif ($payment->isRedirect()) {
      return $payment->getRedirectUrl();
    }
  }
}