<?php


namespace Application\Controller\Import;

use Test\Seitenbau\ControllerTestCase;
use Cms\Service\Website as WebsiteService;

/**
 * ImportController Local Test
 *
 * @package Application\Controller\Import
 *
 * @group import
 */

class LocalTest extends ControllerTestCase
{
  protected $actionEndpoint = 'import/local';

  /**
   * @test
   * @group integration
   */
  public function test_localAction_useGivenNameAsWebsiteName()
  {
    // ARRANGE
    $expectedLocalId = 'local_test_import_002';
    $expectedWebsiteName = 'This_Is_The_New_Website_Name_'.__CLASS__.'::'.__METHOD__;
    $expectedResponseData = array(
      'website' => 1,
      'modules' => array(),
      'templatesnippets' => array(),
      'templates' => array(),
      'pages' => array(),
      'media' => array(),
      'packages' => array(),
    );

    // ACT
    $this->dispatchWithParams($this->actionEndpoint, array(
      'localId' => $expectedLocalId,
      'websiteName' => $expectedWebsiteName,
    ));

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertResponseData($responseData, $expectedResponseData);

    $this->assertObjectHasAttribute('website', $responseData);
    $this->assertInternalType('array', $responseData->website);
    $this->assertCount(1, $responseData->website);
    $importedWebsite = $responseData->website[0];
    $this->assertObjectHasAttribute('name', $importedWebsite);
    $this->assertSame($expectedWebsiteName, $importedWebsite->name);

    $this->assertObjectHasAttribute('id', $importedWebsite);
    $website = $this->getWebsiteService()->getById($importedWebsite->id);
    $this->assertSame($expectedWebsiteName, $website->getName());
  }

  /**
   * @param \stdClass $responseData
   * @param array $expectedResponseData
   */
  protected function assertResponseData($responseData, $expectedResponseData)
  {
    $this->assertInternalType('object', $responseData);

    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertInternalType('string', $responseData->websiteId);
    $this->assertNotEmpty($responseData->websiteId);

    foreach ($expectedResponseData as $dataKey => $expectedValue) {
      $this->assertObjectHasAttribute($dataKey, $responseData);
      if (is_array($expectedValue)) {
        $this->assertEquals($expectedValue, $responseData->$dataKey);
      } else {
        $this->assertInternalType('array', $responseData->$dataKey);
        $this->assertCount($expectedValue, $responseData->$dataKey);
        foreach ($responseData->$dataKey as $actualValue) {
          $this->assertInternalType('object', $actualValue);
          $this->assertObjectHasAttribute('id', $actualValue);
          $this->assertNotEmpty($actualValue->id);
          $this->assertObjectHasAttribute('name', $actualValue);
          $this->assertNotEmpty($actualValue->name);
        }
      }
    }
  }

  /**
   * @return WebsiteService
   */
  protected function getWebsiteService()
  {
    return new WebsiteService('Website');
  }
}