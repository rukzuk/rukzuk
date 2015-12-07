<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot;
use Seitenbau\Registry;
use Seitenbau\Log;

/**
 * Screenshot Wkhtmltoimage Implementierung
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

class Wkhtmltoimage extends Screenshot\Base
{
  const CONFIG_WKHTMLTOIMAGE_SELECTION = 'wkhtmltoimage';

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
    $this->options['out'] = $destinationFile;
    $this->options['in'] = (string)$screenshotUrl;
    $this->options['fmt'] = $this->getFiletype();

    $result = wkhtmltox_convert('image', $this->options);

    return $result;
  }

  /**
   * Setzt die Optionen, welche nur fuer wkhtmltoimage gueltig sind
   */
  protected function setOptions()
  {
    if (array_key_exists(self::CONFIG_WKHTMLTOIMAGE_SELECTION, $this->config)) {
      $wlhtmltoimageConfig = $this->config[self::CONFIG_WKHTMLTOIMAGE_SELECTION];

      if (array_key_exists(Screenshot::OPTIONS, $wlhtmltoimageConfig)) {
        $this->options = $wlhtmltoimageConfig[Screenshot::OPTIONS];
      }
    }

    if (array_key_exists('httpauthentication', $this->config)) {
      $this->options['password'] = $this->config['httpauthentication']['password'];
      $this->options['username'] = $this->config['httpauthentication']['username'];
    }
  }
}
