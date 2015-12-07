<?php


namespace Cms\Dao\Website\Doctrine;

use Cms\Dao\Website\Doctrine as WebsiteDao;
use Test\Seitenbau\TransactionTestCase;

class CopyTest  extends TransactionTestCase
{
  protected $sqlFixtures = array('library_Cms_Dao_Website_Doctrine_CopyTest.json');

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
  public function test_copyShouldGenerateNewShortIdAsExpected()
  {
    // ARRANGE
    $existingShortId = 'copy';
    $daoMock = $this->getMockBuilder(get_class($this->dao))
      ->setMethods(array('createRandomString'))
      ->getMock();
    $daoMock->expects($this->atLeastOnce())
      ->method('createRandomString')
      ->will($this->returnCallback(function () use ($existingShortId) {
          static $count = 0;
          $count++;
          if ($count <= 1) {
            return $existingShortId;
          } else {
            return $this->createRandomString(1, 4);
          }
        }));

    // ACT
    /** @var $website \Orm\Entity\Website */
    $website = $daoMock->copy('SITE-dao0webs-ite0-doct-rine-copytest01-SITE', array(
      'name' => 'PHPUnit Test: '.__CLASS__.'::'.__METHOD__)
    );

    // ASSERT
    $this->assertInstanceOf('\Orm\Entity\Website', $website);
    $this->assertNotEmpty($website->getShortId());
    $this->assertNotEquals($existingShortId, $website->getShortId());
  }

  /**
   * @param integer $minDigits
   * @param integer $maxDigits
   *
   * @return string
   */
  protected function createRandomString($minDigits, $maxDigits)
  {
    return base_convert(mt_rand(pow(36, $minDigits-1), pow(36, $maxDigits)-1), 10, 36);
  }
}
 