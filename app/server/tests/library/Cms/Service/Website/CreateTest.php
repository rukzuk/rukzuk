<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Cms\Response,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer Create Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class CreateTest extends ServiceTestCase
{
  protected $service;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new WebsiteService('Website');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    // ARRANGE
    $this->enableGlobalSets();
    $attributes = array(
      'name' => '',
    );
    $expectedAttributes = array_merge_recursive($attributes, array(
      'description' => '',
      'navigation' => '',
      'publishingEnabled' => false,
      'publish' => '{"cname":"","type":"internal"}',
      'colorscheme' => '',
      'resolutions' => '',
      'version' => 0,
      'home' => '',
      'creationMode' => 'full',
      'isMarkedForDeletion' => false,
      'usedsetid' => 'rukzuk_test',
    ));

    // ACT
    $result = $this->service->create($attributes);

    // ASSERT
    $this->assertResultSuccess($result, $expectedAttributes);
  }

  /**
   * @test
   * @group library
   */
  public function test_createShouldAddNoUsedSetIdIfGlobalSetIdDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $attributes = array(
      'name' => '',
    );
    $expectedAttributes = array_merge_recursive($attributes, array(
      'description' => '',
      'navigation' => '',
      'publishingEnabled' => false,
      'publish' => '{"cname":"","type":"internal"}',
      'colorscheme' => '',
      'resolutions' => '',
      'version' => 0,
      'home' => '',
      'creationMode' => 'full',
      'isMarkedForDeletion' => false,
      'usedsetid' => null,
    ));

    // ACT
    $result = $this->service->create($attributes);

    // ASSERT
    $this->assertResultSuccess($result, $expectedAttributes);
  }

  /**
   * @test
   * @group library
   */
  public function test_createShouldCreateWebsiteWithGivenAttributesAsExpected()
  {
    // ARRANGE
    $this->enableGlobalSets();
    $attributes = array(
      'name' => 'PHPUnit Test Website - Create',
      'description' => 'website description',
      'navigation' => '[]',
      'publish' => '{"cname":"my.cname","type":"myNewType"}',
      'colorscheme' => '[{"foo":"bar"}]',
      'resolutions' => '{"enabled":true}',
      'home' => 'PAGE-ID-PAGE',
      'usedsetid' => 'test_usedsetid_'.__LINE__,
    );
    $expectedAttributes = array_merge_recursive($attributes, array(
      'publishingEnabled' => false,
      'version' => 0,
      'creationMode' => 'full',
      'isMarkedForDeletion' => false,
    ));

    // ACT
    $result = $this->service->create($attributes);

    // ASSERT
    $this->assertResultSuccess($result, $expectedAttributes);
  }

  /**
   * @test
   * @group library
   */
  public function test_createShouldAddDefaultPublishDataAsExpected()
  {
    // ARRANGE
    $expectedPublishData = $this->getDefaultPublishData();
    $serviceMock = $this->getMockBuilder(get_class($this->service))
      ->setConstructorArgs(array('Website'))
      ->setMethods(array('getDefaultPublishData'))
      ->getMock();
    $serviceMock->expects($this->atLeastOnce())
      ->method('getDefaultPublishData')
      ->will($this->returnValue($expectedPublishData));

    // ACT
    /** @var $website \Cms\Data\Website */
    $website = $serviceMock->create(array('name' => 'PHPUnit Test: '.__CLASS__.'::'.__METHOD__));

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\Website', $website);
    $this->assertNotEmpty($website->getPublish());
    $this->assertJson($website->getPublish());
    $actualPublishData =  json_decode($website->getPublish(), true);
    $this->assertEquals($expectedPublishData, $actualPublishData);
  }

  /**
   * @param \Cms\Data\Website $result
   * @param string $expectedData
   */
  protected function assertResultSuccess($result, $expectedData = '')
  {
    $this->assertInstanceOf('Cms\Data\Website', $result);
    $this->assertNotNull($result->getId());
    $actualData = $result->toArray();
    foreach ($expectedData as $attributeName => $expectedValue) {
      $actualAttributeName = strtolower($attributeName);
      $this->assertArrayHasKey($actualAttributeName, $actualData);
      $this->assertEquals($expectedValue, $actualData[strtolower($actualAttributeName)]);
    }

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($result->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $result->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $result->getLastupdate());
  }

  /**
   * @return array
   */
  protected function getDefaultPublishData()
  {
    return array(
      'type' => 'defaultPublishType',
      'foo' => 'bar',
      'bar' => 'foo',
    );
  }

}