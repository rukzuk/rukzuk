<?php
namespace Test\Module\rz_shop_cart;

require_once(__DIR__ . '/../AbstractModuleTestCase.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/vendor/autoload.php');
require_once(MODULE_PATH . '/../../rz_shop/modules/rz_shop_cart/module/src/Payment/PayPalExpressGateway.php');


class Simple_Omnipay_PayPal_ExpressGateway_Mock
{
  private $phpunit_request;
  private $phpunit_params;

  public function purchase(array $parameters = array())
  {
    $this->phpunit_params = $parameters;
    return $this->phpunit_request;
  }

  public function completePurchase(array $parameters = array())
  {
    $this->phpunit_params = $parameters;
    return $this->phpunit_request;
  }

  public function phpunit_set_request($request)
  {
    $this->phpunit_request = $request;
  }

  public function phpunit_get_params()
  {
    return $this->phpunit_params;
  }
}


/**
 * @group rz_shop_cart
 */
class PayPalExpressGatewayTest extends AbstractModuleTestCase
{
  public function test_purchase()
  {
    // ARRANGE
    $uniqueString = __CLASS__ . '::' . __METHOD__ . '::' . __LINE__;
    $expectedPayPalUrl = 'THE-PAYPAL-URL-' . $uniqueString;
    $expectedUrls = array(
      'returnUrl' => 'THE-RETURN-URL-' . $uniqueString,
      'cancelUrl' => 'THE-CANCEL-URL-' . $uniqueString,
    );

    $omnipayResponse = $this->getOmnipayExpressAuthorizeResponseMock(array(
      'isRedirect' => array($this->once(), true),
      'getRedirectUrl' => array($this->once(), $expectedPayPalUrl)
    ));
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendPurchaseRequest' => array($this->once(), $omnipayResponse),
    ));

    // ACT
    $paymentMock->purchase($expectedUrls);

    // ASSERT
    $this->assertFalse($paymentMock->isSuccessful());
    $this->assertTrue($paymentMock->isRedirect());
    $this->assertSame($expectedPayPalUrl, $paymentMock->getRedirectUrl());
  }

  /**
   * @expectedException \rz_shop_cart\Payment\PaymentException
   * @expectedExceptionMessage No payment response!
   */
  public function test_purchase_noOmnipayResponse()
  {
    // ARRANGE
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendPurchaseRequest' => array($this->once(), null),
    ));

    // ACT
    $paymentMock->purchase(array());
  }

  /**
   * @expectedException \rz_shop_cart\Payment\PaymentException
   * @expectedExceptionMessage Payment error: THIS-IS-THE-RESPONSE-ERROR_MSG
   */
  public function test_purchase_omnipayResponseWithError()
  {
    // ARRANGE
    $omnipayResponse = $this->getOmnipayExpressAuthorizeResponseMock(array(
      'isRedirect' => array($this->once(), false),
      'getMessage' => array($this->once(), 'THIS-IS-THE-RESPONSE-ERROR_MSG'),
    ));
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendPurchaseRequest' => array($this->once(), $omnipayResponse),
    ));

    // ACT
    $paymentMock->purchase(array());
  }

  public function test_completePurchase()
  {
    // ARRANGE
    $omnipayResponse = $this->getOmnipayExpressAuthorizeResponseMock(array(
      'isSuccessful' => array($this->once(), true),
      'isRedirect' => array($this->never(), false),
      'getRedirectUrl' => array($this->never(), null)
    ));
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendCompletePurchaseRequest' => array($this->once(), $omnipayResponse),
    ));

    // ACT
    $paymentMock->completePurchase();

    // ASSERT
    $this->assertTrue($paymentMock->isSuccessful());
    $this->assertFalse($paymentMock->isRedirect());
    $this->assertNull($paymentMock->getRedirectUrl());
  }

  /**
   * @expectedException \rz_shop_cart\Payment\PaymentException
   * @expectedExceptionMessage No payment response!
   */
  public function test_completePurchase_noOmnipayResponse()
  {
    // ARRANGE
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendCompletePurchaseRequest' => array($this->once(), null),
    ));

    // ACT
    $paymentMock->completePurchase(array());
  }

  /**
   * @expectedException \rz_shop_cart\Payment\PaymentException
   * @expectedExceptionMessage Payment error: THIS-IS-THE-RESPONSE-ERROR_MSG
   */
  public function test_completePurchase_omnipayResponseWithError()
  {
    // ARRANGE
    $omnipayResponse = $this->getOmnipayExpressAuthorizeResponseMock(array(
      'isSuccessful' => array($this->once(), false),
      'getMessage' => array($this->once(), 'THIS-IS-THE-RESPONSE-ERROR_MSG'),
    ));
    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'sendCompletePurchaseRequest' => array($this->once(), $omnipayResponse),
    ));

    // ACT
    $paymentMock->completePurchase(array());
  }

  public function test_getOmnipayGateway()
  {
    // ARRANGE
    $uniqueString = __CLASS__ . '::' . __METHOD__ . '::' . __LINE__;
    $expectedUsername = 'username' . $uniqueString;
    $expectedPassword = 'password' . $uniqueString;
    $expectedSignature = 'signature' . $uniqueString;

    $translatorMock = $this->getTranslatorMock();
    $cart = $this->getCartMock();
    $checkoutMock = $this->getCheckoutMock();
    $settingsMock = $this->getShopSettingsMock(null, null, array(
      'getPayPalExpressUsername' => array($this->once(), $expectedUsername),
      'getPayPalExpressPassword' => array($this->once(), $expectedPassword),
      'getPayPalExpressSignature' => array($this->once(), $expectedSignature),
    ));
    $paymentMock = $this->getPayPalExpressGatewayMock(array(),
      array($settingsMock, $checkoutMock, $cart, $translatorMock));

    // ACT
    /** @var \Omnipay\PayPal\ExpressGateway $actualOmnipayGateway */
    $actualOmnipayGateway = $this->callMethod($paymentMock, 'getOmnipayGateway');

    // ASSERT
    $this->assertInstanceOf('\Omnipay\PayPal\ExpressGateway', $actualOmnipayGateway);
    $this->assertSame($expectedUsername, $actualOmnipayGateway->getUsername());
    $this->assertSame($expectedPassword, $actualOmnipayGateway->getPassword());
    $this->assertSame($expectedSignature, $actualOmnipayGateway->getSignature());
    $this->assertFalse($actualOmnipayGateway->getTestMode());
  }

  public function test_createCardForShippingAddress()
  {
    // ARRANGE
    $uniqueString = __CLASS__ . '::' . __METHOD__ . '::' . __LINE__;
    $expectedAddress = array(
      'name' => $uniqueString . '-name',
      'company' => $uniqueString . '-company',
      'street' => $uniqueString . '-street',
      'zip' => $uniqueString . '-zip',
      'city' => $uniqueString . '-city',
      'country' => $uniqueString . '-country',
      'telephone' => $uniqueString . '-telephone',
      'email' => $uniqueString . '-email',
    );

    $translatorMock = $this->getTranslatorMock();
    $settingsMock = $this->getShopSettingsMock();
    $cart = $this->getCartMock();
    $checkoutMock = $this->getCheckoutMock(null, null, array(
      'getShippingAddress' => array($this->once(), $expectedAddress),
    ));

    $paymentMock = $this->getPayPalExpressGatewayMock(array(),
      array($settingsMock, $checkoutMock, $cart, $translatorMock));

    // ACT
    /** @var \Omnipay\Common\CreditCard $actualCard */
    $actualCard = $this->callMethod($paymentMock, 'createCardForShippingAddress');

    // ASSERT
    $this->assertSame($expectedAddress['name'], $actualCard->getName());
    $this->assertSame($expectedAddress['street'], $actualCard->getAddress1());
    $this->assertSame($expectedAddress['city'], $actualCard->getCity());
    $this->assertSame($expectedAddress['zip'], $actualCard->getPostcode());
    $this->assertSame($expectedAddress['country'], $actualCard->getCountry());
    $this->assertSame($expectedAddress['telephone'], $actualCard->getPhone());
    $this->assertSame($expectedAddress['email'], $actualCard->getEmail());
    $this->assertSame('', $actualCard->getState());
    $this->assertNull($actualCard->getAddress2());
  }

  /**
   * @dataProvider provider_test_sendPurchaseRequest
   */
  public function test_sendPurchaseRequest($methodeToTest, $expectedUrls, $expectedShippingCosts,
                                           $expectedTax, $expectedLocalCode, $expectedCurrencyCode,
                                           $expectedCardItems, $expectedParameters)
  {
    // ARRANGE
    $translatorMock = $this->getTranslatorMock();
    $checkoutMock = $this->getCheckoutMock();
    $shippingScalePriceData = [];
    $settingsMock = $this->getShopSettingsMock(null, null, array(
      'getLocalCode' => array($this->once(), $expectedLocalCode),
      'getShopCurrencyCode' => array($this->once(), $expectedCurrencyCode),
    ));
    $cart = $this->createCart($expectedCardItems, null, null,
      $expectedShippingCosts, $expectedTax, $shippingScalePriceData);

    $omnipayResponse = $this->getOmnipayExpressAuthorizeResponseMock();
    $omnipayRequest = $this->getOmnipayExpressAuthorizeRequestMock(array(
      'setItems' => array($this->once()),
      'send' => array($this->once(), $omnipayResponse)
    ));
    $omnipayMock = new Simple_Omnipay_PayPal_ExpressGateway_Mock();
    $omnipayMock->phpunit_set_request($omnipayRequest);
    $omnipayCardMock = $this->getOmnipayCardMock();

    $paymentMock = $this->getPayPalExpressGatewayMock(array(
      'getOmnipayGateway' => array($this->once(), $omnipayMock),
      'createCardForShippingAddress' => array($this->once(), $omnipayCardMock)
    ), array($settingsMock, $checkoutMock, $cart, $translatorMock));

    // ACT
    $actualResponse = $this->callMethod($paymentMock, $methodeToTest, array($expectedUrls));

    // ASSERT
    $this->assertSame($omnipayResponse, $actualResponse);
    $expectedParameters['card'] = $omnipayCardMock;
    $this->assertEquals($expectedParameters, $omnipayMock->phpunit_get_params());
  }

  /**
   * @return array
   */
  public function provider_test_sendPurchaseRequest()
  {
    $uniqueString = __CLASS__ . '::' . __METHOD__ . '::' . __LINE__;

    $expectedShippingCosts = 5;
    $expectedTax = 19;
    $expectedLocalCode = 'de';
    $expectedCurrencyCode = 'EUR';
    $expectedUrls = array(
      'returnUrl' => 'THE-RETURN-URL-' . $uniqueString,
      'cancelUrl' => 'THE-CANCEL-URL-' . $uniqueString,
    );
    $expectedCardItems = array(
      array(
        'name' => 'ITEM-1-NAME-' . $uniqueString,
        'pageId' => 'ITEM-1-PAGE-ID-' . $uniqueString,
        'price' => 100,
        'tax' => 19,
        'variant' => null,
      ),
      array(
        'name' => 'ITEM-2-NAME-' . $uniqueString,
        'pageId' => 'ITEM-2-PAGE-ID-' . $uniqueString,
        'price' => 50,
        'tax' => 19,
        'variant' => 'ITEM-2-VARIANT-1',
      )
    );

    return array(
      array(
        'sendPurchaseRequest',
        $expectedUrls,
        $expectedShippingCosts,
        $expectedTax,
        $expectedLocalCode,
        $expectedCurrencyCode,
        $expectedCardItems,
        array(
          'amount' => 193,
          'shippingAmount' => 5,
          'currency' => $expectedCurrencyCode,
          'localeCode' => strtoupper($expectedLocalCode),
          'returnUrl' => $expectedUrls['returnUrl'],
          'cancelUrl' => $expectedUrls['cancelUrl'],
          'landingPage' => 'Login',
          'reqConfirmShipping' => 0,
          'allowNote' => 0,
          'noShipping' => 1,
          'addressOverride' => 1,
        )
      ),
      array(
        'sendCompletePurchaseRequest',
        $expectedUrls,
        $expectedShippingCosts,
        $expectedTax,
        $expectedLocalCode,
        $expectedCurrencyCode,
        $expectedCardItems,
        array(
          'amount' => 193,
          'shippingAmount' => 5,
          'currency' => $expectedCurrencyCode,
          'localeCode' => strtoupper($expectedLocalCode),
        )
      ),
    );
  }

  /**
   * @param array $expects
   * @param array $constructorArgs
   *
   * @return \rz_shop_cart\Payment\PayPalExpressGateway|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getPayPalExpressGatewayMock(array $expects = null, $constructorArgs = null)
  {
    $className = '\\rz_shop_cart\\Payment\\PayPalExpressGateway';
    return $this->createMock($className, null, null, $expects, $constructorArgs);
  }

  /**
   * @param array $methods
   * @param array $excludedMethods
   * @param array $expects
   *
   * @return \Omnipay\PayPal\ExpressGateway|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getOmnipayMock(array $expects = null)
  {
    $className = '\\Omnipay\\PayPal\\ExpressGateway';
    return $this->createMock($className, null, null, $expects);
  }

  /**
   * @param array $expects
   *
   * @return \Omnipay\PayPal\Message\ExpressAuthorizeRequest|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getOmnipayExpressAuthorizeRequestMock(array $expects = null)
  {
    $className = '\\Omnipay\\PayPal\\Message\\ExpressAuthorizeRequest';
    return $this->createMock($className, null, null, $expects);
  }

  /**
   * @param array $expects
   *
   * @return \Omnipay\PayPal\Message\ExpressAuthorizeResponse|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getOmnipayExpressAuthorizeResponseMock(array $expects = null)
  {
    $className = '\\Omnipay\\PayPal\\Message\\ExpressAuthorizeResponse';
    return $this->createMock($className, null, null, $expects);
  }

  /**
   * @param array $expects
   *
   * @return \Omnipay\Common\CreditCard|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getOmnipayCardMock(array $expects = null)
  {
    $className = '\\\Omnipay\\Common\\CreditCard';
    return $this->createMock($className, null, null, $expects);
  }

}