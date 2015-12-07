<?php


namespace Render\InfoStorage\WebsiteInfoStorage;

interface IWebsiteInfoStorage
{

  /**
   * @param string $websiteSettingsId
   *
   * @return array
   */
  public function getWebsiteSettings($websiteSettingsId);

  /**
   * ArrayBasedWebsiteInfoStorage constructor compatible array representation
   *
   * @return array
   */
  public function toArray();
}
