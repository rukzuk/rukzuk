<?php
namespace Seitenbau;

/**
 * @package      Seitenbau
 * @subpackage   FactoryBase
 */
abstract class FactoryBase
{
  /**
   * @return string
   */
  protected static function getDefaultClassName()
  {
    return static::DEFAULT_CLASS;
  }


  /**
   * @return string
   */
  protected static function getClassPath()
  {
    return static::CLASS_PATH;
  }
  

  /**
   * @param  string  $name
   * @return object
   * @throws Exception
   */
  public static function get($name = null)
  {
    $className = static::getClassName($name);
    return self::create($className);
  }

  /**
   * @param   string  $className
   * @return object
   * @throws Exception
   */
  protected static function create($className)
  {
    $classNamePath = sprintf(static::getClassPath(), ucfirst($className));
    if (!class_exists($classNamePath)) {
      $errorMessage = 'Class ' . $classNamePath . ' doesn\'t exist';
      throw new \Exception($errorMessage);
    }
    
    return new $classNamePath();
  }

  /**
   * @param  string  $name
   * @return string
   */
  public static function getClassName($name)
  {
    if (isset($name)) {
      return $name;
    } else {
      return static::getDefaultClassName();
    }
  }
}
