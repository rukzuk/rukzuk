<?php
namespace Test\Module\rz_shop_cart;

use \rz_shop_cart\ShopModeStrategy\ShopModeResponse;
use \rz_shop_cart\ShopModeStrategy\BackToCartStrategy;
use \Rukzuk\Modules\HtmlTagBuilder;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/BackToCartStrategy.php');

/**
 * @group rz_shop_cart
 */
class BackToCartStrategyTest extends AbstractStrategyTestCase
{
  public function test_process()
  {
    // ARRANGE
    $checkoutMock = $this->getCheckoutMock(null, null, array(
      'loadFromGlobals' => array($this->once()),
      'save' => array($this->once()),
    ));
    $strategy = new BackToCartStrategy($checkoutMock);

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCart');
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }
}
