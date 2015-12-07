<?php


namespace Render\InfoStorage\ModuleInfoStorage;


class ArrayModuleInfoStorageTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleInfoProvider
   */  
  public function test_getModuleClassName($moduleId, $expectedModuleInfo)
  {
    $moduleInfos = array($moduleId => $expectedModuleInfo);

    // ACK
    $infoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    $className = $infoStorage->getModuleClassName($moduleId);
    // ASSERT
    $this->assertEquals($expectedModuleInfo['mainClassName'], $className, "Wrong class name returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleInfoProvider
   */  
  public function test_getModuleMainClassFilePath($moduleId, $expectedModuleInfo)
  {
    $moduleInfos = array($moduleId => $expectedModuleInfo);
    $moduleBasePath = '/dataDir';

    // ACK
    $infoStorage = new ArrayBasedModuleInfoStorage($moduleInfos, $moduleBasePath);
    $path = $infoStorage->getModuleMainClassFilePath($moduleId);
    // ASSERT
    $expectedPath = $moduleBasePath.'/'.$expectedModuleInfo['mainClassFilePath'];
    $this->assertEquals($expectedPath, $path, "Wrong path returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleInfoProvider
   */  
  public function test_getModuleManifest($moduleId, $expectedModuleInfo)
  {
    $moduleInfos = array($moduleId => $expectedModuleInfo);

    // ACK
    $infoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    $manifest = $infoStorage->getModuleManifest($moduleId);
    // ASSERT
    $this->assertEquals($expectedModuleInfo['manifest'], $manifest, "Wrong manifest returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleInfoProvider
   */
  public function test_getModuleCustomData($moduleId, $expectedModuleInfo)
  {
    $moduleInfos = array($moduleId => $expectedModuleInfo);

    // ACK
    $infoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    $manifest = $infoStorage->getModuleManifest($moduleId);
    $actualCustomData = $infoStorage->getModuleCustomData($moduleId);
    // ASSERT
    $this->assertEquals($expectedModuleInfo['customData'], $actualCustomData, "Wrong module custom returned");
  }

  /**
   * @return  array
   */
  public function moduleInfoProvider()
  {
    return array(
      array(
        'MODUL-f39e35c3-fc27-47fa-9164-6852f95e3391-MODUL',
        array(
          'mainClassName'     => '\\Rukzuk\\Modules\\MODULf39e35c3fc2747fa91646852f95e3391MODUL',
          'mainClassFilePath' => 'dataDir/modulf39e35c3fc2747fa91646852f95e3391modul.module.php',
          'manifest'          => null,
          'customData'        => null,
        ),
      ),
      array(
        'TestClass',
        array(
          'mainClassName'     => '\\Rukzuk\\Modules\\TestClass',
          'mainClassFilePath' => 'dataDir/testClass.module.php',
          'manifest'          => array(
            'id'                      => 'id@manifest',
            'name'                    => 'name@manifest',
            'description'             => 'description@manifest',
            'version'                 => 'version@manifest',
            'category'                => 'category@manifest',
            'icon'                    => 'icon@manifest',
            'moduleType'              => 'moduleType@manifest',
            'allowedChildModuleType'  => 'allowedChildModuleType@manifest',
            'reRenderRequired'        => 'reRenderRequired@manifest',
          ),
          'customData'                => array(
            'foo'                       => 'bar',
            'data_array'                => array('test1', 'test2'),
            'data_object'               => array(
              'key1'                      => 'value1',
              'key2'                      => 'value2',
            ),
          ),
        ),
      ),
    );
  }
}
