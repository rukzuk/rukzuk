<?php
namespace Cms\Access\AuthBackend;

/**
 * @package      Cms
 * @subpackage   Access\Auth
 */
class Factory
{
  const CLASS_PATH = '\Cms\Access\AuthBackend\Type\%s';
  
  /**
   * @param  string  $name
   * @return object
   * @throws Exception
   */
  public static function get($name, \Cms\Access\Manager $accessManager)
  {
    $classNamePath = sprintf(self::CLASS_PATH, ucfirst($name));
    if (!class_exists($classNamePath)) {
      $errorMessage = 'Class ' . $name . ' doesn\'t exist';
      throw new \Exception($errorMessage);
    }
    
    return new $classNamePath($accessManager);
  }
}
