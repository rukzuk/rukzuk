<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Test\Seitenbau\ServiceTestCase;

/**
 * Tests fuer GetById Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Website
 */

class GetByIdTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new WebsiteService('Website');

    $attributes = array(
      'name' => 'PHPUnit Test Website - Update',
      'description' => 'website description',
      'navigation' => '[]'
    );
    $this->testEntry = $this->service->create($attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $result = $this->service->getById($this->testEntry->getId());
    
    $this->assertResultSuccess($result);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function idNotFound()
  {
    $result = $this->service->getById('ID-EXISTIERT-NICHT');
  }

  protected function assertResultSuccess($result)
  {
    $this->assertInstanceOf('Cms\Data\Website', $result);
    $this->assertSame($this->testEntry->getId(), $result->getId());
    $this->assertSame($this->testEntry->getDescription(), $result->getDescription());
    $this->assertSame(0, $result->getVersion());
  }

  public function assertResultFalse($result, $expectedData = '')
  {
    $this->assertNull($result);
  }
}