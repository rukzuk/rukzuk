<?php


namespace Cms\Dao\User;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class DaoUserConfigTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_deleteAll_shouldReturnFalse()
  {
    // ARRANGE
    $dao = $this->getDao();

    // ACT
    $result = $dao->deleteAll();

    // ASSERT
    $this->assertInternalType('boolean', $result);
    $this->assertFalse($result);
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 