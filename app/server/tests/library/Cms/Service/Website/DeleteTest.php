<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * Tests fuer Delete Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class DeleteTest extends ServiceTestCase
{
  protected $service;

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);
    parent::setUp();

    $this->service = new WebsiteService('Website');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $websiteId = 'SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE';

    $this->service->deleteById($websiteId);

    try
    {
      $result = $this->service->getById($websiteId);
    }
    catch (\Exception $e)
    {
      $this->assertSame(602, $e->getCode());
      $this->assertArrayHasKey('id', $e->getData());
      $data = $e->getData();
      $this->assertSame($websiteId, $data['id']);
    }
  }

  /**
   * @test
   * @group library
   */
  public function idNotFound()
  {
    $websiteId = 'SITE-11223344-362a-4b89-a084-53c4696473f8-SITE';

    try
    {
      $this->service->deleteById($websiteId);
    }
    catch (\Exception $e)
    {
      $this->assertSame(602, $e->getCode());
      $this->assertArrayHasKey('id', $e->getData());
      $data = $e->getData();
      $this->assertSame($websiteId, $data['id']);
    }
  }
}