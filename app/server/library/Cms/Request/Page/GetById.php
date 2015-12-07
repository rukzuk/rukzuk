<?php
namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * Request object fuer Page getById
 *
 * @package      Cms
 * @subpackage   Request
 */

class GetById extends Base
{
  private $id;

  private $websiteId;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
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
