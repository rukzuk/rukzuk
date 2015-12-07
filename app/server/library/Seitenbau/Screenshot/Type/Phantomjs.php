<?php
namespace Seitenbau\Screenshot\Type;

use Seitenbau\Screenshot\Base as ScreenshotBase;
use Seitenbau\Screenshot;
use Seitenbau\Registry;
use Seitenbau\Log;

/**
 * Screenshot PhantomJS impl
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */
class Phantomjs extends ScreenshotBase
{
  const CONFIG_SELECTION = 'phantomjs';
  const CONFIG_OPTION_COMMAND = 'command';
  const CONFIG_OPTION_JSSCRIPT = 'jsScript';
  const CONFIG_PARAMS = 'params';
  const CONFIG_OUTPUTFILE = 'output';
  const CONFIG_WAIT_RESPONSE = 'wait';

  /**
   * @var string
   */
  private $screenCommand = null;

  /**
   * @var string
   */
  private $jsScript = null;

  /**
   * @var int
   */
  private $width = 1024;

  /**
   * @var int
   */
  private $height = 768;

  /**
   * @var array
   */
  private $params = array();

  /**
   * @var string
   */
  private $outputfile = null;

  /**
   * @var bool
   */
  private $wait = false;

  /**
   * @param string                       $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string                       $destinationFile
   *
   * @return bool
   */
  protected function shootImplementation($shootId, $screenshotUrl, $destinationFile)
  {
    // TODO: check for valid url (try to prevent injection - might be possible with valid urls)

    $paramString = '';
    foreach ($this->params as $paramName => $paramValue) {
      $paramString .= ' --' . $paramName . '=' . $paramValue;
    }

    $command = sprintf(
      '%s %s "%s" "%s" "%s" %d %d',
      $this->screenCommand,
      $paramString,
      $this->jsScript,
      $screenshotUrl->get(true),
      $destinationFile,
      $this->width,
      $this->height
    );

    return $this->execCommand($command, $this->getOutputFile(), $this->wait);
  }

  /**
   * @param string $command
   * @param string $outputFile
   * @param bool   $wait
   *
   * @return bool
   */
  protected function execCommand($command, $outputFile, $wait)
  {
    $execCommand = $command . ' > ' .  $outputFile . ' 2>&1';

    if (!$wait) {
      $execCommand .= ' &';
    }

    Registry::getLogger()->log(
      __METHOD__,
      __LINE__,
      'PhantomJS exec call: ' . $execCommand,
      Log::DEBUG
    );

    @exec($execCommand);
    return true;
  }
  /**
   * @return string
   */
  protected function getOutputFile()
  {
    if ($this->existsOutputFile()) {
      return $this->outputfile;
    } else {
      return '/dev/null';
    }
  }

  /**
   * @return bool
   */
  protected function existsOutputFile()
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
   * @return bool
   */
  public function isUsable()
  {
    if (parent::isUsable() && $this->isCommandUsable()) {
      return true;
    }
    return false;
  }

  /**
   * @return bool
   */
  private function isCommandUsable()
  {
    if (file_exists($this->screenCommand)) {
      return true;
    }
    Registry::getLogger()->log(
        __METHOD__,
        __LINE__,
        'Screen Command "' . $this->screenCommand . '" not found',
        Log::NOTICE
    );
    return false;
  }


  protected function setOptions()
  {
    if (array_key_exists(self::CONFIG_SELECTION, $this->config)) {
      $config = $this->config[self::CONFIG_SELECTION];

      if (array_key_exists(self::CONFIG_OPTION_COMMAND, $config)) {
        $this->screenCommand = $config[self::CONFIG_OPTION_COMMAND];
      }

      if (array_key_exists(self::CONFIG_OPTION_JSSCRIPT, $this->config)) {
        $this->jsScript = $config[self::CONFIG_OPTION_JSSCRIPT];
      } else {
        $this->jsScript = __DIR__ . '/Phantomjs/Phantomjs.js';
      }

      if (array_key_exists(self::CONFIG_PARAMS, $config)) {
        $this->params = $config[self::CONFIG_PARAMS];
      }

      if (array_key_exists(self::CONFIG_OUTPUTFILE, $config)) {
        $this->outputfile = $config[self::CONFIG_OUTPUTFILE];
      }

      if (array_key_exists(self::CONFIG_WAIT_RESPONSE, $config)) {
        $this->wait = ($config[self::CONFIG_WAIT_RESPONSE] == true);
      }
    }

    if (array_key_exists('screenWidth', $this->config)) {
      $this->width = (int)$this->config['screenWidth'];
    }
    if (array_key_exists('screenHeight', $this->config)) {
      $this->height = (int)$this->config['screenHeight'];
    }
  }

  /**
   * @param array $config
   *
   * @throws Screenshot\InvalidConfigException
   */
  protected function checkRequiredOptions(array $config = array())
  {
    parent::checkRequiredOptions($config);

    if (!array_key_exists(self::CONFIG_SELECTION, $config)) {
      throw new Screenshot\InvalidConfigException(sprintf(
        'Configuration section for "%s" missing', self::CONFIG_SELECTION));
    }

    $phantomJsConfig = $config[self::CONFIG_SELECTION];
    if (!array_key_exists(self::CONFIG_OPTION_COMMAND, $phantomJsConfig)) {
      throw new Screenshot\InvalidConfigException(sprintf(
        'Configuration must have keys for "%s"', self::CONFIG_OPTION_COMMAND));
    }
  }
}
