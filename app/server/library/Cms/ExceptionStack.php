<?php
namespace Cms;

/**
 * Exception Stack
 *
 * @package    Cms
 */

class ExceptionStack
{
  protected static $exceptions = array();

  /**
   * Fuegt eine Exception dem Stack hinzu
   *
   * @param \Exception $exception
   * @param int $code
   * @param array $data
   */
  public static function addException(\Exception $exception)
  {
    self::$exceptions[] = $exception;
  }

  public static function getExceptions()
  {
    return self::$exceptions;
  }

  /**
   * Bricht die weitere Verarbeitung im Code ab und leitet auf den
   * Error-Controller um
   */
  public static function throwErrors($responseData = null)
  {
    throw new ExceptionStackException(self::getExceptions(), $responseData);
  }

  /**
   * Entfernt alle Fehlermeldungen
   */
  public static function reset()
  {
    self::$exceptions = array();
  }

  /**
   * Sind Fehlermeldungen vorhanden
   */
  public static function hasErrors()
  {
    return (count(self::$exceptions) > 0 ? true : false);
  }
}
