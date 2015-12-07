<?php
namespace Test\Module\rz_shop_cart;

require_once('AbstractModuleTestCase.php');

/**
 * @group rz_shop_cart
 */
class RenderTest extends AbstractModuleTestCase
{
  /**
   * @dataProvider provider_test_renderContent_calledShowMethodsAsExpected
   */
  public function test_renderContent_calledShowMethodsAsExpected($unitContext,
                                                                 $showCartMatcher,
                                                                 $showCheckoutMatcher,
                                                                 $showSuccessMatcher,
                                                                 $expectedOutputParts)
  {
    // ARRANGE
    $unitId = 'test-rz-shop-cart';
    $unit = $this->createUnit(array('id' => $unitId));
    $apiMockConfig = array('websiteSettings' => array('rz_shop' => null));

    $moduleMock = $this->getModuleMock(array('showCart', 'showCheckout', 'showSuccess'));
    $moduleMock->expects($showCartMatcher)->method('showCart');
    $moduleMock->expects($showCheckoutMatcher)->method('showCheckout');
    $moduleMock->expects($showSuccessMatcher)->method('showSuccess');

    $moduleMock->phpunit_setUnitContext(array($unitId => $unitContext));

    // ACT
    $output = $this->render($moduleMock, $apiMockConfig, $unit);
    foreach ($expectedOutputParts as $part) {
      $this->assertContains($part, $output);
    }
  }

  /**
   * @return array
   */
  public function provider_test_renderContent_calledShowMethodsAsExpected()
  {
    return array(
      array(
        array(
          'errors' => array('TEST ERROR 1', 'TEST ERROR 2')
        ),
        $this->once(),
        $this->never(),
        $this->never(),
        array(
          '<div class="errorMessage">TEST ERROR 1</div>',
          '<div class="errorMessage">TEST ERROR 2</div>',
        ),
      ),
      array(
        array(
          'view' => 'showCart',
          'errors' => array('TEST ERROR 3', 'TEST ERROR 4'),
        ),
        $this->once(),
        $this->never(),
        $this->never(),
        array(
          '<div class="errorMessage">TEST ERROR 3</div>',
          '<div class="errorMessage">TEST ERROR 4</div>',
        ),
      ),
      array(
        array(
          'view' => 'showCheckout',
          'errors' => array('TEST ERROR 5', 'TEST ERROR 6'),
        ),
        $this->never(),
        $this->once(),
        $this->never(),
        array(
          '<div class="errorMessage">TEST ERROR 5</div>',
          '<div class="errorMessage">TEST ERROR 6</div>',
        ),
      ),
      array(
        array(
          'view' => 'showSuccess',
          'errors' => array('TEST ERROR 7', 'TEST ERROR 8'),
        ),
        $this->never(),
        $this->never(),
        $this->once(),
        array(
          '<div class="errorMessage">TEST ERROR 7</div>',
          '<div class="errorMessage">TEST ERROR 8</div>',
        ),
      ),
    );
  }
}
