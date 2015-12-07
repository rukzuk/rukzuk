<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer GetById Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class GetByIdTest extends ServiceTestCase
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
      'name' => 'PHPUnit Test Page - GetById - name',
      'description' => 'PHPUnit Test Page - GetById - description',
      'mediaid' => 'PHPUnit Test Page - GetById - mediaId',
      'pageType' => 'the_page_type_id',
      'pageAttributes' => (object)array(
        'foo' => 'bar',
        'myObject' => new \stdClass(),
        'myArray' => array(),
      ),
    );
    $this->testEntry = $this->service->create($this->websiteId, $attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $result = $this->service->getById(
      $this->testEntry->getId(),
      $this->testEntry->getWebsiteId()
    );

    $this->assertResultSuccess($result);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function idNotFound()
  {
    $id = 'TEST-123';
    $result = $this->service->getById($id, $this->testEntry->getWebsiteId());
  }

  /**
   * @param \Cms\Data\Page $result
   */
  protected function assertResultSuccess($result)
  {
    $this->assertInstanceOf('Cms\Data\Page', $result);
    $this->assertEquals($this->testEntry->toArray(), $result->toArray());
  }

  public function assertResultFalse($result)
  {
    $this->assertNull($result);
  }
}