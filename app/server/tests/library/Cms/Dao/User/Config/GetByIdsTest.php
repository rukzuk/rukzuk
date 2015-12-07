<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetByIdsTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdsShouldReturnUsersAsExpected()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualUsers = $dao->getByIds(array($expectedOwner['id']));

    // ASSERT
    $this->assertInternalType('array', $actualUsers);
    $this->assertCount(1, $actualUsers);
    /** @var $actualOwner \Cms\Data\User */
    $actualUser = array_shift($actualUsers);
    $this->assertInstanceOf('\Cms\Data\User', $actualUser);
    $this->assertEquals($expectedOwner['id'], $actualUser->getId());
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\User\UserNotFoundException
   * @expectedExceptionCode 1002
   */
  public function test_getByIdsShouldThrowExceptionIfAtLeastOneUserNotExists()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualUsers = $dao->getByIds(array(
      $expectedOwner['id'], 'USER-NOT-EXISTS-ID'));
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 