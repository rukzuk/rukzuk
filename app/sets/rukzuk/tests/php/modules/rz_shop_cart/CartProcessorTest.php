<?php
namespace Test\Module\rz_shop_cart;

require_once('AbstractModuleTestCase.php');

/**
 * @group rz_shop_cart
 */
class CartProcessorTest extends AbstractModuleTestCase
{
  public function test_handlePOST_clearCart()
  {
    // ARRANGE
    $cart = $this->createCart(array(
      array(
        'name' => 'fake.cartItem1.name',
        'pageId' => null,
        'price' => 100,
        'tax' => 19,
        'variant' => null,
      ),
      array(
        'name' => 'fake.cartItem2.name',
        'pageId' => null,
        'price' => 50,
        'tax' => 19,
        'variant' => null,
      )
    ));

    $cartProcessor = $this->getCartProcessorMock(null, array('handlePOST'));
    $cartProcessor->expects($this->once())
      ->method('getFromPOST')
      ->with($this->equalTo('action'))
      ->will($this->returnValue('clearCart'));

    // ACT
    $cartProcessor->handlePOST($cart);

    // ASSERT
    $this->assertCount(0, $cart->all());
  }

  public function test_handlePOST_addToCart()
  {
    // ARRANGE
    $cart = $this->createCart();

    $cartProcessor = $this->getCartProcessorMock(array('getFromPOST', 'getProductData',
      'getDefaultVat'));
    $cartProcessor->expects($this->exactly(3))
      ->method('getFromPOST')
      ->will($this->onConsecutiveCalls('addToCart', 'THE-PRODUCT-PAGE-ID', 'THE-VARIANT'));
    $cartProcessor->expects($this->once())
      ->method('getProductData')
      ->with($this->equalTo('THE-PRODUCT-PAGE-ID'))
      ->will($this->returnValue(array(
        '_name' => 'THE-PRODUCT-NAME',
        'price' => 119,
      )));
    $cartProcessor->expects($this->once())
      ->method('getDefaultVat')
      ->will($this->returnValue(0.19));

    // ACT
    $cartProcessor->handlePOST($cart);

    // ASSERT
    $items = $cart->all();
    $this->assertCount(1, $items);
    $this->assertSame('THE-PRODUCT-NAME', $items[0]['name']);
    $this->assertSame('THE-PRODUCT-PAGE-ID', $items[0]['pageId']);
    $this->assertEquals(100, $items[0]['price']);
    $this->assertEquals(19, $items[0]['tax']);
    $this->assertEquals('THE-VARIANT', $items[0]['variant']);
    $this->assertEquals(1, $items[0]['quantity']);
  }

  public function test_handlePOST_updateAmount()
  {
    // ARRANGE
    $cartItem = $this->createCartItem(array(
      'name' => 'fake.cartItem1.name',
      'pageId' => null,
      'price' => 100,
      'tax' => 19,
      'variant' => null,
    ));
    $cartItemId = $cartItem->getId();
    $cart = $this->createCart(array($cartItem));

    $cartProcessor = $this->getCartProcessorMock(array('getFromPOST'));
    $cartProcessor->expects($this->exactly(3))
      ->method('getFromPOST')
      ->will($this->onConsecutiveCalls('updateAmount', $cartItemId, 5));

    // ACT
    $cartProcessor->handlePOST($cart);

    // ASSERT
    $this->assertCount(1, $cart->all());
    $this->assertTrue($cart->has($cartItemId));
    $item = $cart->get($cartItemId);
    $this->assertEquals(5, $item['quantity']);
  }

  public function test_handlePOST_removedItemIfAmountIsZero()
  {
    // ARRANGE
    $cartItem = $this->createCartItem(array(
      'name' => 'fake.cartItem1.name',
      'pageId' => null,
      'price' => 100,
      'tax' => 19,
      'variant' => null,
    ));
    $cartItemId = $cartItem->getId();
    $cart = $this->createCart(array($cartItem));

    $cartProcessor = $this->getCartProcessorMock(array('getFromPOST'));
    $cartProcessor->expects($this->exactly(3))
      ->method('getFromPOST')
      ->will($this->onConsecutiveCalls('updateAmount', $cartItemId, 0));

    // ACT
    $cartProcessor->handlePOST($cart);

    // ASSERT
    $this->assertCount(0, $cart->all());
  }

  public function test_handlePOST_notSupportedAction()
  {
    // ARRANGE
    $cart = $this->getCartMock();

    $cartProcessor = $this->getCartProcessorMock(null, array('handlePOST'));
    $cartProcessor->expects($this->never())
      ->method($this->logicalNot($this->logicalOr('handlePOST', 'getFromPOST')));
    $cartProcessor->expects($this->once())
      ->method('getFromPOST')
      ->with($this->equalTo('action'))
      ->will($this->returnValue('thisIsNoAction'));

    // ACT
    $cartProcessor->handlePOST($cart);
  }

}
