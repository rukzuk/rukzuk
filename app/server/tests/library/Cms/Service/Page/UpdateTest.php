<?php
namespace Cms\Service\Page;

use Cms\Service\Page as PageService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class UpdateTest extends ServiceTestCase
{
  protected $service;

  /**
   * @var \Cms\Data\Page
   */
  protected $testEntry;

  protected $websiteId = 'SITE-s3dfghju-4s5x-dnxj-2ns1-fc42dc78fe50-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PageService('Page');

    $attributes = array(
      'templateid' => '',
      'name' => 'PHPUnit Test Page - Update',
    );
    $this->testEntry = $this->service->create($this->websiteId, $attributes);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $attributes = array(
      'name' => 'new name',
      'pageType' => 'the_new_page_type_id',
      'pageAttributes' => (object) array('newKey' => 'newValue'),
    );

    $this->assertNotEquals($attributes['name'], $this->testEntry->getName());
    $this->assertNotEquals($attributes['pageType'], $this->testEntry->getPageType());
    $this->assertNotEquals($attributes['pageAttributes'], json_decode($this->testEntry->getPageAttributes()));

    $lastUpdateBeforUpdate = $this->testEntry->getLastupdate();

    // kurz warten, damit updateTime geprueft werden kann (sonst ist Zeit zu kurz)
    sleep(1);

    $this->service->update($this->testEntry->getId(), $this->websiteId, $attributes);

    $page = $this->service->getById($this->testEntry->getId(), $this->websiteId);

    $this->assertEquals($attributes['name'], $page->getName());
    $this->assertEquals($attributes['pageType'], $page->getPageType());
    $this->assertEquals($attributes['pageAttributes'], json_decode($page->getPageAttributes()));

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($page->getLastupdate());
    $this->assertNotEquals($lastUpdateBeforUpdate, $page->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $page->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $page->getLastupdate());
  }
}