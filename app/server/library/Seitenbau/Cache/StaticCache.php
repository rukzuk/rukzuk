<?php
namespace Seitenbau\Cache;

/**
 * Class StaticCache
 *
 * @package Seitenbau\Cache
 */
class StaticCache
{
  /**
   * @var mixed[]
   */
  static protected $cache = array();
  /**
   * @var
   */
  private $section;

  /**
   * @param string $section
   */
  public function __construct($section)
  {
    $this->section = $section;
    if (!isset(self::$cache[$this->section])) {
      self::$cache[$this->section] = array();
    }
  }

  /**
   * @param bool $resetAllSections
   */
  public function resetCache($resetAllSections = false)
  {
    if ($resetAllSections) {
      // reset all registered sections
      foreach (array_keys(self::$cache) as $section) {
        self::$cache[$section] = array();
      }
    } else {
      self::$cache[$this->section] = array();
    }
  }

  /**
   * @param string $key
   * @param mixed  $value
   */
  public function setValue($key, $value)
  {
    self::$cache[$this->section][$key] = $value;
  }

  /**
   * @param string $key
   * @param null   $default
   *
   * @return mixed|null
   */
  public function getValue($key, $default = null)
  {
    if (!isset(self::$cache[$this->section][$key])) {
      return $default;
    }
    return self::$cache[$this->section][$key];
  }

  /**
   * @param string $key
   *
   * @return bool
   */
  public function hasValue($key)
  {
    return isset(self::$cache[$this->section][$key]);
  }
}
