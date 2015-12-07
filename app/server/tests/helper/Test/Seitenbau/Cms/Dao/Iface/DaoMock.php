<?php
namespace Test\Seitenbau\Cms\Dao\Iface;

/**
 * dao mock marker interface for writeable mocks
 *
 * @package      Test
 * @subpackage   Dao
 */
interface DaoMock
{
  /**
   */
  static public function setUp();

  /**
   */
  static public function tearDown();
}