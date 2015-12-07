<?php


namespace Test\Seitenbau\Cms\Dao\Page;

use Cms\Dao\Page\Doctrine as PageDao;
use Test\Seitenbau\Cms\Dao\Iface\DaoMock;

class WriteableMock extends PageDao implements DaoMock
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
