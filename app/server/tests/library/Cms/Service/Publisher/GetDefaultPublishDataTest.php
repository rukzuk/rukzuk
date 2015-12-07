<?php


namespace Cms\Service\Publisher;

use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase;

class GetDefaultPublishDataTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Publisher
   */
  protected $service;

  protected function setUp()
  {
    parent::setUp();
    $this->service = new \Cms\Service\Publisher('Publisher');
  }

  /**
   * @test
   * @group library
   */
  public function test_getDefaultPublishDataShouldUsePublishTypeInternalAsDefault()
  {
    // ARRANGE
    $expectedPublishDataType = 'internal';

    // ACT
    $actualPublishData = $this->service->getDefaultPublishData();

    // ASSERT
    $this->assertArrayHasKey('type', $actualPublishData);
    $this->assertEquals($expectedPublishDataType, $actualPublishData['type']);
  }

  /**
   * @test
   * @group library
   */
  public function test_getDefaultPublishDataShouldReturnDataAsExpected()
  {
    // ARRANGE
    $expectedPublishData = $this->setDefaultPublishDataIntoConfig();

    // ACT
    $actualPublishData = $this->service->getDefaultPublishData();

    // ASSERT
    $this->assertEquals($expectedPublishData, $actualPublishData);
  }

  /**
   * @return array
   */
  protected function setDefaultPublishDataIntoConfig()
  {
    $type = __CLASS__.'::'.__METHOD__.'::'.microtime();
    $defaultConfig = array(
      'foo' => 'bar',
      'bar' => 'foo',
      'test' => array('active' => true),
    );

    ConfigHelper::mergeIntoConfig(array('publisher' => array('defaultPublish' => array(
      'type' => $type,
      'config' => array($type => $defaultConfig),
    ))));

    $defaultConfig['type'] = $type;
    return $defaultConfig;
  }
}