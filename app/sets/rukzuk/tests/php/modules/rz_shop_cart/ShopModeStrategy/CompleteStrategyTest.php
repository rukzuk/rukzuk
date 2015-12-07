<?php
namespace Test\Module\rz_shop_cart;

use rz_shop_cart\ShopModeStrategy\ShopModeResponse;
use \Rukzuk\Modules\HtmlTagBuilder;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/CompleteStrategy.php');

/**
 * @group rz_shop_cart
 */
class CompleteStrategyTest extends AbstractStrategyTestCase
{
  public function test_process()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $settingsMock = $this->getShopSettingsMock(null, null, array(
      'getEmailNotificationAdr' => array($this->once(), $uniqueString.'-shop@example.com'),
      'getEmailConfirmationText' => array($this->once(), $uniqueString.'-buyer@example.com'),
    ));
    $cartMock = $this->getCartMock();
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock();
    $mailerMock = $this->getMailerMock();

    $strategy = $this->getCompleteStrategyMock(array(
      'renderCart' => array($this->once(), HtmlTagBuilder::div()->appendText('CartOutput')),
      'renderCheckoutSummary' => array($this->once(), HtmlTagBuilder::div()->appendText('CheckoutSummaryOutput')),
      'getMailer' => array($this->exactly(2), $mailerMock),
    ), array($settingsMock, $checkoutMock, $cartMock, $translatorMock));

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showSuccess');
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  /**
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\rz_shop_cart\ShopModeStrategy\CompleteStrategy
   */
  protected function getCompleteStrategyMock(array $expects = null, $constructorArgs = null)
  {
    $className = '\\rz_shop_cart\\ShopModeStrategy\\CompleteStrategy';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }

  /**
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Rukzuk\Modules\Mailer
   */
  protected function getMailerMock(array $expects = null, $constructorArgs = null)
  {
    $className = '\\Rukzuk\\Modules\\Mailer';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }
}
