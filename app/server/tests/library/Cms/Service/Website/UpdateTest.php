<?php
namespace Cms\Service\Website;

use Cms\Service\Website as Service,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\Website
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class UpdateTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new Service('Website');

    $attributes = array(
      'name' => 'PHPUnit Test Website - Update'
    );
    $this->testEntry = $this->service->create($attributes);
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

    $this->service->update($this->testEntry->getId(), $attributes);

    $page = $this->service->getById($this->testEntry->getId());

    $this->assertSame($attributes['name'], $page->getName());

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($page->getLastupdate());
    $this->assertNotEquals($lastUpdateBeforUpdate, $page->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $page->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $page->getLastupdate());
  }
}