<?php


namespace Render\InfoStorage\WebsiteInfoStorage;

use Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists;

/**
 * Class ArrayBasedColorInfoStorage
 *
 * @package Render\InfoStorage\WebsiteInfoStorage
 */
class ArrayBasedWebsiteInfoStorage implements IWebsiteInfoStorage
{
  /**
   * @var array
   */
  protected $websiteSettings = array();

  /**
   * @param array $websiteSettings
   */
  public function __construct(array $websiteSettings)
  {
    $this->websiteSettings = $websiteSettings;
  }

  /**
   * @param string $websiteSettingsId
   *
   * @return array
   * @throws WebsiteSettingsDoesNotExists
   */
  public function getWebsiteSettings($websiteSettingsId)
  {
    if (!is_string($websiteSettingsId) || !array_key_exists($websiteSettingsId, $this->websiteSettings)) {
      throw new WebsiteSettingsDoesNotExists();
    }
    return $this->websiteSettings[$websiteSettingsId];
  }

  /**
   * ArrayBasedWebsiteInfoStorage constructor compatible array representation
   *
   * @return array
   */
  public function toArray()
  {
    return $this->websiteSettings;
  }
}
