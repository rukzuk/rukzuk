<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot,
    Seitenbau\Registry;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * Komponententest fÃ¼r Seitenbau\Screenshot\Type\Phpimagegrabwindow
 *
 * @package      Seitenbau
 * @subpackage   Screenshot\Type\Phpimagegrabwindow
 */

class PhpimagegrabwindowTest extends AbstractTestCase
{
  protected $screenshotTypeHandler = null;

  protected $config = null;

  protected function setUp()
  {
    // Parent aufrufen
    parent::setUp();
    
    $this->config = ConfigHelper::getWritableConfig(Registry::getConfig()->screens);

    $this->config->activ = 'yes';
    $this->config->type = 'Phpimagegrabwindow';
    
    $this->screenshotTypeHandler = new Screenshot\Type\Phpimagegrabwindow($this->config->toArray());
  }

  /**
   * @test
   * @group library
   */
  public function requiredSettingApplication()
  {
    $screenshotHandler = new Screenshot\Type\Phpimagegrabwindow($this->config->toArray());
    
    $this->assertInstanceOf('\Seitenbau\Screenshot\Type\Phpimagegrabwindow', $screenshotHandler);
    
    $specificTestConfig = clone($this->config);
    
    unset($specificTestConfig->phpimagegrabwindow->application);

    $this->setExpectedException('\Seitenbau\Screenshot\InvalidConfigException');
    
    $screenshotHandler = new Screenshot\Type\Phpimagegrabwindow($specificTestConfig->toArray());
  }

  /**
   * @test
   * @group library
   * @expectedException \Seitenbau\Screenshot\ScreenshotException
   */
  public function invalidServerSystem()
  {
    $destinationFile = $this->config->directory. '/phpimagegrabwindowTest/test.png';

    $shootId = time();
    $this->screenshotTypeHandler->shoot($shootId, 'http://www.seitenbau.com', $destinationFile);
  }
}