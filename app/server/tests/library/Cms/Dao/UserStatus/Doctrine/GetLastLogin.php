<?php


namespace Cms\Dao\UserStatus\Doctrine;

use Test\Cms\Dao\UserStatus\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * @package Cms\Dao\WebsiteSettings\Doctrine
 *
 * @group UserStatus
 */
class GetLastLogin extends AbstractDaoTestCase
{
  public $sqlFixturesForTestMethod = array(
    'test_getLastLogin_success' => array('UserStatus.json'),
  );

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getLastLogin_success()
  {
    // ARRANGE
    $expectedLastLogin = new \DateTime('2015-06-07 08:09:10');
    $dao = $this->getDoctrineDao();

    // ACT
    $actualLastLogin = $dao->getLastLogin();

    // ASSERT
    $this->assertEquals($expectedLastLogin->getTimestamp(), $actualLastLogin->getTimestamp());
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getLastLogin_returnNullIfNoUserStatusExists()
  {
    // ARRANGE
    $dao = $this->getDoctrineDao();

    // ACT
    $actualLastLogin = $dao->getLastLogin();

    // ASSERT
    $this->assertNull($actualLastLogin);
  }
}
 