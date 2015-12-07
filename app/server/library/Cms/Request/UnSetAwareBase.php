<?php

namespace Cms\Request;

/**
 * @package      Cms
 * @subpackage   Request
 *
 */
abstract class UnSetAwareBase extends Base
{

  protected $properties = array();

  abstract protected function getSupportedProperties();

  /**
   * @return array
   */
  protected function transformProperties()
  {

  }

  protected function setValues()
  {
    foreach ($this->getSupportedProperties() as $property) {
      if ($this->hasRequestParam($property)) {
        $this->properties[$property] = $this->getRequestParam($property);
      }
    }
    $this->transformProperties();
  }

  /**
   * @param array $names
   * @return array
   */
  public function getProperties(array $names = null)
  {
    if (is_null($names)) {
      return $this->properties;
    }
    return array_intersect_key($this->properties, $names);
  }

  /**
   * @param $name
   * @return mixed
   * @throws PropertyAccessException
   */
  public function getProperty($name)
  {
    if ($this->hasProperty($name)) {
      return $this->properties[$name];
    } else {
      throw new PropertyAccessException($name);
    }
  }

  /**
   * @param $name
   * @return bool
   */
  public function hasProperty($name)
  {
    if (isset($this->properties[$name]) || array_key_exists($name, $this->properties)) {
      return true;
    }
    return false;
  }
}
