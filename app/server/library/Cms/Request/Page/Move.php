<?php
namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * Request object fuer Page move
 *
 * @package      Cms
 * @subpackage   Request
 */

class Move extends Base
{
  protected $id;

  protected $parentId;

  protected $insertBeforeId;

  protected $websiteId;

  protected function setValues()
  {
    $this->setId($this->getRequestParam('id'));
    $this->setParentId(($this->getRequestParam('parentid')));
    $this->setInsertBeforeId($this->getRequestParam('insertbeforeid'));
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

  public function getParentId()
  {
    return $this->parentId;
  }

  public function setParentId($parentId)
  {
    $this->parentId = $parentId;
  }

  public function getInsertBeforeId()
  {
    return $this->insertBeforeId;
  }

  public function setInsertBeforeId($insertBeforeId)
  {
    $this->insertBeforeId = (string) $insertBeforeId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
}
