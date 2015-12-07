<?php
namespace Cms\Request\Render;

use Cms\Request\Base;

/**
 * Request object for Render Page
 *
 * @package      Cms
 * @subpackage   Request
 */

class Page extends Base
{
  private $pageId;

  private $websiteId;

  private $data;

  private $mode;

  protected function setValues()
  {
    $this->setPageId($this->getRequestParam('pageid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    if ($this->getRequestParam('data')) {
      $this->setData($this->getRequestParam('data'));
    }
    if ($this->getRequestParam('mode')) {
      $this->setMode($this->getRequestParam('mode'));
    }
  }

  public function getPageId()
  {
    return $this->pageId;
  }

  public function setPageId($pageId)
  {
    $this->pageId = $pageId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getData()
  {
    return $this->data;
  }

  public function setData($data)
  {
    $this->data = $data;
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function setMode($mode)
  {
    $this->mode = strtoupper($mode);
  }
}
