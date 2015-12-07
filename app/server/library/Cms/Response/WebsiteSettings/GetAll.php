<?php


namespace Cms\Response\WebsiteSettings;

use Cms\Response\IsResponseData;
use Cms\Response\WebsiteSettings as WebsiteSettingsResponse;

/**
 * @package      Cms\Response
 * @subpackage   WebsiteSettings
 */
class GetAll implements IsResponseData
{
  public $websiteSettings = array();

  /**
   * @param \Cms\Data\WebsiteSettings[] $websiteSettings
   */
  public function __construct($websiteSettings = array())
  {
    $this->setWebsites($websiteSettings);
  }

  /**
   * @return WebsiteSettingsResponse[]
   */
  public function getWebsites()
  {
    return $this->websiteSettings;
  }

  /**
   * @param \Cms\Data\WebsiteSettings[] $websiteSettings
   */
  protected function setWebsites(array $websiteSettings)
  {
    foreach ($websiteSettings as $settings) {
      $this->websiteSettings[] = new WebsiteSettingsResponse($settings);
    }
  }
}
