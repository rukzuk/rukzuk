<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class DeleteTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\User\UserNotFoundException
   * @expactedExceptionCode 1006
   */
  public function test_deleteShouldThrowUserNotFoundException()
  {
    // ARRANGE
    $dao = $this->getDao();

    // ACT
    $dao->delete('THIS-USER-ID-NOT-EXISTS');
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\User\UserIsReadOnlyException
   * @expactedExceptionCode 1006
   */
  public function test_deleteShouldThrowReadonlyException()
  {
    // ARRANGE
    $owner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $dao->delete($owner['id']);
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 