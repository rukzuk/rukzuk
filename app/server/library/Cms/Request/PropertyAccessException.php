<?php

namespace Cms\Request;

class PropertyAccessException extends \Exception
{
  /**
   * @var string
   */
  private $name;

  public function __construct($name)
  {

    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}
