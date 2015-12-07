<?php
namespace Test\Module\rz_shop_cart;

require_once(__DIR__ . '/../AbstractModuleTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/Payment/OfflineGateway.php');

use \rz_shop_cart\Payment\OfflineGateway;

/**
 * @group rz_shop_cart
 */
class OfflineGatewayTest extends AbstractModuleTestCase
{
  public function test_purchase()
  {
    // ARRANGE
    $settingsMock = $this->getShopSettingsMock();
    $checkoutMock = $this->getCheckoutMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();

    $paymentGateway = new OfflineGateway($settingsMock, $checkoutMock, $cartMock, $translatorMock);

    // ACT
    $paymentGateway->purchase(array());

    // ASSERT
    $this->assertTrue($paymentGateway->isSuccessful());
    $this->assertFalse($paymentGateway->isRedirect());
    $this->assertNull($paymentGateway->getRedirectUrl());
  }

  public function test_completePurchase()
  {
    // ARRANGE
    $settingsMock = $this->getShopSettingsMock();
    $checkoutMock = $this->getCheckoutMock();
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();

    $paymentGateway = new OfflineGateway($settingsMock, $checkoutMock, $cartMock, $translatorMock);

    // ACT
    $paymentGateway->completePurchase(array());

    // ASSERT
    $this->assertTrue($paymentGateway->isSuccessful());
    $this->assertFalse($paymentGateway->isRedirect());
    $this->assertNull($paymentGateway->getRedirectUrl());
  }
}