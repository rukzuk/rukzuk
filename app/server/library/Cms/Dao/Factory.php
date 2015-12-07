<?php
namespace Cms\Dao;

use Cms\Exception as CmsException;
use Seitenbau\Registry as Registry;

/**
 * Dao Factory
 *
 * @package      Cms
 * @subpackage   Dao
 */
class Factory
{
  static protected $daoCreateMapping = array();

  /**
   * @param  string $daoName
   *
   * @return \Cms\Dao
   * @throws \Cms\Exception
   */
  public static function get($daoName)
  {
    $daoType = self::getDaoType($daoName);

    if (isset(self::$daoCreateMapping[$daoName])) {
      $createCallback = self::$daoCreateMapping[$daoName];
      return $createCallback($daoName, $daoType);
    } else {
      return self::create($daoName, $daoType);
    }
  }

  /**
   * creates the dao object
   *
   * @param   string $daoName
   * @param   string $daoType
   *
   * @throws \Cms\Exception
   * @return object
   */
  protected static function create($daoName, $daoType)
  {
    $daoClassName = 'Cms\Dao\\' . $daoName . '\\' . $daoType;
    if (!class_exists($daoClassName)) {
      $errorMessage = 'Class ' . $daoClassName . ' doesn\'t exist';
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, \Seitenbau\Log::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }

    return new $daoClassName();
  }

  /**
   * returns the dao type for the given dao
   *
   * @param   string $daoName
   *
   * @return string
   */
  protected static function getDaoType($daoName)
  {
    $daoConfig = Registry::getConfig()->dao;
    if (isset($daoConfig->$daoName)) {
      return $daoConfig->$daoName->connection;
    } else {
      return $daoConfig->connection;
    }
  }

  /**
   * sets the dao creation callback for the given dao
   *
   * @param   string   $daoName
   * @param   Callable $createCallback
   */
  public static function setDaoCreate($daoName, $createCallback)
  {
    self::$daoCreateMapping[$daoName] = $createCallback;
  }

  /**
   * resets the dao factory class
   */
  public static function reset()
  {
    self::$daoCreateMapping = array();
  }
}
