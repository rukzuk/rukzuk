<?php
namespace Cms\Publisher;

use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;

/**
 * Factory
 *
 * @package      Cms
 * @subpackage   Publisher
 */
class Factory
{
  /**
   * @param  string  $name
   * @return \Cms\Publisher\Publisher
   * @throws \Cms\Exception
   */
  public static function get($name = null)
  {
    $publisherName = self::getPublisherName($name);
    return self::create($publisherName);
  }

  /**
   * creates the publisher object
   *
   * @param   string  $publisherName
   * @return
   */
  protected static function create($publisherName)
  {
    $publisherClassName = '\Cms\Publisher\Type\\' . ucfirst($publisherName);
    if (!class_exists($publisherClassName)) {
      $errorMessage = 'Class ' . $publisherClassName . ' doesn\'t exist';
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, Log::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }
    
    return new $publisherClassName();
  }

  /**
   * @param  string  $name
   * @return Cms\Publisher
   * @throws Cms\Exception
   */
  public static function getPublisherName($name)
  {
    if (isset($name)) {
      return $name;
    } else {
      return Registry::getConfig()->publisher->type;
    }
  }
}
