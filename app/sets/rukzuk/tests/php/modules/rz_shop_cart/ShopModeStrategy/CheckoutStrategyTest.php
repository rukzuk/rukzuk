<?php
namespace Test\Module\rz_shop_cart;

use \rz_shop_cart\ShopModeStrategy\ShopModeResponse;
use \rz_shop_cart\ShopModeStrategy\CheckoutStrategy;
use \Rukzuk\Modules\HtmlTagBuilder;

require_once('AbstractStrategyTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/ShopModeStrategy/CheckoutStrategy.php');

/**
 * @group rz_shop_cart
 */
class CheckoutStrategyTest extends AbstractStrategyTestCase
{
  public function test_process()
  {
    // ARRANGE
    $strategy = new CheckoutStrategy();

    // ACT
    $actualResponse = $strategy->process();

    // ASSERT
    $expectedShopResponse = new ShopModeResponse('showCheckout');
    $this->assertEquals($expectedShopResponse, $actualResponse);
  }
}
