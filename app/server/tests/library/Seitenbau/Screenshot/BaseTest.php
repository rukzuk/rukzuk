<?php
namespace Seitenbau\Screenshot;

use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\Screenshot,
    Seitenbau\Registry;

/**
 * Testen der Standard-Funktionen der Base-Klasse von Screenshots
 *
 * @package      Seitenbau
 * @subpackage   Screenshot\Base
 */

class BaseTest extends AbstractTestCase
{
  const BACKUP_CONFIG = true;

  private $screenConfig = null;

  protected function setUp()
  {
    // Parent aufrufen
    parent::setUp();

    $this->screenConfig = ConfigHelper::getWritableConfig(Registry::getConfig()->screens);
    $this->screenConfig->activ = 'yes';
    
    // positiv Test mit gueltiger Konfiguration
    $validBase = new Screenshot\Base($this->screenConfig->toArray());
    $this->assertInstanceOf('Seitenbau\Screenshot\Base', $validBase);
  }
  
  /**
   * @test
   * @group library
   */
  public function configOptionDirectory()
  {
    $configOptionName = \Seitenbau\Screenshot::DIRECTORY;
    
    $testConfig = clone($this->screenConfig);
    $testConfig->$configOptionName = $testConfig->$configOptionName . '/optionDirectoryTest';
    
    $tool = new Screenshot\Base($testConfig->toArray());
    $this->assertSame(1, $tool->countCreatedDirectories);
    $this->assertSame($testConfig->$configOptionName, $tool->getDirectory());
    
    $this->assertFileExists($testConfig->$configOptionName);
    
    rmdir($testConfig->$configOptionName);
  }
  
  /**
   * @test
   * @group library
   */
  public function configOptionActive()
  {
    $configOptionName = \Seitenbau\Screenshot::ACTIVATE;
    $testConfig = clone($this->screenConfig);

    // Aktiv = true
    $testConfig->$configOptionName = true;
    $this->checkActiveConfigOption(true, 1, $testConfig);

    $testConfig->$configOptionName = false;
    $this->checkActiveConfigOption(false, 0, $testConfig);
    
    unset($testConfig->$configOptionName);
    $this->assertNull($testConfig->$configOptionName);
    $this->checkActiveConfigOption(true, 1, $testConfig);
  }

  private function checkActiveConfigOption($expectedIsUsableResult,
    $expectedShootCounter, $config
  ){
    $tool = new Screenshot\Base($config->toArray());
    $this->assertInstanceOf('Seitenbau\Screenshot\Base', $tool);
    $this->assertEquals($expectedIsUsableResult, $tool->isUsable());
    $this->assertSame(0, $tool->countShoots);
    $tool->shoot('SHOOT_ID', 'exampleSource', 'exampleDestinationFile');
    $this->assertSame($expectedShootCounter, $tool->countShoots);
  }

  /**
   * @test
   * @group library
   */
  public function configOptionFiletype()
  {
    $configOptionName = \Seitenbau\Screenshot::FILETYPE;
    $testConfig = clone($this->screenConfig);

    $testConfig->$configOptionName = 'png';
    $this->checkFiletypeConfigOption('png', $testConfig);

    $testConfig->$configOptionName = 'jpg';
    $this->checkFiletypeConfigOption('jpg', $testConfig);

    // default jpg
    unset($testConfig->$configOptionName);
    $this->checkFiletypeConfigOption('jpg', $testConfig);
  }

  private function checkFiletypeConfigOption($expectedFiletype, $config)
  {
    $filetypeTool = new Screenshot\Base($config->toArray());
    $this->assertInstanceOf('Seitenbau\Screenshot\Base', $filetypeTool);
    $this->assertSame($expectedFiletype, $filetypeTool->getFiletype());
  }

  /**
   * @test
   * @group library
   */
  public function functionIsUsable()
  {
    $baseTool = new Screenshot\Base($this->screenConfig->toArray());
    $this->assertInstanceOf('Seitenbau\Screenshot\Base', $baseTool);

    $baseTool->setActiv(false);
    $this->assertFalse($baseTool->isUsable());
    $baseTool->setActiv(true);
    $this->assertTrue($baseTool->isUsable());
  }

  /**
   * @test
   * @group library
   */
  public function requiredConfigOptionDirectory()
  {
    $this->checkRequiredConfigOption(\Seitenbau\Screenshot::DIRECTORY);
  }

  /**
   * Prueft, ob ein uebergebene String in der Config gesetzt wurde
   *
   * @param string $optionName
   */
  private function checkRequiredConfigOption($optionName)
  {
    $missingDirectoryConfig = clone($this->screenConfig);
    unset($missingDirectoryConfig->$optionName);
    $this->setExpectedException('\Seitenbau\Screenshot\InvalidConfigException');
    $missingDirectoryBase = new Screenshot\Base($missingDirectoryConfig->toArray());
  }
}