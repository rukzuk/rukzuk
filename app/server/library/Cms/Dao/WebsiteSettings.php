<?php


namespace Cms\Dao;

use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;

interface WebsiteSettings
{
  /**
   * returns all Packages of the given source
   *
   * @param WebsiteSettingsSource $source
   *
   * @return \Cms\Data\WebsiteSettings[]
   */
  public function getAll(WebsiteSettingsSource $source);

  /**
   * returns the WebsiteSettings of the given source and id
   *
   * @param WebsiteSettingsSource $source
   *
   * @return DataWebsiteSettings
   */
  public function getById(WebsiteSettingsSource $source, $id);

  /**
   * Checks if there is are WebsiteSettings under the given WebsiteSettings-Id and Website-Id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function exists(WebsiteSettingsSource $source, $id);

  /**
   * creates a new WebsiteSettings
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings
   */
  public function create(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings);

  /**
   * updates the website settings of the given id and website id
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings
   */
  public function update(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings);

  /**
   * @param WebsiteSettingsSource $fromSource
   * @param WebsiteSettingsSource $toSource
   *
   * @return boolean
   */
  public function copyToNewWebsite(WebsiteSettingsSource $fromSource, WebsiteSettingsSource $toSource);

  /**
   * deletes all website settings of the given website id
   *
   * @param WebsiteSettingsSource $source
   */
  public function deleteByWebsiteId(WebsiteSettingsSource $source);
}
