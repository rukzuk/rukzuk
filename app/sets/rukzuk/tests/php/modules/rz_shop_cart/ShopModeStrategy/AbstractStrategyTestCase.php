<?php
namespace Test\Module\rz_shop_cart;

use rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once(__DIR__ . '/../AbstractModuleTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/Payment/OfflineGateway.php');

/**
 * @group rz_shop_cart
 */
abstract class AbstractStrategyTestCase extends AbstractModuleTestCase
{
  /**
   * @param array $expects
   *
   * @return \rz_shop_cart\Payment\OfflineGateway|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getPaymentGatewayMock(array $expects = null)
  {
    $className = '\\rz_shop_cart\\Payment\\OfflineGateway';
    return $this->createMock($className, null, null, $expects);
  }
}
