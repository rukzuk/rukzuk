<?php
namespace Cms\Service\Template;

use Cms\Service\Template as Service,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\Template
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class UpdateTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->service = new Service('Template');

    $attributes = array(
      'name' => 'PHPUnit Test Template - Update'
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
      'name' => 'new name'
    );

    $this->assertNotSame($attributes['name'], $this->testEntry->getName());

    $lastUpdateBeforUpdate = $this->testEntry->getLastupdate();

    // kurz warten, damit updateTime geprueft werden kann (sonst ist Zeit zu kurz)
    sleep(1);

    $this->service->update($this->testEntry->getId(), $this->websiteId, $attributes);

    $page = $this->service->getById($this->testEntry->getId(), $this->websiteId);

    $this->assertSame($attributes['name'], $page->getName());

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($page->getLastupdate());
    $this->assertNotEquals($lastUpdateBeforUpdate, $page->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $page->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $page->getLastupdate());
  }
}