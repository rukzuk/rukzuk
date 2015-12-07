<?php
namespace Test\Module\rz_shop_cart;

use \rz_shop_cart\ShopModeStrategy\ProcessCartStrategy;
use \rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/ProcessCartStrategy.php');

/**
 * @group rz_shop_cart
 */
class ProcessCartStrategyTest extends AbstractStrategyTestCase
{
  public function test_mode_payment()
  {
    // ARRANGE
    $cart = $this->getCartMock(null, null, array(
      'save' => array($this->once()),
    ));
    $cartProcessor = $this->getCartProcessorMock();
    $cartProcessor->expects($this->once())
      ->method('handlePOST')
      ->with($this->identicalTo($cart));

    $strategy = new ProcessCartStrategy($cartProcessor, $cart);

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCart');
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }
}
