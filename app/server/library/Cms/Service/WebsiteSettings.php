<?php


namespace Cms\Service;

use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Exception;
use Cms\Service\Base\Dao as DaoServiceBase;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;
use \Cms\Data\WebsiteSettings as DataWebsiteSettings;

/**
 * @package Cms\Service
 *
 * @method \Cms\Dao\WebsiteSettings getDao
 */
class WebsiteSettings extends DaoServiceBase
{
  const WEBSITE_SETTINGS_SUBDIRECTORY = 'websiteSettings';

  /**
   * returns all website settings of the given website
   *
   * @param   string $websiteId
   *
   * @return  \Cms\Data\WebsiteSettings[]
   */
  public function getAll($websiteId)
  {
    $source = $this->getSource($websiteId);
    return $this->getDao()->getAll($source);
  }

  /**
   * returns the website settings of the given website id and website settings id
   *
   * @param   string $websiteId
   *
   * @return  \Cms\Data\WebsiteSettings
   */
  public function getById($websiteId, $id)
  {
    $source = $this->getSource($websiteId);
    return $this->getDao()->getById($source, $id);
  }

  /**
   * checks if the website settings given by website id and id exists
   *
   * @param   string $websiteId
   * @param   string $id
   *
   * @return bool
   */
  public function exists($websiteId, $id)
  {
    $source = $this->getSource($websiteId);
    return $this->getDao()->exists($source, $id);
  }

  /**
   * updates the given website settings
   *
   * @param string $websiteId
   * @param string $id
   * @param array  $attributes
   *
   * @return DataWebsiteSettings
   */
  public function update($websiteId, $id, array $attributes)
  {
    $source = $this->getSource($websiteId);

    $websiteSettings = new DataWebsiteSettings();
    $websiteSettings->setWebsiteId($websiteId);
    $websiteSettings->setId($id);
    $this->setAttributesToDataWebsiteSettings($websiteSettings, $attributes);

    return $this->getDao()->update($source, $websiteSettings);
  }

  /**
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    $source = $this->getSource($websiteId);
    $this->getDao()->deleteByWebsiteId($source);
  }

  /**
   * @param string $fromWebsiteId
   * @param string $toWebsiteId
   */
  public function copyToNewWebsite($fromWebsiteId, $toWebsiteId)
  {
    $sourceFrom = $this->getSource($fromWebsiteId);
    $sourceTo = $this->getSource($toWebsiteId);
    return $this->getDao()->copyToNewWebsite($sourceFrom, $sourceTo);
  }

  /**
   * @param DataWebsiteSettings $websiteSettings
   * @param array               $attributes
   */
  protected function setAttributesToDataWebsiteSettings(
      DataWebsiteSettings $websiteSettings,
      array $attributes
  ) {
    if (array_key_exists('formValues', $attributes)) {
      $websiteSettings->setFormValues($attributes['formValues']);
    }
  }

  /**
   * @param string $websiteId
   *
   * @return WebsiteSettingsSource
   */
  protected function getSource($websiteId)
  {
    $sources = array();
    try {
      $packageService = $this->getPackageService();
      $packages = $packageService->getAll($websiteId);
      foreach ($packages as $package) {
        $sources = array_merge($sources, $package->getWebsiteSettingsSource());
      }
    } catch (Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return new WebsiteSettingsSource($websiteId, $sources);
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    return $this->getService('Package');
  }
}
