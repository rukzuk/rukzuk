<?php


namespace Cms\Dao\UserStatus\Doctrine;

use Test\Cms\Dao\UserStatus\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * @package Cms\Dao\WebsiteSettings\Doctrine
 *
 * @group UserStatus
 */
class SetLastLogin extends AbstractDaoTestCase
{
  public $sqlFixturesForTestMethod = array(
    'test_setLastLogin_overwriteExistingLastLogin' => array('UserStatus.json'),
  );

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_setLastLogin_success()
  {
    // ARRANGE
    $userId = 'USER-'.__METHOD__;
    $authBackend = 'AUTHBACKEN-'.__METHOD__;
    $expectedLastLogin = new \DateTime('2014-12-13 14:15:16');
    $dao = $this->getDoctrineDao();

    // ACT
    $actualUserState = $dao->setLastLogin($userId, $authBackend, $expectedLastLogin);

    // ASSERT
    $actualLastLogin = $actualUserState->getLastLogin();
    $this->assertEquals($expectedLastLogin->getTimestamp(), $actualLastLogin->getTimestamp());
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_setLastLogin_overwriteExistingLastLogin()
  {
    // ARRANGE
    $userId = 'USER-1';
    $authBackend = 'cms';
    $expectedLastLogin = new \DateTime('2015-12-11 10:09:08');
    $dao = $this->getDoctrineDao();

    // ACT
    $actualUserState = $dao->setLastLogin($userId, $authBackend, $expectedLastLogin);

    // ASSERT
    $actualLastLogin = $actualUserState->getLastLogin();
    $this->assertEquals($expectedLastLogin->getTimestamp(), $actualLastLogin->getTimestamp());
    $this->assertEquals($userId, $actualUserState->getUserId());
    $this->assertEquals($authBackend, $actualUserState->getAuthBackend());
  }

}
 