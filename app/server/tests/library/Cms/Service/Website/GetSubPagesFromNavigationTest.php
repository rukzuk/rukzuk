<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Cms\Response,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer getSubPagesFromNavigation Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class GetSubPagesFromNavigationTest extends ServiceTestCase
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
    $website = $this->service->getById('SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE');

    $result = $this->service->getSubPagesFromNavigation(
      'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'PAGE-13c402fe-e201-46c2-9623-61a91723a7bc-PAGE'
    );

    $this->assertInternalType('array', $result);
    $this->assertSame(2, count($result));
  }
}