<?php


namespace Test\Seitenbau\Cms\Dao\Website;

use Cms\Dao\Website\Doctrine as WebsiteDao;
use Test\Seitenbau\Cms\Dao\Iface\DaoMock;


class WriteableMock extends WebsiteDao implements DaoMock
{
  static protected $initialeSetUp = false;

  static public function setUp()
  {
    if (!self::$initialeSetUp) {
      self::$initialeSetUp = true;
      self::tearDown();
    }
  }
  
  static public function tearDown()
  {
  }
}
