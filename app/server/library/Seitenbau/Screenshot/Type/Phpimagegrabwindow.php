<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot;
use Seitenbau\Registry;
use Seitenbau\Log;

/**
 * Screenshot PHP Imagegrabwindow Implementierung
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

class Phpimagegrabwindow extends Screenshot\Base
{
  const CONFIG_SELECTION = 'phpimagegrabwindow';

  const CONFIG_OPTION_APPLICATION = 'application';

  private $application = null;

  /**
   * @var array
   */
  private $options = array();

  /**
   * Screenshot wird erstellt
   *
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string $destinationFile
   * @return boolean
   */
  protected function shootImplementation($shootId, $screenshotUrl, $destinationFile)
  {
    if (!$this->isWindowSystem()) {
      throw new Screenshot\ScreenshotException(
          'Invalid server installation for this screenshot-type. ' .
          'Expected server-system is windows'
      );
    }

//    $browser = new \COM($this->application);
//    $handle = $browser->HWND;
//    $browser->Visible = true;
//    $browser->Navigate((string)$screenshotUrl);
//    $im = imagegrabwindow($handle);
//    $browser->Quit();
//    imagepng($im, $this->getDirectory() . '/iesnap.png');
//    imagedestroy($im);
  }

  /**
   * Pruefung, ob das Betriebssystem des Servers ein Windows System ist
   */
  protected function isWindowSystem()
  {
    $result = strpos(php_uname(), 'Windows');
    return ($result == false) ? false : true;
  }
  
  /**
   * Setzt die Optionen, welche nur fuer wkhtmltoimage gueltig sind
   */
  protected function setOptions()
  {
    if (array_key_exists(self::CONFIG_SELECTION, $this->config)) {
      $this->options = $this->config[self::CONFIG_SELECTION];
    }
  }

  /**
   * Prueft, ob die Pflichtfelder in der Config gesetzt sind
   *
   * @param array $config
   */
  protected function checkRequiredOptions(array $config = array())
  {
    parent::checkRequiredOptions($config);

    // Application mit der der Screenshot erstellt wird muss angegeben sein
    if (array_key_exists(self::CONFIG_SELECTION, $config)) {
      $igwConfig = $config[self::CONFIG_SELECTION];
      if (!\array_key_exists(self::CONFIG_OPTION_APPLICATION, $igwConfig)) {
        throw new Screenshot\InvalidConfigException('Configuration must have a key for "' .
          self::CONFIG_OPTION_APPLICATION . '" that defined the application to shoot the screenshot');
      }
      $this->application = $igwConfig[self::CONFIG_OPTION_APPLICATION];
    }
  }
}
