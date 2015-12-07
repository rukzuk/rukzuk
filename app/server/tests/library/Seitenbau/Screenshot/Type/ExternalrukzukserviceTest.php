<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Registry,
    Test\Seitenbau\Screenshot\Type\ExternalrukzukserviceMock as ExternalrukzukserviceMock;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * Komponententest fuer Seitenbau\Screenshot\Type\Externalrukzukservice
 *
 * @package      Seitenbau
 * @subpackage   Screenshot\Type\Externalrukzukservice
 */

class ExternalrukzukserviceTest extends AbstractTestCase
{
  const DUMMY_SERVICE_HOST    = 'http://test.screenshot.service.rukzuk.net';
  const DUMMY_ENPOINT_STATUS  = '/pageshooter/status/';
  const DUMMY_ENPOINT_TRIGGER = '/pageshooter/shoot/';
  const DUMMY_TOKEN           = 'TEST_TOKEN';
  const DUMMY_URL             = 'http://test.screenshot.url.rukzuk.net';
  
  protected $config = null;
  
  protected $screenOutputDir = 'externalrukzukserviceTest';
  protected $screenOutput;

  protected function setUp()
  {
    // Parent aufrufen
    parent::setUp();

    $this->config = ConfigHelper::getWritableConfig(Registry::getConfig()->screens);
    $this->config->externalrukzukservice->hosts = array(self::DUMMY_SERVICE_HOST);
    $this->config->externalrukzukservice->endpoint = array(
      'status'  => array(
        'url'           => self::DUMMY_ENPOINT_STATUS,
        'timeout'       => 100,
        'maxRedirects'  => 100,
      ),
      'trigger' => array(
        'url'           => self::DUMMY_ENPOINT_TRIGGER,
        'timeout'       => 100,
        'maxRedirects'  => 100,
      ),
    );
    $this->config->externalrukzukservice->options = array(
      'status'  => array('foo' => 'status'),
      'trigger' => array('foo' => 'trigger'),
    );

    $this->config->activ = 'yes';
    $this->config->type = 'Externalrukzukservice';

    $this->screenOutput = $this->config->directory . DIRECTORY_SEPARATOR . 
                    $this->screenOutputDir;
    if (!is_dir($this->screenOutput))
    {
      mkdir($this->screenOutput);
    }
    
    ExternalrukzukserviceMock::setTestToken(self::DUMMY_TOKEN);
  }
  
  protected function tearDown()
  {
    // Reset Test Data
    ExternalrukzukserviceMock::clearTestData();
            
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
    // no hosts
    $exceptionOccur = false;
    try {
      $invalidConfig = $this->config->toArray();
      unset($invalidConfig['externalrukzukservice']['hosts']);
      $screenshotHandler = new ExternalrukzukserviceMock($invalidConfig);
    } catch (\Seitenbau\Screenshot\InvalidConfigException $expected) {
      $exceptionOccur = true; 
    }
    $this->assertTrue($exceptionOccur, 'Expected exception \Seitenbau\Screenshot\InvalidConfigException"');

    // no endpoint
    $exceptionOccur = false;
    try {
      $invalidConfig = $this->config->toArray();
      unset($invalidConfig['externalrukzukservice']['endpoint']);
      $screenshotHandler = new ExternalrukzukserviceMock($invalidConfig);
    } catch (\Seitenbau\Screenshot\InvalidConfigException $expected) {
      $exceptionOccur = true; 
    }
    $this->assertTrue($exceptionOccur, 'Expected exception \Seitenbau\Screenshot\InvalidConfigException"');

    // no status endpoint
    $exceptionOccur = false;
    try {
      $invalidConfig = $this->config->toArray();
      unset($invalidConfig['externalrukzukservice']['endpoint']['status']);
      $screenshotHandler = new ExternalrukzukserviceMock($invalidConfig);
    } catch (\Seitenbau\Screenshot\InvalidConfigException $expected) {
      $exceptionOccur = true; 
    }
    $this->assertTrue($exceptionOccur, 'Expected exception \Seitenbau\Screenshot\InvalidConfigException"');

    // no trigger endpoint
    $exceptionOccur = false;
    try {
      $invalidConfig = $this->config->toArray();
      unset($invalidConfig['externalrukzukservice']['endpoint']['trigger']);
      $screenshotHandler = new ExternalrukzukserviceMock($invalidConfig);
    } catch (\Seitenbau\Screenshot\InvalidConfigException $expected) {
      $exceptionOccur = true; 
    }
    $this->assertTrue($exceptionOccur, 'Expected exception \Seitenbau\Screenshot\InvalidConfigException"');
  }

  /**
   * @test
   * @group library
   */
  public function screenshotShouldShootPageAsExpected()
  {
    $config = $this->config->toArray();
    $screenshotHandler = new ExternalrukzukserviceMock($config);

    $destinationFile = $this->screenOutput . DIRECTORY_SEPARATOR . 'test.mock';
    $this->assertFileNotExists($destinationFile);

    $shootId = time();
    $expectedCallData = array(
      'responseCode'  => 200,
      'host'          => self::DUMMY_SERVICE_HOST,
      'request'       => array(
        'url'           => self::DUMMY_ENPOINT_TRIGGER,
        'timeout'       => 100,
        'maxRedirects'  => 100,
        'params'        => array(
          'id'            => $shootId,
          'token'         => self::DUMMY_TOKEN,
          'url'           => self::DUMMY_URL,
          'foo'           => 'trigger',
        ),
      ),
    );
    
    ExternalrukzukserviceMock::setTestResponseCodes(array(404, 200));
    $result = $screenshotHandler->shoot($shootId, self::DUMMY_URL, $destinationFile);
    $this->assertTrue($result);
    
    $this->assertFileExists($destinationFile);
    
    $actualCallData = json_decode(file_get_contents($destinationFile), true);
    
    $this->assertEquals($expectedCallData, $actualCallData);
  }

  /**
   * @test
   * @group library
   */
  public function screenshotShouldReturnPageOnStatusSuccessAsExpected()
  {
    $config = $this->config->toArray();
    $screenshotHandler = new ExternalrukzukserviceMock($config);

    $destinationFile = $this->screenOutput . DIRECTORY_SEPARATOR . 'test.mock';
    $this->assertFileNotExists($destinationFile);

    $shootId = time();
    $expectedCallData = array(
      'responseCode'  => 200,
      'host'          => self::DUMMY_SERVICE_HOST,
      'request'       => array(
        'url'           => self::DUMMY_ENPOINT_STATUS,
        'timeout'       => 100,
        'maxRedirects'  => 100,
        'params'        => array (
          'id'            => $shootId,
          'token'         => self::DUMMY_TOKEN,
          'foo'           => 'status',
        ),
      ),
    );
    
    ExternalrukzukserviceMock::setTestResponseCodes(array(200));
    $result = $screenshotHandler->shoot($shootId, self::DUMMY_URL, $destinationFile);
    $this->assertTrue($result);
    
    $this->assertFileExists($destinationFile);
    
    $actualCallData = json_decode(file_get_contents($destinationFile), true);
    
    $this->assertEquals($expectedCallData, $actualCallData);
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