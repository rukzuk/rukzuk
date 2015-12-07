<?php
namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * Request object fuer Page copy
 *
 * @package      Cms
 * @subpackage   Request
 */

class Copy extends Base
{
  private $id;

  private $name;

  private $websiteId;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
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

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
