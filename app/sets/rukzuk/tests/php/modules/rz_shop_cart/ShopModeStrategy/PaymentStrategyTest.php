<?php
namespace Test\Module\rz_shop_cart;

use Test\Rukzuk\TestCaseException;
use rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/PaymentStrategy.php');

/**
 * @group rz_shop_cart
 */
class PaymentStrategyTest extends AbstractStrategyTestCase
{
  public function test_process()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedRedirectUrl = 'THE-REDIRECT-URL-FROM-payment-'.$uniqueString;

    $settingsMock = $this->getShopSettingsMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock(null, null, array(
      'loadFromGlobals' => array($this->once()),
      'save' => array($this->once()),
      'isValid' => array($this->once(), true),
    ));

    $paymentGatewayMock = $this->getPaymentGatewayMock(array(
      'purchase' => array($this->once()),
      'isSuccessful' => array($this->once(), false),
      'isRedirect' => array($this->once(), true),
      'getRedirectUrl' => array($this->once(), $expectedRedirectUrl),
    ));

    $strategy = $this->getPaymentStrategyMock(array(
      'createPayment' => array($this->once(), $paymentGatewayMock),
      'getCurrentAbsoluteUrl' => array($this->exactly(2), $expectedRedirectUrl)
    ), array($settingsMock, $checkoutMock, $cartMock, $translatorMock));

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCheckout', $expectedRedirectUrl);
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  public function test_process_returnUrlToModeCompleteIfPurchaseIsSuccessful()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedRedirectUrl = 'THE-REDIRECT-URL-FROM-payment-'.$uniqueString;

    $settingsMock = $this->getShopSettingsMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock(null, null, array(
      'loadFromGlobals' => array($this->once()),
      'save' => array($this->once()),
      'isValid' => array($this->once(), true),
    ));

    $paymentGatewayMock = $this->getPaymentGatewayMock(array(
      'purchase' => array($this->once()),
      'isSuccessful' => array($this->once(), true),
      'isRedirect' => array($this->never()),
      'getRedirectUrl' => array($this->never()),
    ));

    $strategy = $this->getPaymentStrategyMock(array(
      'createPayment' => array($this->once(), $paymentGatewayMock),
      'getCurrentAbsoluteUrl' => array($this->exactly(3), $expectedRedirectUrl)
    ), array($settingsMock, $checkoutMock, $cartMock, $translatorMock));

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCheckout', $expectedRedirectUrl);
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  /**
   * @expectedException \rz_shop_cart\ShopModeStrategy\Exceptions\CheckoutInvalidException
   */
  public function test_process_throwExceptionIfCheckoutNotValid()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedRedirectUrl = 'THE-REDIRECT-URL-FROM-payment-'.$uniqueString;

    $settingsMock = $this->getShopSettingsMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock(null, null, array(
      'loadFromGlobals' => array($this->once()),
      'save' => array($this->once()),
      'isValid' => array($this->once(), false),
    ));

    $strategy = $this->getPaymentStrategyMock(array(
      'createPayment' => array($this->never())
    ), array($settingsMock, $checkoutMock, $cartMock, $translatorMock));

    // ACT
    $strategy->process();
  }

  /**
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\rz_shop_cart\ShopModeStrategy\PaymentStrategy
   */
  protected function getPaymentStrategyMock(array $expects = null, $constructorArgs = null)
  {
    $className = '\\rz_shop_cart\\ShopModeStrategy\\PaymentStrategy';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }
}
