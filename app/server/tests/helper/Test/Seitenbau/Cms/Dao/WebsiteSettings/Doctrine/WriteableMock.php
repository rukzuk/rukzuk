<?php


namespace Test\Seitenbau\Cms\Dao\WebsiteSettings\Doctrine;

use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Cms\Dao\WebsiteSettings\Doctrine as WebsiteSettingsDoctrineDao;

class WriteableMock extends WebsiteSettingsDoctrineDao implements DaoMock
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
  }
}
