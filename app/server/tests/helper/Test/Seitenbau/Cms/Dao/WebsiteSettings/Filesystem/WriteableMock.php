<?php


namespace Test\Seitenbau\Cms\Dao\WebsiteSettings\Filesystem;

use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Cms\Dao\WebsiteSettings\Filesystem as WebsiteSettingsFilesystemDao;

class WriteableMock extends WebsiteSettingsFilesystemDao implements DaoMock
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
