<?php


namespace Render\InfoStorage\WebsiteInfoStorage;

use Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists;

/**
 * Class FileBasedWebsiteInfoStorage
 *
 * @package Render\InfoStorage\WebsiteInfoStorage
 */
class LiveArrayWebsiteInfoStorage extends ArrayBasedWebsiteInfoStorage
{
  protected $websiteSettingsFile = null;
  protected $loaded = false;

  /**
   * @param string $websiteSettingsFile
   */
  public function __construct($websiteSettingsFile)
  {
    $this->websiteSettingsFile = $websiteSettingsFile;
  }

  /**
   * @param string $websiteSettingsId
   *
   * @return string
   * @throws WebsiteSettingsDoesNotExists
   */
  public function getWebsiteSettings($websiteSettingsId)
  {
    $this->loadWebsiteSettings();
    return parent::getWebsiteSettings($websiteSettingsId);
  }

  /**
   * ArrayBasedWebsiteInfoStorage constructor compatible array representation
   *
   * @return array
   */
  public function toArray()
  {
    $this->loadWebsiteSettings();
    return parent::toArray();
  }

  /**
   * loads the website settings from array file
   */
  protected function loadWebsiteSettings()
  {
    if ($this->loaded) {
      return;
    }

    if (file_exists($this->websiteSettingsFile)) {
      /** @noinspection PhpIncludeInspection */
      $this->websiteSettings = @include($this->websiteSettingsFile);
    }
    if (!is_array($this->websiteSettings)) {
      $this->websiteSettings = array();
    }

    // set loaded flag
    $this->loaded = true;
  }
}
