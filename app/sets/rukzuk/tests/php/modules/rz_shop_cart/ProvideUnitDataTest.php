<?php
namespace Test\Module\rz_shop_cart;

use rz_shop_cart\ShopModeStrategy\ShopModeResponse;

require_once('AbstractModuleTestCase.php');

/**
 * @group rz_shop_cart
 */
class ProvideUnitDataTest extends AbstractModuleTestCase
{
  public function test_provideUnitData_processActionOnlyCalledOnce()
  {
    // ARRANGE
    $shopModeResponse = new ShopModeResponse();
    $moduleMock = $this->getModuleMock(null, null, array(
      'processAction' => array($this->once(), $shopModeResponse)
    ));

    // ACT
    $this->provideUnitData($moduleMock);
    $this->provideUnitData($moduleMock);
  }

  public function test_provideUnitData_setValuesFromResponseToUnitContext()
  {
    // ARRANGE
    $uniqueString = __CLASS__.'::'.__METHOD__.'::'.__LINE__;
    $expectedViewMode = 'THE-NEW-VIEW-MODE-'.$uniqueString;
    $expectedErrorMsg = 'THE-ERROR-MESSAGE-'.$uniqueString;
    $expectedRedirectUrl = 'THE-REDIRECT-URL-'.$uniqueString;
    $unitId = 'UNIT-ID-'.$uniqueString;
    $unit = $this->createUnit(array('id' => $unitId));
    $shopModeResponse = new ShopModeResponse($expectedViewMode, $expectedRedirectUrl);
    $shopModeResponse->addError($expectedErrorMsg);
    $moduleMock = $this->getModuleMock(null, null, array(
      'processAction' => array($this->once(), $shopModeResponse)
    ));

    // ACT
    $actualProvidedData = $this->provideUnitData($moduleMock, null, $unit);

    // ASSERT
    $expectedUnixContext = array($unitId => array(
      'view' => $expectedViewMode,
      'errors' => array($expectedErrorMsg),
      'alreadyProcessed' => true,
    ));
    $this->assertEquals($expectedUnixContext, $moduleMock->phpunit_getUnitContext());
    $this->assertEquals(array('url' => $expectedRedirectUrl), $actualProvidedData['redirect']);
  }

  public function test_provideUnitData_setNoRedirect()
  {
    // ARRANGE
    $moduleMock = $this->getModuleMock(null, null, array(
      'processAction' => array($this->once(), new ShopModeResponse())
    ));

    // ACT
    $actualProvidedData = $this->provideUnitData($moduleMock);

    // ASSERT
    $this->assertNull($actualProvidedData['redirect']);
  }
}
