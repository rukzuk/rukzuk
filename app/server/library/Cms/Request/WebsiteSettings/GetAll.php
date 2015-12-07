<?php


namespace Cms\Request\WebsiteSettings;

use Cms\Request\Base;

/**
 * @package      Cms\Request
 * @subpackage   WebsiteSettings
 */

class GetAll extends Base
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
