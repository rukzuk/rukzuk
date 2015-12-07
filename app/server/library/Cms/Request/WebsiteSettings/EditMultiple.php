<?php


namespace Cms\Request\WebsiteSettings;

use Cms\Request\Base;

/**
 * @package      Cms\Request
 * @subpackage   WebsiteSettings
 */

class EditMultiple extends Base
{
  private $websiteId;
  private $allWebsiteSettings;
  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setAllWebsiteSettings($this->getRequestParam('websitesettings'));
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function getAllWebsiteSettings()
  {
    return $this->allWebsiteSettings;
  }

  public function setAllWebsiteSettings($allWebsiteSettings)
  {
    if (is_array($allWebsiteSettings)) {
      $this->allWebsiteSettings = $allWebsiteSettings;
    } elseif (is_object($allWebsiteSettings)) {
      $this->allWebsiteSettings = get_object_vars($allWebsiteSettings);
    } else {
      $this->allWebsiteSettings = array();
    }
  }
}
