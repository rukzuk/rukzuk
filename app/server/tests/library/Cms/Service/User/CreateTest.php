<?php
namespace Cms\Service\User;

use Cms\Service\User as Service,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * CreateTest for User
 *
 * @package      Application
 * @subpackage   Controller
 */

class CreateTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Template
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new Service('User');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $attributes = array(
      'email' => 'service_user_create_test@sbcms.de',
      'lastname' => 'create test',
      'firstname' => 'service user',
      'gender' => 'm',
      'isSuperuser' => false,
      'isDeletable' => true
    );
    $newUser = $this->service->create($attributes);

    $this->assertInstanceOf('Cms\Data\User', $newUser);
    $this->assertSame($attributes['email'], $newUser->getEmail());
    $this->assertSame($attributes['lastname'], $newUser->getLastname());
    $this->assertSame($attributes['firstname'], $newUser->getFirstname());
    $this->assertSame($attributes['gender'], $newUser->getGender());
    $this->assertSame($attributes['isSuperuser'], $newUser->isSuperuser());
    $this->assertSame($attributes['isDeletable'], $newUser->isDeletable());

    $uuidValidator = new UniqueIdValidator(
      \Orm\Data\User::ID_PREFIX,
      \Orm\Data\User::ID_SUFFIX
    );
    $this->assertTrue($uuidValidator->isValid($newUser->getId()));

    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($newUser->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $newUser->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $newUser->getLastupdate());
  }
}