<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService;
use Cms\Response;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * Tests fuer Copy Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class CopyTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);
    parent::setUp();

    $this->service = new WebsiteService('Website');

    $attributes = array(
      'name' => 'PHPUnit Test Website - Create',
      'description' => 'website description',
      'navigation' => '[]',
      'publish' => '{}'
    );
    $this->testEntry = $this->service->create($attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $newName = 'PHPUnit Test Website - Copy NewName';
    $result = $this->service->copy($this->testEntry->getId(), $newName);

    $expectedData = array('newName' => $newName);

    $this->assertResultSuccess($result, $expectedData);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function entryNotExists()
  {
    $newName = 'PHPUnit Test Website - Copy NewName';
    $result = $this->service->copy('ID-EXISTIERT-NICHT', $newName);
  }

  protected function assertResultFalse($result)
  {
    $this->assertNull($result);
  }

  protected function assertResultSuccess($result, $expectedData)
  {
    $this->assertInstanceOf('Cms\Data\Website', $result);
    $this->assertNotSame($this->testEntry->getId(), $result->getId());
    $this->assertSame($expectedData['newName'], $result->getName());
    $this->assertsame($this->testEntry->getDescription(), $result->getDescription());
  }

}