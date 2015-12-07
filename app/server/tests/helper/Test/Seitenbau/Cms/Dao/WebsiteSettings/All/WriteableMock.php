<?php


namespace Test\Seitenbau\Cms\Dao\WebsiteSettings\All;

use Test\Seitenbau\Cms\Dao\Iface\DaoMock;
use Cms\Dao\WebsiteSettings\All as WebsiteSettingsAllDao;

class WriteableMock extends WebsiteSettingsAllDao implements DaoMock
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
    \Test\Seitenbau\Cms\Dao\WebsiteSettings\Doctrine\WriteableMock::tearDown();
    \Test\Seitenbau\Cms\Dao\WebsiteSettings\Filesystem\WriteableMock::tearDown();
  }

  public function phpunit_setDoctrineDao($daoDoctrine)
  {
    $this->daoDoctrine = $daoDoctrine;
  }

  public function phpunit_setFilesystemDao($daoFilesystem)
  {
    $this->daoFilesystem = $daoFilesystem;
  }
}
