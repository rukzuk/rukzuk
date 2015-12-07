<?php
namespace Cms\Request\Website;

use Cms\Request\Base;

/**
 * Request object for Website copy
 *
 * @package      Cms
 * @subpackage   Request
 */

class Copy extends Base
{
  private $id;

  private $name;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setName($this->getRequestParam('name'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }
}
