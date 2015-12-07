<?php
namespace Test\Module\rz_shop_cart;

use Test\Rukzuk\TestCaseException;
use rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/AfterPaymentStrategy.php');

/**
 * @group rz_shop_cart
 */
class AfterPaymentStrategyTest extends AbstractStrategyTestCase
{
  public function test_process()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedRedirectUrl = 'THE-REDIRECT-URL-FROM-payment-'.$uniqueString;

    $settingsMock = $this->getShopSettingsMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock();

    $paymentGatewayMock = $this->getPaymentGatewayMock(array(
      'completePurchase' => array($this->once()),
      'isSuccessful' => array($this->once(), true),
    ));

    $strategy = $this->getAfterPaymentStrategyMock(array(
      'createPayment' => array($this->once(), $paymentGatewayMock),
      'getCurrentAbsoluteUrl' => array($this->once(), $expectedRedirectUrl)
    ), array($settingsMock, $checkoutMock, $cartMock, $translatorMock));

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCheckout', $expectedRedirectUrl);
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  /**
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\rz_shop_cart\ShopModeStrategy\PaymentStrategy
   */
  protected function getAfterPaymentStrategyMock(array $expects = null, $constructorArgs = null)
  {
    $className = '\\rz_shop_cart\\ShopModeStrategy\\AfterPaymentStrategy';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }
}
