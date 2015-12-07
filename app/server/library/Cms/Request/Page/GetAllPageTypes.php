<?php


namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * @package      Cms\Request
 * @subpackage   Page
 */

class GetAllPageTypes extends Base
{
  private $websiteId;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
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
