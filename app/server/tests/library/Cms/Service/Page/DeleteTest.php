<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
    Cms\Service\Website as WebsiteService,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer Delete Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class DeleteTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PageService('Page');

    $attributes = array(
      'templateid' => '',
      'name' => 'PHPUnit Test Page - Delete'
    );
    $this->testEntry = $this->service->create($this->websiteId, $attributes);
  }

  /**
   * @test
   * @group library
   */
  public function successDeleteOnePage()
  {
    $result = $this->service->delete($this->testEntry->getId(),
      $this->testEntry->getWebsiteId());

    $this->assertTrue($result);
  }

  /**
   * @test
   * @group library
   */
  public function successDeleteTwoPages()
  {
    $ids = array(
      'PAGE-1xsfhjki-jk63-4he9-af5c-90ae9d96d3c2-PAGE',
      'PAGE-2xsfhjki-jk63-4he9-af5c-90ae9d96d3c2-PAGE'
    );
    
    $result = $this->service->deletePages($ids, $this->testEntry->getWebsiteId());

    $this->assertSame(2, $result);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function entryNotExists()
  {
    $result = $this->service->delete('NICHT-EXISTIERENDE-ID',
      $this->testEntry->getWebsiteId());
  }

  protected function assertResultFalse($result, $expectedData = '')
  {
    $this->assertNull($result);
  }

  protected function assertResultSuccess($result, $expectedData = '')
  {
    $this->assertInstanceOf('Cms\Data\Page', $result);
    $this->assertSame($result->getName(), $expectedData);
    $this->assertNotSame($result->getId(), $this->testEntry->getId());
    $this->assertSame($result->getWebsiteId(), $this->testEntry->getWebsiteId());
  }
}