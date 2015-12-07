<?php


namespace Render\InfoStorage\ModuleInfoStorage;


use Cms\Render\InfoStorage\ModuleInfoStorage\ServiceBasedModuleInfoStorage;

class ServiceBasedModuleInfoStorageTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @expectedException PHPUnit_Framework_Error
   */
  public function test_constructor_denies_null()
  {
    new ServiceBasedModuleInfoStorage(null, null);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleClassNameProvider
   */
  public function test_getModuleClassName($siteId, $moduleId, $expectedClassName)
  {
    // Prepare module Service Mock
    $serviceMock = $this->getMock('Cms\Service\Modul', array(), array('Modul'));
    // ACK
    $infoStorage = new ServiceBasedModuleInfoStorage($siteId, $serviceMock);
    $className = $infoStorage->getModuleClassName($moduleId);
    // ASSERT
    $this->assertEquals($expectedClassName, $className,
      "Wrong class name returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleMainClassFilePathProvider
   */  
  public function test_getModuleMainClassFilePath($siteId, $moduleId, $expectedPath)
  {
    // Prepare module Service Mock
    $serviceMock = $this->getMock('Cms\Service\Modul', array(), array('Modul'));
    $serviceMock->expects($this->once())
            ->method('getDataPath')
            ->with($this->equalTo($siteId), $this->equalTo($moduleId))
            ->will($this->returnValue('dataDir'));
    // ACK
    $infoStorage = new ServiceBasedModuleInfoStorage($siteId, $serviceMock);
    $path = $infoStorage->getModuleMainClassFilePath($moduleId);
    // ASSERT
    $this->assertEquals($expectedPath, $path, "Wrong path returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleManifestProvider
   */  
  public function test_getModuleManifest($siteId, $moduleId, $expectedManifest)
  {
    // Prepare module Data Mock
    $moduleMock = $this->getMock('\Cms\Data\Modul');
    $moduleMock->expects($this->once())
            ->method('getManifest')
            ->will($this->returnValue($expectedManifest));
    // Prepare module Service Mock
    $serviceMock = $this->getMock('Cms\Service\Modul', array(), array('Modul'));
    $serviceMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($moduleId), $this->equalTo($siteId))
            ->will($this->returnValue($moduleMock));
    // ACK
    $infoStorage = new ServiceBasedModuleInfoStorage($siteId, $serviceMock);
    $manifest = $infoStorage->getModuleManifest($moduleId);
    // ASSERT
    $this->assertEquals($expectedManifest, $manifest, "Wrong manifest returned");
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider moduleCustomDataProvider
   */
  public function test_getModuleCustomData($siteId, $moduleId, $rawModuleCustomData,
                                           $expectedCustomData)
  {
    // Prepare module Data Mock
    $moduleMock = $this->getMock('\Cms\Data\Modul');
    $moduleMock->expects($this->once())
      ->method('getCustomData')
      ->will($this->returnValue($rawModuleCustomData));
    // Prepare module Service Mock
    $serviceMock = $this->getMock('Cms\Service\Modul', array(), array('Modul'));
    $serviceMock->expects($this->once())
      ->method('getById')
      ->with($this->equalTo($moduleId), $this->equalTo($siteId))
      ->will($this->returnValue($moduleMock));
    // ACK
    $infoStorage = new ServiceBasedModuleInfoStorage($siteId, $serviceMock);
    $actualCustomData = $infoStorage->getModuleCustomData($moduleId);
    // ASSERT
    $this->assertEquals($expectedCustomData, $actualCustomData, "Wrong module custom returned");
  }

  /**
   * @return  array
   */
  public function moduleClassNameProvider()
  {
    return array(
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'MODUL-f39e35c3-fc27-47fa-9164-6852f95e3391-MODUL',
        ServiceBasedModuleInfoStorage::MODULE_NAMESPACE.'MODULf39e35c3fc2747fa91646852f95e3391MODUL',
      ),
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'Slider_rukzuk_com',
        ServiceBasedModuleInfoStorage::MODULE_NAMESPACE.'Slider_rukzuk_com',
      ),
    );
  }

  /**
   * @return  array
   */
  public function moduleMainClassFilePathProvider()
  {
    return array(
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'MODUL-f39e35c3-fc27-47fa-9164-6852f95e3391-MODUL',
        'dataDir/modulf39e35c3fc2747fa91646852f95e3391modul.php',
      ),
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'Slider_rukzuk_com',
        'dataDir/slider_rukzuk_com.php',
      ),
    );
  }

  /**
   * @return  array
   */
  public function moduleManifestProvider()
  {
    return array(
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'MODUL-f39e35c3-fc27-47fa-9164-6852f95e3391-MODUL',
        null,
      ),
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'Slider_rukzuk_com',
        array(
          'id' => 'id@manifest',
          'name' => 'name@manifest',
          'description' => 'description@manifest',
          'version' => 'version@manifest',
          'category' => 'category@manifest',
          'icon' => 'icon@manifest',
          'moduleType' => 'moduleType@manifest',
          'allowedChildModuleType' => 'allowedChildModuleType@manifest',
          'reRenderRequired' => 'reRenderRequired@manifest',
        ),
      ),
    );
  }

  /**
   * @return array
   */
  public function moduleCustomDataProvider()
  {
    $test1module_rawCustomData = (object) array(
      'foo' => 'bar',
      'data_array' => array('test1', 'test2'),
      'data_object' => (object) array(
          'key1' => 'value1',
          'key2' => 'value2',
    ));

    return array(
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'MODUL-f39e35c3-fc27-47fa-9164-6852f95e3391-MODUL',
        null,
        array(),
      ),
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'test_1_module',
        $test1module_rawCustomData,
        json_decode(json_encode($test1module_rawCustomData), true),
      ),
    );
  }
}
