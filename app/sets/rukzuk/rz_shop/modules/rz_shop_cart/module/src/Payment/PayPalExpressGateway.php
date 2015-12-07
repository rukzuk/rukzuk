<?php
namespace rz_shop_cart\Payment;

require_once(__DIR__ . '/AbstractGateway.php');
require_once(__DIR__ . '/PaymentException.php');

use \Render\APIs\APIv1\HeadAPI;
use \Rukzuk\Modules\Translator;
use \Omnipay\Omnipay;

/**
 * Class PayPalExpressGateway
 *
 * @package rz_shop_cart
 */
class PayPalExpressGateway extends AbstractGateway
{
  /**
   * @param array $params
   */
  public function purchase(array $params)
  {
    $this->setSuccessful(false);
    $this->setRedirectUrl();

    $response = $this->sendPurchaseRequest($params);

    if (!isset($response)) {
      throw new PaymentException("No payment response!");
    } elseif (!$response->isRedirect()) {
      throw new PaymentException("Payment error: " . $response->getMessage());
    }

    $this->setRedirectUrl($response->getRedirectUrl());
  }

  /**
   * @param array $params
   */
  public function completePurchase(array $params = array())
  {
    $this->setSuccessful(false);
    $this->setRedirectUrl();

    $response = $this->sendCompletePurchaseRequest($params);

    if (!isset($response)) {
      throw new PaymentException("No payment response!");
    } elseif (!$response->isSuccessful()) {
      throw new PaymentException("Payment error: " . $response->getMessage());
    }

    $this->setSuccessful(true);
  }

  /**
   * @param array $params
   *
   * @return \Omnipay\PayPal\Message\ExpressAuthorizeResponse
   */
  protected function sendPurchaseRequest(array $params)
  {
    $gateway = $this->getOmnipayGateway();
    $defaultParams = $this->createDefaultPayPalParams();
    $request = $gateway->purchase(array_replace($defaultParams, array(
      'returnUrl' => $this->getReturnUrl($params),
      'cancelUrl' => $this->getCancelUrl($params),
      'landingPage' => 'Login',
      'reqConfirmShipping' => 0,
      'allowNote' => 0,
      'noShipping' => 1,
      'addressOverride' => 1,
    )));
    $request->setItems($this->getItemsAsArray());
    return $request->send();
  }

  /**
   * @param array $params
   *
   * @return \Omnipay\PayPal\Message\ExpressCompletePurchaseRequest
   */
  protected function sendCompletePurchaseRequest(array $params)
  {
    $gateway = $this->getOmnipayGateway();
    $defaultParams = $this->createDefaultPayPalParams();
    $request = $gateway->completePurchase($defaultParams);
    $request->setItems($this->getItemsAsArray());
    return $request->send();
  }

  /**
   * @return \Omnipay\PayPal\ExpressGateway
   */
  protected function getOmnipayGateway()
  {
    $settings = $this->getSettings();

    $gateway = Omnipay::create('PayPal_Express');
    $gateway->setUsername($settings->getPayPalExpressUsername());
    $gateway->setPassword($settings->getPayPalExpressPassword());
    $gateway->setSignature($settings->getPayPalExpressSignature());
    $gateway->setTestMode(false);

    return $gateway;
  }

  /**
   * @param array $params
   *
   * @return string
   */
  protected function getReturnUrl(array $params)
  {
    return isset($params['returnUrl']) ? $params['returnUrl'] : '';
  }

  /**
   * @param $params
   *
   * @return string
   */
  protected function getCancelUrl($params)
  {
    return isset($params['cancelUrl']) ? $params['cancelUrl'] : '';
  }

  /**
   * @return array
   */
  protected function createDefaultPayPalParams()
  {
    $settings = $this->getSettings();
    $cart = $this->getCart();
    return array(
      'amount' => $cart->totalWithShipping(),
      'shippingAmount' => $cart->shipping(),
      'currency' => $settings->getShopCurrencyCode(),
      'localeCode' => strtoupper($settings->getLocalCode()),
      'card' => $this->createCardForShippingAddress(),
    );
  }

  /**
   * @return array
   */
  protected function getItemsAsArray()
  {
    $cart = $this->getCart();
    return array_map(function ($item) {
      $name = $item->get('name');
      if ($item->get('variant')) {
        $name .= ' - ' . $item->get('variant');
      }
      return array(
        'name' => $name,
        'quantity' => (int)$item->quantity,
        'price' => (float)$item->getSinglePrice(),
      );
    }, $cart->all());
  }

  /**
   * @return \Omnipay\Common\CreditCard
   */
  protected function createCardForShippingAddress()
  {
    $address = $this->getCheckout()->getShippingAddress();
    return new \Omnipay\Common\CreditCard(array(
      'Name' => $address['name'],
      'Address1' => $address['street'],
      'City' => $address['city'],
      'Postcode' => $address['zip'],
      'State' => '',
      'Country' => $address['country'],
      'Phone' => $address['telephone'],
      'company' => $address['company'],
      'email' => $address['email'],
    ));
  }
}
