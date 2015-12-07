<?php


namespace Test\Cms\Dao\Page;


use Test\Seitenbau\TransactionTestCase;
use Test\Seitenbau\Cms\Dao\Page\WriteableMock as PageWriteableMock;
use Test\Seitenbau\Cms\Dao\Page\ReadonlyMock as PageReadonlyMock;
use Cms\Dao\Page\Doctrine as PageDao;

abstract class AbstractDaoTestCase extends TransactionTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @var bool
   */
  private $resetTestModuleAtTearDown = false;

  protected function setUp()
  {
    parent::setUp();
    $this->resetTestModuleAtTearDown = false;
  }

  public function tearDown()
  {
    if ($this->resetTestModuleAtTearDown) {
      PageWriteableMock::tearDown();
    }
    parent::tearDown();
  }

  /**
   * @param bool $writable
   *
   * @return PageDao
   */
  protected function createPageDao($writable = false)
  {
    if ($writable) {
      $this->resetTestModuleAtTearDown = true;
      return new PageWriteableMock();
    } else {
      return new PageReadonlyMock();
    }
  }
}