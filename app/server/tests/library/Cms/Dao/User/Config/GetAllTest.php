<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetAllTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsEmptyArrayIfOwnerNotExists()
  {
    // ARRANGE
    ConfigHelper::removeOwner();
    $dao = $this->getDao();

    // ACT
    $allUsers = $dao->getAll();

    // ASSERT
    $this->assertInternalType('array', $allUsers);
    $this->assertEmpty($allUsers);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsOwnerAndConfigUsers()
  {
    // ARRANGE
    $expectedUsers = array(
      ConfigHelper::setOwner(),
      ConfigHelper::addConfigUser(),
      ConfigHelper::addConfigUser(),
      ConfigHelper::addConfigUser(),
    );
    $expectedUserIds = array_column($expectedUsers, 'id');

    $dao = $this->getDao();

    // ACT
    $allUsers = $dao->getAll();

    // ASSERT
    $this->assertInternalType('array', $allUsers);
    $this->assertCount(count($expectedUserIds), $allUsers);
    foreach ($allUsers as $nextUser) {
      $this->assertInstanceOf('\Cms\Data\User', $nextUser);
      $this->assertContains($nextUser->getId(), $expectedUserIds);
      $this->assertTrue($nextUser->isReadonly());
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
 