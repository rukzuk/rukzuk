<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer CopyPagesToNewWebsite Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class CopyPagesToNewWebsiteTest extends ServiceTestCase
{
  protected $sqlFixtures = array('library_Cms_Service_Page.json');

  /**
   * @var \Cms\Service\Page
   */
  protected $service;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PageService('Page');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $fromWebsiteId = 'SITE-k1s28dky-1dxq-4e1e-951f-307e4b8765db-SITE';
    $toWebsiteId = 'SITE-k2s28dky-1dxq-4e1e-951f-307e4b8765db-SITE';
    $oldPages = $this->getAllPages($fromWebsiteId);

    $result = $this->service->copyPagesToNewWebsite($fromWebsiteId, $toWebsiteId);
    $this->assertTrue($result);
    $newPages = $this->getAllPages($toWebsiteId);

    foreach ($oldPages as $key => $oldPage) {
      $this->assertSame($oldPage->getId(), $newPages[$key]->getId());
      $this->assertNotSame($oldPage->getWebsiteId(), $newPages[$key]->getWebsiteId());
      $this->assertSame($oldPage->getTemplateId(), $newPages[$key]->getTemplateId());
      $this->assertSame($oldPage->getName(), $newPages[$key]->getName());
      $this->assertSame($oldPage->getPageType(), $newPages[$key]->getPageType());
      $this->assertSame($oldPage->getPageAttributes(), $newPages[$key]->getPageAttributes());
    }
  }

  /**
   * @param string $websiteId
   *
   * @return \Cms\Data\Page[]
   */
  private function getAllPages($websiteId)
  {
    $allPages = array();
    $allIds = $this->service->getIdsByWebsiteId($websiteId);
    foreach ($allIds as $pageId) {
      $allPages[$pageId] = $this->service->getById($pageId, $websiteId);
    }
    return $allPages;
  }
}