<?php
namespace Test\Seitenbau\Cms\Dao\Module;

use Cms\Dao\Module\Filesystem as ModuleDao;
use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Test\Rukzuk\DataDirectoryHelper;

/**
 * dao mock for writeable modules
 *
 * @package      Test
 * @subpackage   Dao
 */
class WriteableMock extends ModuleDao implements DaoMock
{
  static protected $initialeSetUp = false;
  
  static public function setUp()
  {
    if (!self::$initialeSetUp) {
      self::$initialeSetUp = true;
      self::restoreDataDir();
    }
  }
  
  static public function tearDown()
  {
    self::restoreDataDir();
  }
  
  static protected function restoreDataDir()
  {
    static::_resetCacheInternal();
    DataDirectoryHelper::resetDataDirectory();
  }
}
