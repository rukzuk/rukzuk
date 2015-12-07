<?php
namespace Cms\Service\User;

use Cms\Service\User as Service,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\User
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

    $this->service = new Service('User');

    $attributes = array(
      'email' => 'service_user_update_test@sbcms.de',
      'lastname' => 'update test',
      'firstname' => 'service user',
      'gender' => 'm',
      'isSuperuser' => false,
      'isDeletable' => true
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
      'lastname' => 'new lastname'
    );

    $this->assertNotSame($attributes['lastname'], $this->testEntry->getLastname());

    $lastUpdateBeforUpdate = $this->testEntry->getLastupdate();

    // kurz warten, damit updateTime geprueft werden kann (sonst ist Zeit zu kurz)
    sleep(1);

    $user = $this->service->edit($this->testEntry->getId(), $attributes);

    $this->assertInstanceOf('\Cms\Data\User', $user);
    
    $this->assertSame($attributes['lastname'], $user->getLastname());

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($user->getLastUpdate());
    $this->assertNotEquals($lastUpdateBeforUpdate, $user->getLastUpdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $user->getLastUpdate());
    $this->assertGreaterThan($currentTime - 2, $user->getLastUpdate());
  }
}