<?php
namespace Test\Module\rz_shop_cart;

use rz_shop_cart\ShopModeStrategy\AbstractShopModeStrategy;
use Test\Rukzuk\TestCaseException;
use rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once('AbstractModuleTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/AbstractShopModeStrategy.php');


/**
 * @group rz_shop_cart
 */
class ProcessActionTest extends AbstractModuleTestCase
{
  public function test_exception_occurred()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedExceptionMsg = 'TEST-CASE-EXCEPTION-'.$uniqueString;
    $expectedException = new TestCaseException($expectedExceptionMsg);
    $errorShopModeStrategy = $this->getErrorShopModeStrategy(array(
      'process' => array($this->once(), $this->throwException($expectedException)),
    ));

    /** @var rz_shop_cart_ModuleTest_TestModule $module */
    $module = $this->getModuleMock(null, null, array(
      'getStrategy' => array($this->once(), $errorShopModeStrategy),
    ));

    // ACT
    $actualResponse = $this->callProcessAction($module);

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCart');
    $expectedShopResponse->addError($expectedExceptionMsg);
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  /**
   * @dataProvider provider_test_noModeGiven
   */
  public function test_noModeGiven($unitId, $apiMockConfig, $expectedShopResponse)
  {
    // ARRANGE
    $unit = $this->createUnit(array('id' => $unitId));

    $cart = $this->getCartMock();
    $createCheckoutForm = $this->getCheckoutMock();

    /** @var rz_shop_cart_ModuleTest_TestModule $module */
    $module = $this->getModuleMock(null, null, array(
      'createCart' => array($this->once(), $cart),
      'createCheckoutForm' => array($this->once(), $createCheckoutForm),
    ));

    // ACT
    $actualResponse = $this->callProcessAction($module, $apiMockConfig, $unit);

    // ASSERT
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }

  public function provider_test_noModeGiven()
  {
    $unitId = 'test-rz-shop-cart';

    return array(
      array(
        $unitId,
        array(
          'websiteSettings' => array('rz_shop' => null),
          'isTemplate' => true,
        ),
        new ShopModeResponse('showCheckout'),
      ),
      array(
        $unitId,
        array(
          'websiteSettings' => array('rz_shop' => null),
          'isTemplate' => false,
        ),
        new ShopModeResponse('showCart'),
      ),
    );
  }

  /**
   * @param SimpleModule|array|null $module The module instance
   *    that implements the render method; or a configuration for createModule
   * @param CssApiMock|array|null $api The api mock instance or a
   *    configuration for createCssApi
   * @param Unit|array|null $unit The unit data instance a configuration for createUnit
   * @param ModuleInfo|array|null $module The module info instance or a configuration
   *    for createModuleInfo
   *
   * @return mixed
   */
  protected function callProcessAction($module = null, $api = null, $unit = null, $moduleInfo = null)
  {
    // create sane defaults
    if (is_null($module) || is_array($module)) {
      $module = $this->createModule($module);
    }
    if (is_null($api) || is_array($api)) {
      $api = $this->createRenderApi($api);
    }
    if (is_null($unit) || is_array($unit)) {
      $unit = $this->createUnit($unit);
    }
    if (is_null($moduleInfo) || is_array($moduleInfo)) {
      $moduleInfo = $this->createModuleInfo($moduleInfo);
    }

    // do rendering
    ob_start();
    $returnValue = $this->callMethod($module, 'processAction', array($api, $unit, $moduleInfo));
    $output = ob_get_contents();
    ob_end_clean();

    // assert that there is no output
    $this->assertSame('', $output);

    return $returnValue;
  }

  /**
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\rz_shop_cart\ShopModeStrategy\AbstractShopModeStrategy
   */
  protected function getErrorShopModeStrategy(array $expects = null, $constructorArgs = null)
  {
    $className = '\\rz_shop_cart\\ShopModeStrategy\\AbstractShopModeStrategy';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }
}
