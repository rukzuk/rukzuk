<?php


namespace Test\Cms\Dao\Module;


use Cms\Dao\Base\SourceItem;
use Seitenbau\Registry;
use Test\Seitenbau\TransactionTestCase;
use Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
use Test\Seitenbau\Cms\Dao\Module\ReadonlyMock as ModuleReadonlyMock;
use Cms\Dao\Module\Filesystem as ModuleDao;
use Seitenbau\FileSystem as FS;

abstract class AbstractFilesystemTestCase extends TransactionTestCase
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
      ModuleWriteableMock::tearDown();
    }
    parent::tearDown();
  }

  /**
   * @param bool $writable
   *
   * @return ModuleDao
   */
  protected function createModuleDao($writable = false)
  {
    if ($writable) {
      $this->resetTestModuleAtTearDown = true;
      return new ModuleWriteableMock(true);
    } else {
      return new ModuleReadonlyMock(true);
    }
  }
}