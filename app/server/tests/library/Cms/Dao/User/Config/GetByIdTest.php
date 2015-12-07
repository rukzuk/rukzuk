<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetByIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\User\UserNotFoundException
   * @expectedExceptionCode 1002
   */
  public function test_getByIdShouldThrowExceptionIfExpectedUserNotExists()
  {
    // ARRANGE
    $dao = $this->getDao();

    // ACT
    $user = $dao->getById('USER-NOT-EXISTS-ID');
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdShouldReturnUserAsExpected()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualUser = $dao->getById($expectedOwner['id']);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\User', $actualUser);
    $this->assertEquals($expectedOwner['id'], $actualUser->getId());
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 