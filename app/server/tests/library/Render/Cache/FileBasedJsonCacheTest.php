<?php
namespace Render\Cache;

use org\bovigo\vfs\vfsStream;

/**
 * Class FileBasedJsonCacheTest
 * @package Render\Cache
 */
class FileBasedJsonCacheTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var  vfsStreamDirectory
   */
  private $root;

  /**
   * set up test environment
   */
  public function setUp()
  {
    $this->root = vfsStream::setup('virtualTestCacheDir');
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_setUnitValueCreatesDirForUnit()
  {
    // ARRANGE
    $unit = new \Render\Unit('unit-id', 'unit_module_id', 'unit name');

    $fileCache = new FileBasedJsonCache(vfsStream::url('virtualTestCacheDir'));

    // ACT
    $fileCache->setUnitValue($unit, 'key', array('value' => '#test'));

    // ASSERT
    $this->assertTrue($this->root->hasChild('unit-id'));
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_setAndGetUnitValue()
  {
    // ARRANGE
    $unit = new \Render\Unit('unit-id', 'unit_module_id', 'unit name');

    $fileCache = new FileBasedJsonCache(vfsStream::url('virtualTestCacheDir'));

    // ACT
    $input = array('value' => '#test', 'blubb' => array('nested'));
    $fileCache->setUnitValue($unit, 'key', $input);
    $output = $fileCache->getUnitValue($unit, 'key');

    // ASSERT
    $this->assertEquals($output, $input);
  }

}
