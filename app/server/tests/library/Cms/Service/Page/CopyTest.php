<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
  Cms\Service\Website as WebsiteService,
  Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer Create Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */
class CopyTest extends ServiceTestCase
{
  protected $service;

  /**
   * @var \Cms\Data\Page
   */
  protected $testEntry;

  protected $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PageService('Page');

    $attributes = array(
      'templateid' => '',
      'name' => 'PHPUnit Test Page - Copy',
      'pageType' => 'the_page_type_id',
      'pageAttributes' => json_encode((object)array('foo' => 'bar')),
    );
    $this->testEntry = $this->service->create($this->websiteId, $attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $newName = 'PHPUnit Test Website - Copy NewName';
    $result = $this->service->copy($this->testEntry->getId(),
      $this->testEntry->getWebsiteId(), $newName);

    $this->assertResultSuccess($result, $newName);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function entryNotExists()
  {
    $newName = 'PHPUnit Test Website - Copy NewName';
    $result = $this->service->copy('NICHT-EXISTIERENDE-ID',
      $this->testEntry->getWebsiteId(), $newName);
  }

  protected function assertResultFalse($result, $expectedData = '')
  {
    $this->assertNull($result);
  }

  /**
   * @param  \Cms\Data\Page $result
   * @param string          $expectedData
   */
  protected function assertResultSuccess($result, $expectedData = '')
  {
    $this->assertInstanceOf('Cms\Data\Page', $result);
    $this->assertSame($result->getName(), $expectedData);
    $this->assertNotSame($result->getId(), $this->testEntry->getId());
    $this->assertSame($result->getWebsiteId(), $this->testEntry->getWebsiteId());
    $this->assertEquals($result->getPageType(), $this->testEntry->getPageType());
    $this->assertEquals($result->getPageAttributes(), $this->testEntry->getPageAttributes());
  }
}