<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class UpdateTest extends AbstractDaoTestCase
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
  public function test_updateShouldThrowUserNotFoundException()
  {
    // ARRANGE
    $dao = $this->getDao();

    // ACT
    $dao->update('THIS-USER-ID-NOT-EXISTS', array());
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
  public function test_updateShouldThrowReadonlyException()
  {
    // ARRANGE
    $owner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $dao->update($owner['id'], array());
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 