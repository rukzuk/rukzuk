<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot\Base as ScreenshotBase;
use Seitenbau\Screenshot;
use Seitenbau\Registry;
use Seitenbau\Log;

/**
 * Screenshot Wkhtmltoimage Implementierung
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

class Systemcallwkhtmltoimage extends ScreenshotBase
{
  const CONFIG_SELECTION = 'systemcallwkhtmltoimage';
  const CONFIG_OPTION_COMMAND = 'command';
  const CONFIG_OUTPUTFILE = 'output';

  /**
   * @var string
   */
  private $screenCommand = null;

  /**
   * @var array
   */
  private $options = array();

  /**
   * @var string
   */
  private $outputfile = null;

  /**
   * Erstellt den Screenshot einer URL
   *
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string $destinationFile
   */
  protected function shootImplementation($shootId, $screenshotUrl, $destinationFile)
  {
    $optionParams = '';
    foreach ($this->options as $optionName => $optionValue) {
      $optionParams .= ' --' . $optionName . ' ' . $optionValue;
    }

    $command = $this->screenCommand
      . $optionParams
      . ' "' . (string)$screenshotUrl . '"'
      . ' "' . $destinationFile . '"';

    $outputfile = ($this->existsOutputfile() == null)
                ? '/dev/null'
                : $this->outputfile;

    $diabledOutput = '2>&1';
    if (!isset($this->config['systemcallwkhtmltoimage']['wait']['response']) ||
        $this->config['systemcallwkhtmltoimage']['wait']['response'] != true) {
      $diabledOutput .= ' &';
    }
    
    exec($command . ' > ' . $outputfile . ' ' . $diabledOutput);
    
    return true;
  }

  /**
   * Prueft, ob die Output-Datei vorhanden ist
   * Versucht ggf. die Datei anzulegen
   *
   * @return  boolean
   */
  protected function existsOutputfile()
  {
    if ($this->outputfile != null) {
      if (file_exists($this->outputfile)) {
        return true;
      } else {
        $fileName = strrchr($this->outputfile, '/');
        $filePath = str_replace($fileName, '', $this->outputfile);
        if (file_exists($filePath)) {
          return true;
        } else {
          return \mkdir($filePath);
        }
      }
    }
    return true;
  }

  /**
   * Prueft, ob das Screenshot-Tool einsatzbereit ist
   *
   * @return boolean
   */
  public function isUsable()
  {
    if (parent::isUsable() && $this->isCommandUsable()) {
      return true;
    }
    return false;
  }

  /**
   * Prueft, ob das Command fuer das Screenshot Tool definiert ist und
   * aufgerufen werden kann
   *
   * @return boolean
   */
  private function isCommandUsable()
  {
    $screenCommand = $this->config[self::CONFIG_SELECTION][self::CONFIG_OPTION_COMMAND];
    if (!\file_exists($screenCommand)) {
      Registry::getLogger()->log(
          __METHOD__,
          __LINE__,
          'Screen Command "' . $screenCommand . '" not found',
          Log::NOTICE
      );
      return false;
    }
    $this->screenCommand = $screenCommand;
    return true;
  }

  /**
   * Setzt die Optionen, welche nur fuer wkhtmltoimage gueltig sind
   *
   * @param array $config
   */
  protected function setOptions()
  {
    if (array_key_exists(self::CONFIG_SELECTION, $this->config)) {
      $wlhtmltoimageConfig = $this->config[self::CONFIG_SELECTION];

      if (array_key_exists(Screenshot::OPTIONS, $wlhtmltoimageConfig)) {
        $this->options = $wlhtmltoimageConfig[Screenshot::OPTIONS];
      }

      if (\array_key_exists(self::CONFIG_OUTPUTFILE, $wlhtmltoimageConfig)) {
        $this->outputfile = $wlhtmltoimageConfig[self::CONFIG_OUTPUTFILE];
      }
    }

    if (array_key_exists('httpauthentication', $this->config)) {
      $this->options['password'] = $this->config['httpauthentication']['password'];
      $this->options['username'] = $this->config['httpauthentication']['username'];
    }

    if (array_key_exists('screenWidth', $this->config)) {
      $this->options['width'] = $this->config['screenWidth'];
    }
    if (array_key_exists('screenHeight', $this->config)) {
      $this->options['height'] = $this->config['screenHeight'];
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
      $scwConfig = $config[self::CONFIG_SELECTION];
      // Kommando muss gesetzt werden
      if (!\array_key_exists(self::CONFIG_OPTION_COMMAND, $scwConfig)) {
        throw new Screenshot\InvalidConfigException('Configuration must have a key for "' .
          self::CONFIG_OPTION_COMMAND . '" that defined the application to shoot the screenshot');
      }
    }
  }
}
