<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetAllSuperusersTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllSuperusers_returnsEmptyArrayIfNoOwnerAndNoConfigUsersExists()
  {
    // ARRANGE
    ConfigHelper::removeOwner();
    ConfigHelper::removeAllConfigUsers();
    $dao = $this->getDao();

    // ACT
    $allSuperusers = $dao->getAllSuperusers();

    // ASSERT
    $this->assertInternalType('array', $allSuperusers);
    $this->assertEmpty($allSuperusers);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllSuperusers_returnsAllConfigUsers()
  {
    // ARRANGE
    $expectedSuperusers = array(
      ConfigHelper::setOwner(),
      ConfigHelper::addConfigUser(),
    );
    $expectedSuperuserIds = array_column($expectedSuperusers, 'id');
    $dao = $this->getDao();

    // ACT
    $actualSuperusers = $dao->getAllSuperusers();

    // ASSERT
    $this->assertInternalType('array', $actualSuperusers);
    $this->assertCount(count($expectedSuperusers), $actualSuperusers);
    foreach ($actualSuperusers as $nextUser) {
      $this->assertInstanceOf('\Cms\Data\User', $nextUser);
      $this->assertContains($nextUser->getId(), $expectedSuperuserIds);
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllSuperusers_returnsOnlyOwnerIfNoConfigUsersExists()
  {
    // ARRANGE
    ConfigHelper::removeAllConfigUsers();
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualAllSuperusers = $dao->getAllSuperusers();

    // ASSERT
    $this->assertInternalType('array', $actualAllSuperusers);
    $this->assertCount(1, $actualAllSuperusers);
    $actualSuperuser = array_shift($actualAllSuperusers);
    $this->assertInstanceOf('\Cms\Data\User', $actualSuperuser);
    $this->assertContains($actualSuperuser->getId(), $expectedOwner['id']);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllSuperusers_returnsOnlyConfigUsersIfOwnerNotExists()
  {
    // ARRANGE
    ConfigHelper::removeAllConfigUsers();
    ConfigHelper::removeOwner();
    $expectedSuperusers = array(
      ConfigHelper::addConfigUser(),
      ConfigHelper::addConfigUser(),
      ConfigHelper::addConfigUser(),
    );
    $expectedSuperuserIds = array_column($expectedSuperusers, 'id');
    $dao = $this->getDao();

    // ACT
    $actualSuperusers = $dao->getAllSuperusers();

    // ASSERT
    $this->assertInternalType('array', $actualSuperusers);
    $this->assertCount(count($expectedSuperusers), $actualSuperusers);
    foreach ($actualSuperusers as $nextUser) {
      $this->assertInstanceOf('\Cms\Data\User', $nextUser);
      $this->assertContains($nextUser->getId(), $expectedSuperuserIds);
    }
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 