<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot,
    Seitenbau\Registry;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * Komponententest fuer Seitenbau\Screenshot\Type\Systemcallwkhtmltoimage
 *
 * @package      Seitenbau
 * @subpackage   Screenshot\Type\Systemcallwkhtmltoimage
 */

class SystemcallwkhtmltoimageTest extends AbstractTestCase
{
  protected $config = null;
  
  protected $screenOutputDir = 'systemcallwkhtmltoimageTest';
  protected $screenOutput;

  protected function setUp()
  {
    // Parent aufrufen
    parent::setUp();

    $this->config = ConfigHelper::getWritableConfig(Registry::getConfig()->screens);

    $this->config->activ = 'yes';
    $this->config->type = 'Systemcallwkhtmltoimage';

    $this->screenOutput = $this->config->directory . DIRECTORY_SEPARATOR . 
                    $this->screenOutputDir;
    if (!is_dir($this->screenOutput))
    {
      mkdir($this->screenOutput);
    }
  }
  
  protected function tearDown()
  {
    // Verzeichnisse wieder loeschen
    if (is_dir($this->screenOutput))
      $this->removeDir($this->screenOutput);

    // Parent aufrufen
    parent::tearDown();

  }

  /**
   * @test
   * @group library
   */
  public function requiredSettingApplication()
  {
    $screenshotHandler = new Screenshot\Type\Systemcallwkhtmltoimage($this->config->toArray());

    $this->assertInstanceOf('\Seitenbau\Screenshot\Type\Systemcallwkhtmltoimage', $screenshotHandler);

    $specificTestConfig = clone($this->config);
    $commandLabel = Screenshot\Type\Systemcallwkhtmltoimage::CONFIG_OPTION_COMMAND;
    unset($specificTestConfig->systemcallwkhtmltoimage->$commandLabel);

    $this->setExpectedException('\Seitenbau\Screenshot\InvalidConfigException');

    $screenshotHandler = new Screenshot\Type\Systemcallwkhtmltoimage($specificTestConfig->toArray());
  }

  /**
   * @test
   * @group library
   * @requires extension phpwkhtmltox
   */
  public function commandNotUsable()
  {
    $screenshotHandler = new Screenshot\Type\Systemcallwkhtmltoimage($this->config->toArray());

    $this->assertInstanceOf('\Seitenbau\Screenshot\Type\Systemcallwkhtmltoimage', $screenshotHandler);
    $this->assertTrue($screenshotHandler->isUsable());

    $specificTestConfig = clone($this->config);
    $commandLabel = Screenshot\Type\Systemcallwkhtmltoimage::CONFIG_OPTION_COMMAND;
    $specificTestConfig->systemcallwkhtmltoimage->$commandLabel = 'gibtEsNicht';

    $screenshotHandler = new Screenshot\Type\Systemcallwkhtmltoimage($specificTestConfig->toArray());
    $this->assertFalse($screenshotHandler->isUsable());
  }

  /**
   * @test
   * @group library
   * @requires extension phpwkhtmltox
   * @dataProvider successProvider
   */
  public function success($fileTyp)
  {
    $this->markTestSkipped('Disabled because of memory problems');

    $this->config->filetype = $fileTyp;
    $screenshotHandler = new Screenshot\Type\Systemcallwkhtmltoimage($this->config->toArray());
    
    $destinationFile = $this->config->directory 
      . '/systemcallwkhtmltoimageTest/test.'.$fileTyp;
    
    $this->assertFileNotExists($destinationFile);

    $pageId = 'PAGE-033d84e8-cc3e-4a1f-a408-b8fa374af75f-PAGE';
    $websiteId = 'SITE-a344abb2-2a96-4836-b847-1ab0571b1e6d-SITE';
    
    $url = Registry::getBaseUrl()->webhost
      . Registry::getConfig()->server->url 
      . '/render/page/' 
      . Registry::getConfig()->request->parameter 
      . '/' 
      . urlencode('{"websiteid":"' . $websiteId . '","pageid":"'
        . $pageId . '",' . '"mode":"preview"}'
      );
    
    $shootId = time();
    $screenshotHandler->shoot($shootId, $url, $destinationFile);
    
    $expectedFile = Registry::getConfig()->test->output->screenshot->directory 
      . '/systemcallwkhtmltoimage/expectedSuccess.'
      . $fileTyp;

    $this->assertFileExistsWithDelay($destinationFile);
        
    // Die Pruefung kann ausgeschaltet werden -> auf dem Build-System ist
    // keine CMS-Oberflaeche vorhanden, welche fuer diesen Test notwendig waere
    if ($this->config->systemcallwkhtmltoimage->check->files->equal != false)
    {
      $this->assertFileEquals($expectedFile, $destinationFile);
    }
  }

  /**
   * @return  array
   */
  public function successProvider()
  {
    return array(
        array('jpg'),
        array('png'),
    );
  }
  
  /**
   * Loescht ein Verzeichnis samt Inhalt (Dateien und Unterordner)
   *
   * @param string $websiteDir
   */
  private function removeDir($dir)
  {
    if (\is_dir($dir))
    {
      $dirHandle = opendir($dir);
      while(($file = \readdir($dirHandle)) !== false)
      {
        if ($file == '.' || $file == '..') continue;
        $handle = $dir . DIRECTORY_SEPARATOR . $file;
        
        $filetype = filetype($handle);

        if ($filetype == 'dir')
        {
          $this->removeDir($handle);
        }
        else
        {
          unlink($handle);
        }
      }
      closedir($dirHandle);
      rmdir($dir);
    }
  }
  
  /**
   * Prueft, ob die angegebene Datei existiert, die Pruefung wird mehrmals
   * durchgefuehrt, bis die max Wartezeit verstrichen ist
   *
   * @param type $filename
   * @param int $delayInSec
   * @return type
   */
  private function assertFileExistsWithDelay($filename, $delayInSec = 20,
    $errorMessage = null
  ){
    while (!file_exists($filename) && $delayInSec > 0)
    {
      sleep(1);
      $delayInSec--;
    }

    $this->assertFileExists($filename, $errorMessage);
  }  
}