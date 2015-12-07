<?php


namespace Test\Seitenbau\Cms\Dao\Package;

use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Cms\Dao\Package\Filesystem as PackageDao;
use Test\Rukzuk\DataDirectoryHelper;

class WriteableMock extends PackageDao implements DaoMock
{
  static protected $initialSetUp = false;

  static public function setUp()
  {
    if (!self::$initialSetUp) {
      self::$initialSetUp = true;
      self::tearDown();
    }
  }

  static public function tearDown()
  {
    static::_resetCacheInternal();
    DataDirectoryHelper::resetDataDirectory();
  }
}
