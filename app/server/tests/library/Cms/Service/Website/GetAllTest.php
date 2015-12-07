<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer GetAll Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class GetAllTest extends ServiceTestCase
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
    $result = $this->service->getAll();

    $this->assertResultSuccess($result);
  }
  
  protected function assertResultSuccess($result)
  {
    $this->assertInternalType('array', $result);

    foreach ($result as $entry)
    {
      $this->assertInstanceOf('Cms\Data\Website', $entry);
    }
  }
}