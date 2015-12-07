<?php


namespace Cms\Dao\WebsiteSettings;

use Cms\Dao\Base\Filesystem as BaseFilesystemDao;
use Cms\Dao\Base\ReadOnlyException;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\WebsiteSettings as WebsiteSettingsDaoInterface;
use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;
use Cms\Validator\WebsiteSettingsId as WebsiteSettingsIdValidator;
use Seitenbau\FileSystem as FS;
use Cms\Exception as CmsException;

/**
 * Filesystem dao for website settings
 *
 * @package Cms\Dao\WebsiteSettings
 */
class Filesystem extends BaseFilesystemDao implements WebsiteSettingsDaoInterface
{
  /**
   * @return string
   */
  protected function getManifestFileName()
  {
    return 'websiteSettings.json';
  }

  /**
   * returns all Packages of the given source
   *
   * @param WebsiteSettingsSource $source
   *
   * @return \Cms\Data\WebsiteSettings[]
   */
  public function getAll(WebsiteSettingsSource $source)
  {
    return $this->internalGetAll($source);
  }

  /**
   * returns the WebsiteSettings of the given source and id
   *
   * @param WebsiteSettingsSource $source
   * @param string                $id
   *
   * @return DataWebsiteSettings
   */
  public function getById(WebsiteSettingsSource $source, $id)
  {
    return $this->internalGetById($source, $id);
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
    return $this->internalExists($source, $id);
  }

  /**
   * creates a new WebsiteSettings
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings
   * @throws ReadOnlyException
   */
  public function create(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    throw new ReadOnlyException(2, __METHOD__, __LINE__, null, array(
      'message' => 'read only DAO'));
  }

  /**
   * updates the website settings of the given id and website id
   *
   * @param WebsiteSettingsSource $source
   * @param DataWebsiteSettings   $websiteSettings
   *
   * @return \Cms\Data\WebsiteSettings
   * @throws ReadOnlyException
   */
  public function update(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    throw new ReadOnlyException(2, __METHOD__, __LINE__, null, array(
      'message' => 'read only DAO'));
  }

  /**
   * @param WebsiteSettingsSource $fromSource
   * @param WebsiteSettingsSource $toSource
   *
   * @return bool
   * @throws ReadOnlyException
   */
  public function copyToNewWebsite(WebsiteSettingsSource $fromSource, WebsiteSettingsSource $toSource)
  {
    throw new ReadOnlyException(2, __METHOD__, __LINE__, null, array(
      'message' => 'read only DAO'));
  }

  /**
   * deletes all website settings of the given website id
   *
   * @param WebsiteSettingsSource $source
   *
   * @throws ReadOnlyException
   */
  public function deleteByWebsiteId(WebsiteSettingsSource $source)
  {
    throw new ReadOnlyException(2, __METHOD__, __LINE__, null, array(
      'message' => 'read only DAO'));
  }

  /**
   * @return WebsiteSettingsIdValidator
   */
  protected function createIdValidator()
  {
    return new WebsiteSettingsIdValidator();
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param array      $additionalData
   *
   * @return object
   * @throws \Exception
   */
  protected function loadEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData)
  {
    $manifest = $this->loadManifestFile($sourceItem);
    return $this->loadDataObject($websiteId, $id, $sourceItem, $manifest);
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param \stdClass  $manifest
   *
   * @return DataWebsiteSettings
   */
  protected function loadDataObject($websiteId, $id, SourceItem $sourceItem, \stdClass $manifest)
  {
    $websiteSettings = new DataWebsiteSettings();

    $websiteSettings->setWebsiteid($websiteId);
    $websiteSettings->setId($id);
    $websiteSettings->setReadonly($sourceItem->isReadonly());
    $websiteSettings->setSource($sourceItem);
    $websiteSettings->setSourceType($sourceItem->getType());

    if (property_exists($manifest, 'name') && is_object($manifest->name)) {
      $websiteSettings->setName($manifest->name);
    }
    if (property_exists($manifest, 'description') && is_object($manifest->description)) {
      $websiteSettings->setDescription($manifest->description);
    }
    if (property_exists($manifest, 'version')) {
      $websiteSettings->setVersion($manifest->version);
    }
    if (property_exists($manifest, 'form')) {
      $websiteSettings->setForm($manifest->form);
    }
    if (property_exists($manifest, 'formValues')) {
      $websiteSettings->setFormValues($manifest->formValues);
    }

    return $websiteSettings;
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwNotExistsException($method, $line, $data)
  {
    throw new CmsException(2502, $method, $line, $data);
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwGetByIdErrorException($method, $line, $data)
  {
    throw new CmsException(2503, $method, $line, $data);
  }

  /**
   * @return string
   */
  protected static function getCacheSectionName()
  {
    return __CLASS__;
  }
}
