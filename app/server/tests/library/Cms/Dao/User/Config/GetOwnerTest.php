<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetOwnerTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getOwnerShouldReturnOwnerAsExpected()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualOwner = $dao->getOwner();

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\User', $actualOwner);
    $this->assertEquals($expectedOwner['id'], $actualOwner->getId());
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
  public function test_getOwnerShouldThrowExceptionIfOwnerNotExists()
  {
    // ARRANGE
    ConfigHelper::removeOwner();
    $dao = $this->getDao();

    // ACT
    $dao->getOwner();
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 