<?php


namespace rz_shop_cart\ShopModeStrategy;

require_once(__DIR__ . '/AbstractPaymentStrategy.php');

use \Rukzuk\Modules\Translator;
use \rz_shop_cart\Payment;


class AfterPaymentStrategy extends AbstractPaymentStrategy
{
  public function process()
  {
    $this->checkout->loadFromStore();
    $redirect = $this->handleAfterPayment();
    $shopModeResponse = new ShopModeResponse(ShopModeResponse::VIEW_MODE_CHECKOUT);
    if (!empty($redirect)) {
      $shopModeResponse->setRedirectUrl($redirect);
    }
    return $shopModeResponse;
  }

  /**
   * @return string
   */
  protected function handleAfterPayment()
  {
    $payment = $this->createPayment();
    $payment->completePurchase();

    if ($payment->isSuccessful()) {
      return $this->getCurrentAbsoluteUrl(array('mode' => 'complete'));
    }
  }
}