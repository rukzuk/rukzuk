<?php


namespace Cms\Dao\WebsiteSettings;

use Cms\Dao\WebsiteSettings as WebsiteSettingsDaoInterface;
use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;

/**
 * dao for merging multiple website snippet dao
 *
 * - loading website settings base data from filesystem dao
 * - loading and saving website settings formValues data over the doctrine dao
 *
 * @package Cms\Dao\WebsiteSettings
 */
class All implements WebsiteSettingsDaoInterface
{
  protected $daoFilesystem;
  protected $daoDoctrine;

  public function __construct()
  {
    $this->daoDoctrine = new Doctrine();
    $this->daoFilesystem = new Filesystem();
  }

  /**
   * returns all WebsiteSettings of the given source
   *
   * @param WebsiteSettingsSource $source
   *
   * @return DataWebsiteSettings[]
   */
  public function getAll(WebsiteSettingsSource $source)
  {
    $allWebsiteSettings = $this->daoFilesystem->getAll($source);
    $allDoctrineSettings = $this->daoDoctrine->getAll($source);
    foreach ($allDoctrineSettings as $id => $doctrineSettings) {
      if (array_key_exists($id, $allWebsiteSettings)) {
        $this->updateFilesystemData($allWebsiteSettings[$id], $doctrineSettings);
      }
    }
    return $allWebsiteSettings;
  }

  /**
   * returns the WebsiteSettings of the given source and id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @return \Cms\Data\WebsiteSettings
   * @throws \Cms\Exception
   */
  public function getById(WebsiteSettingsSource $source, $id)
  {
    $websiteSettings = $this->daoFilesystem->getById($source, $id);
    if ($this->daoDoctrine->exists($source, $id)) {
      $doctrineSettings = $this->daoDoctrine->getById($source, $id);
      $this->updateFilesystemData($websiteSettings, $doctrineSettings);
    }
    return $websiteSettings;
  }

  /**
   * Checks if there is are WebsiteSettings under the given WebsiteSettings-Id and Website-Id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @throws \Cms\Exception
   * @return boolean
   */
  public function exists(WebsiteSettingsSource $source, $id)
  {
    return $this->daoFilesystem->exists($source, $id);
  }

  /**
   * creates a new WebsiteSettings
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings[]
   */
  public function create(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    return $this->daoDoctrine->create($source, $websiteSettings);
  }

  /**
   * updates the website settings of the given id and website id
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings
   */
  public function update(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    $id = $websiteSettings->getId();
    $newWebsiteSettings = $this->daoFilesystem->getById($source, $id);
    if ($this->daoDoctrine->exists($source, $id)) {
      $doctrineSettings = $this->daoDoctrine->update($source, $websiteSettings);
    } else {
      $doctrineSettings = $this->daoDoctrine->create($source, $websiteSettings);
    }
    $this->updateFilesystemData($newWebsiteSettings, $doctrineSettings);
    return $newWebsiteSettings;
  }

  /**
   * @param WebsiteSettingsSource $fromSource
   * @param WebsiteSettingsSource $toSource
   *
   * @return boolean
   */
  public function copyToNewWebsite(WebsiteSettingsSource $fromSource, WebsiteSettingsSource $toSource)
  {
    return $this->daoDoctrine->copyToNewWebsite($fromSource, $toSource);
  }

  /**
   * deletes all website settings of the given website id
   *
   * @param WebsiteSettingsSource $source
   */
  public function deleteByWebsiteId(WebsiteSettingsSource $source)
  {
    return $this->daoDoctrine->deleteByWebsiteId($source);
  }

  /**
   * @param DataWebsiteSettings $filesystemSettings
   * @param DataWebsiteSettings $doctrineSettings
   */
  protected function updateFilesystemData(
      DataWebsiteSettings $filesystemSettings,
      DataWebsiteSettings $doctrineSettings
  ) {
    $formValues = $this->getFormValuesAsArray($filesystemSettings);
    $dbFormValues = $this->getFormValuesAsArray($doctrineSettings);
    $filesystemSettings->setFormValues((object) array_merge($formValues, $dbFormValues));
  }

  /**
   * @param DataWebsiteSettings $websiteSettings
   *
   * @return array
   */
  protected function getFormValuesAsArray(DataWebsiteSettings $websiteSettings)
  {
    $formValues = $websiteSettings->getFormValues();
    if (is_object($formValues)) {
      $formValuesAsArray = get_object_vars($formValues);
    } elseif (!is_array($formValues)) {
      $formValuesAsArray = array();
    }
    return $formValuesAsArray;
  }
}
