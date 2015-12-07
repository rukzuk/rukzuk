<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;

class CreateTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\User\UserIsReadOnlyException
   * @expactedExceptionCode 1004
   */
  public function test_createShouldThrowReadonlyException()
  {
    // ARRANGE
    $dao = $this->getDao();

    // ACT
    $dao->create(array());
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 