<?php
namespace Seitenbau;

use Seitenbau\Image as ImageFiles;

/**
 * Bildverarbeitungs-Factory
 *
 * @package      Seitenbau
 */

class Image
{
  static private $_adapterClassesLoaded = array();
  static private $_memory_limit = null;

  /**
   * Erstellt ein Bildverarbeitungs-Tool Seitenbau\Image\Base
   *
   * @param array $config
   */
  public static function factory($config = null)
  {
    //set memory limit for large images
    if (!isset(self::$_memory_limit)) {
      self::setMemoryLimit();
    }

    $imageAdapterName = null;

    if ($config instanceof \Zend_Config) {
      $config = $config->toArray();
    }
    
    if (!is_array($config)) {
      $globalConfig = Registry::getConfig();
      if (isset($globalConfig->imageAdapter) && !empty($globalConfig->imageAdapter)) {
        $config = $globalConfig->imageAdapter->toArray();
      }
    }

    if (isset($config['adapter']) && !empty($config['adapter'])) {
      $imageAdapterName = (string) $config['adapter'];
    } else {
      // Default/Fallback
      $imageAdapterName = 'Phpgd';
    }

    if (!is_string($imageAdapterName) || empty($imageAdapterName)) {
      throw new \Exception('Type name must be specified in a string');
    }

    $imageAdapterName = ucfirst($imageAdapterName);

    if (!self::loadClass($imageAdapterName)) {
      throw new \Exception("Image adpater class '$imageAdapterName' couldn't be loaded");
    }

    $imageAdapterClassName = '\Seitenbau\Image\Adapter\\'.$imageAdapterName;
    if (!class_exists($imageAdapterClassName)) {
      $errorMessage = sprintf(
          "Image processing class '%s' doesn't exist",
          $imageAdapterClassName
      );
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, \Seitenbau\Log::ERR);
      throw new \Exception($errorMessage);
    }

    try {
      $config = (is_array($config) ? $config : array());
      $imageTool = new $imageAdapterClassName($config);
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, $e->getCode());
    }
    
    if (!($imageTool instanceof ImageFiles\Image)) {
      throw new \Exception("Type class '$imagetoolName' does not extend \Seitenbau\Image\Image");
    }

    return $imageTool;
  }

  /**
   * Laede eine Bearbeitungsklasse
   */
  private static function loadClass($imageAdapterName)
  {
    // Bearbeitungsklasse bereits geladen
    if (isset(self::$_adapterClassesLoaded[$imageAdapterName])) {
      return true;
    }

    // Bearbeitungsklasse laden
    $imageAdapterClassFile = __DIR__.'/Image/Adapter/'.$imageAdapterName.'.php';
    if (file_exists($imageAdapterClassFile)) {
      include_once($imageAdapterClassFile);
      self::$_adapterClassesLoaded[$imageAdapterName] = true;
      return true;
    }

    // Bearbeitungsklasse konnte nicht geladen werden
    return false;
  }

  /**
   * Versucht das memory-limit auf mindestens 255MB (267386880 Byte)
   */
  private static function setMemoryLimit($newMemoryLimit = 267386880)
  {
    $memoryLimit = self::return_bytes(@ini_get('memory_limit'));
    if ($memoryLimit < $newMemoryLimit) {
      @ini_set('memory_limit', (int)$newMemoryLimit);
    }
    self::$_memory_limit = $memoryLimit;
  }
  /**
   * Ermittelt aus dem uebergebenen String den Wert in Byte
   */
  private static function return_bytes($val)
  {
    if (empty($val)) {
      return 0;
    }

    $val = trim($val);

    preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

    $last = '';
    if (isset($matches[2])) {
        $last = $matches[2];
    }

    if (isset($matches[1])) {
        $val = (int) $matches[1];
    }

    switch (strtolower($last))
    {
      case 'g':
      case 'gb':
          $val *= 1024;
          // hier absichtlich kein break damit Factorisierung funktioniert
      case 'm':
      case 'mb':
          $val *= 1024;
          // hier absichtlich kein break damit Factorisierung funktioniert
      case 'k':
      case 'kb':
          $val *= 1024;
    }

    return (int) $val;
  }
}
