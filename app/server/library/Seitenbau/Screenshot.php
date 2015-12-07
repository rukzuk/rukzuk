<?php
namespace Seitenbau;

use Seitenbau\Screenshot as ScreenshotFiles;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;

/**
 * Screenshot Factory
 *
 * @package      Seitenbau
 */

class Screenshot
{
  const ACTIVATE = 'activ';
  const DIRECTORY = 'directory';
  const OPTIONS = 'options';
  const FILETYPE = 'filetype';

  static protected $adapterCreateMapping = array();

  /**
   * @param string $adapter
   * @param array $config
   * @return Seitenbau\Screenshot\Base
   */
  public static function factory($adapter, $config = array())
  {
    if ($config instanceof \Zend_Config) {
      $config = $config->toArray();
    }

    if ($adapter instanceof \Zend_Config) {
      if (isset($adapter->params)) {
        $config = $type->params->toArray();
      }
      if (isset($adapter->adapter)) {
        $adapterName = (string) $adapter->adapter;
      } else {
        $adapterName = null;
      }
    } else {
      $adapterName = $adapter;
    }
    
    if (!is_string($adapterName) || empty($adapterName)) {
      throw new \Exception('Adapter name must be specified in a string');
    }
    
    $adapterName = strtolower($adapterName);

    if (isset(self::$adapterCreateMapping[$adapterName])) {
      $createCallback = self::$adapterCreateMapping[$adapterName];
      return $createCallback($adapterName, $config);
    } else {
      return self::create($adapterName, $config);
    }
  }
  

  /**
   * creates the screenshot object
   *
   * @param   string  $adapterName
   * @param   array   $config
   * @return
   */
  protected static function create($adapterName, array $config)
  {
    $screentoolClassName = '\Seitenbau\Screenshot\Type\\' . ucfirst($adapterName);
    if (!class_exists($screentoolClassName)) {
      $errorMessage = 'Class ' . $screentoolClassName . ' doesn\'t exist';
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, SbLog::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }
    
    $screentool = new $screentoolClassName($config);
    
    if (!($screentool instanceof ScreenshotFiles\Screenshot)) {
      $errorMessage = 'Type class ' . $screentoolClassName . ' does not extend \Seitenbau\Screenshot\Screenshot';
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, SbLog::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }

    return $screentool;
  }
  
  /**
   * sets the screenshot creation callback for the given screenshot type
   *
   * @param   string    $daoName
   * @param   Callable  $createCallback
   */
  public static function setAdapterCreate($daoName, $createCallback)
  {
    self::$adapterCreateMapping[strtolower($daoName)] = $createCallback;
  }

  /**
   * resets the screenshot factory class
   */
  public static function reset()
  {
    self::$daoCreateMapping = array();
  }
}
