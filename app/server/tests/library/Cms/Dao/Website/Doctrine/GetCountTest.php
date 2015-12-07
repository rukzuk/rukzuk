<?php


namespace Cms\Dao\Website\Doctrine;

use Cms\Dao\Website\Doctrine as WebsiteDao;
use Test\Seitenbau\TransactionTestCase;

class GetCountTest  extends TransactionTestCase
{
  /**
   * @var WebsiteDao
   */
  protected $dao;

  protected function setUp()
  {
    parent::setUp();

    $this->dao = new WebsiteDao();
  }

  /**
   * @test
   * @group library
   */
  public function test_getCountReturnNumberAsExpected()
  {
    // ARRANGE
    $expectedCount = $this->dao->getCount() + 1;

    // ACT
    $this->dao->create(array('name' => 'PHPUnit Test Website dao - getCount4quota +2'));
    $actualCount = $this->dao->getCount();

    // ASSERT
    $this->assertEquals($expectedCount, $actualCount);
  }
}
 