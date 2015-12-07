<?php


namespace Test\Seitenbau\Cms\Dao\PageType;

use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Cms\Dao\PageType\Filesystem as PageTypeDao;

class WriteableMock extends PageTypeDao implements DaoMock
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
  }
}
