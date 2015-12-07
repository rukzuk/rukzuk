<?php
namespace Cms\Business\Reparse;

use Cms\Business,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer ReparsePages Funktionalitaet Cms\Service\Reparse
 *
 * @package      Cms
 * @subpackage   Service\Peparse
 */

class ReparseWebsiteTest extends ServiceTestCase
{
  protected $business;

  protected function setUp()
  {
    parent::setUp();
    $this->business = new Business\Reparse('Reparse');
  }

  /**
   * @test
   * @group library
   */
  public function reparseWebsiteSuccess()
  {
    $websiteId = 'SITE-5sz2bve3-1cfg-4836-b847-1ab0571b1e6d-SITE';

    $pageService = $this->business->getService('Page');
    $allPageIds = $pageService->getIdsByWebsiteId($websiteId);

    foreach ($allPageIds as $pageId) {
      $page = $pageService->getById($pageId, $websiteId);
      $pageContent[$page->getId()] = $page->getContent();
      $templateContent[$page->getId()] = $page->getTemplatecontent();
    }

    \Cms\ExceptionStack::reset();
    $reparsedPageIds = $this->business->reparseWebsite($websiteId);
    $this->assertEquals(0, count(\Cms\ExceptionStack::getExceptions()), 'Exception(s) occured');

    $this->assertSame(2, count($reparsedPageIds));

    foreach ($allPageIds as $pageId) {
      $page = $pageService->getById($pageId, $websiteId);
      $this->assertContains($page->getId(), $reparsedPageIds);
      $this->assertNotSame($pageContent[$page->getId()], $page->getContent());
      $this->assertNotSame($templateContent[$page->getId()], $page->getTemplatecontent());
    }
  }
}