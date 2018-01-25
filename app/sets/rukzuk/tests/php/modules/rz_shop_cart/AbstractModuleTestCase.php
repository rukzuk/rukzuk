<?php
namespace Test\Module\rz_shop_cart;

use \Test\Rukzuk\ModuleTestCase;
use \rz_shop_cart\CartWithShipping;

require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/rz_shop_cart.php');

class rz_shop_cart_ModuleTest_TestModule extends \Rukzuk\Modules\rz_shop_cart
{
  public function phpunit_getUnitContext()
  {
    return $this->unitContext;
  }

  public function phpunit_setUnitContext($unitContext)
  {
    $this->unitContext = $unitContext;
  }
}

/**
 * Class rz_shop_cart_ModuleTest
 *
 * @group rz_shop_cart
 */
abstract class AbstractModuleTestCase extends ModuleTestCase
{

  protected $moduleNS = '\\Test\\Module\\rz_shop_cart';
  protected $moduleClass = 'rz_shop_cart_ModuleTest_TestModule';


  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return rz_shop_cart_ModuleTest_TestModule|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getModuleMock(array $methods = null, array $excludedMethods = null,
                                   array $expects = null)
  {
    $className = $this->moduleNS . '\\' . $this->moduleClass;
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \rz_shop_cart\CartProcessor|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getCartProcessorMock(array $methods = null, array $excludedMethods = null,
                                          array $expects = null)
  {
    $className = '\\rz_shop_cart\\CartProcessor';
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\rz_shop_cart\CartProcessor
   */
  protected function getCartMock(array $methods = null, array $excludedMethods = null,
                                 array $expects = null)
  {
    $className = '\\rz_shop_cart\\CartWithShipping';
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \rz_shop_cart\CheckoutFrom|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getCheckoutMock(array $methods = null, array $excludedMethods = null,
                                     array $expects = null)
  {
    $className = '\\rz_shop_cart\\CheckoutFrom';
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \rz_shop_cart\CheckoutFrom|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getShopSettingsMock(array $methods = null, array $excludedMethods = null,
                                         array $expects = null)
  {
    $className = '\\rz_shop_cart\\ShopSettings';
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \Rukzuk\Modules\Translator|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getTranslatorMock(array $methods = null, array $excludedMethods = null,
                                       array $expects = null)
  {
    $className = '\\Rukzuk\\Modules\\Translator';
    return $this->createMock($className, $methods, $excludedMethods, $expects);
  }

  /**
   * @param string     $className
   * @param array|null $methods
   * @param array|null $excludedMethods
   * @param array|null $expects
   * @param array|null $constructorArgs
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMock($className, $methods, $excludedMethods, $expects,
                                $constructorArgs = null)
  {
    $replacedMethods = $this->getMethodsToBeReplaced($className, $methods, $excludedMethods, $expects);
    $mockBuilder = $this->getMockBuilder($className);
    if (is_array($constructorArgs)) {
      $mockBuilder->setConstructorArgs($constructorArgs);
    } else {
      $mockBuilder->disableOriginalConstructor();
    }
    if (is_array($replacedMethods)) {
      $mockBuilder->setMethods($replacedMethods);
    }
    $mock = $mockBuilder->getMock();
    if (is_array($expects)) {
      foreach($expects as $methodName => $expect) {
        $mocker = $mock->expects($expect[0])->method($methodName);
        if (count($expect) > 1) {
          if ($expect[1] instanceof \PHPUnit_Framework_MockObject_Stub) {
            $mocker->will($expect[1]);
          } else {
            $mocker->will($this->returnValue($expect[1]));
          }
        }
      }
    }
    return $mock;
  }

  /**
   * @param string     $className
   * @param array|null $methods
   * @param array|null $excludedMethods
   * @param array|null $expects
   *
   * @return array|null
   */
  protected function getMethodsToBeReplaced($className, $methods, $excludedMethods, $expects)
  {
    if (!is_array($methods) && !is_array($excludedMethods) && !is_array($expects)) {
      return null;
    }

    if (is_array($methods)) {
      $methodsToReplace = $methods;
    } else {
      $methodsToReplace = array();
    }

    if (is_array($expects)) {
      $methodsToReplace = array_merge($methodsToReplace, array_keys($expects));
    }

    if (is_array($excludedMethods)) {
      $includedMethodNames = $this->getAllMethodNamesExcept($className, $excludedMethods);
      $methodsToReplace = array_merge($methodsToReplace, $includedMethodNames);
    }

    return array_unique($methodsToReplace);
  }

  /**
   * @param string $className
   * @param array  $excluded
   *
   * @return array
   */
  protected function getAllMethodNamesExcept($className, array $excluded)
  {
    $obj = new \ReflectionClass ($className);
    $allMethods = array_map(function (&$v) {
      return $v->getName();
    }, $obj->getMethods());
    return array_diff($allMethods, $excluded);
  }

  /**
   * @param array       $items
   * @param string      $cartId
   * @param null|object $store
   * @param int         $shippingCosts
   * @param int         $shippingTax
   *
   * @return CartWithShipping
   */
  protected function createCart($items = array(), $cartId = 'CART_ID', $store = null,
                                $shippingCosts = 0, $shippingTax = 0, $shippingScalePriceData = [])
  {
    if (!is_object($store)) {
      $store = $this->createMock('\\Cart\\Storage\\Store', null, null, null);
    }
    $cart = new CartWithShipping($cartId, $store, $shippingCosts, $shippingTax, $shippingScalePriceData);
    if (count($items) > 0) {
      foreach ($items as $item) {
        if (is_array($item)) {
          $cart->add($this->createCartItem($item));
        } else {
          $cart->add($item);
        }
      }
    }
    return $cart;
  }

  /**
   * @param array $itemConfig
   *
   * @return \Cart\CartItem
   */
  protected function createCartItem(array $itemConfig = array())
  {
    return new \Cart\CartItem($itemConfig);
  }
}
