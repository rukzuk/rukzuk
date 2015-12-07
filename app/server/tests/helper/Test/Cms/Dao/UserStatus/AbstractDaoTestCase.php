<?php


namespace Test\Cms\Dao\UserStatus;

use Test\Seitenbau\TransactionTestCase;
use Cms\Dao\UserStatus\Doctrine as DoctrineDao;
use Seitenbau\FileSystem as FS;


abstract class AbstractDaoTestCase extends TransactionTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @return DoctrineDao
   */
  protected function getDoctrineDao()
  {
    return new DoctrineDao();
  }

  /**
   * @return \Cms\Dao\UserStatus\Doctrine|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getDoctrineDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\UserStatus\Doctrine')->getMock();
  }
}