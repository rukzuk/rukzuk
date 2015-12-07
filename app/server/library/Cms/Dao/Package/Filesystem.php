<?php


namespace Cms\Dao\Package;

use Cms\Dao\Base\Filesystem as BaseFilesystemDao;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\Package as PackageDaoInterface;
use Cms\Dao\Package\Source as PackageSource;
use Cms\Data\Package as DataPackage;
use Cms\Exception as CmsException;
use Cms\Validator\PackageId as PackageIdValidator;
use Seitenbau\FileSystem as FS;

/**
 * Filesystem dao for packages
 *
 * @package Cms\Dao\Package
 */
class Filesystem extends BaseFilesystemDao implements PackageDaoInterface
{
  const SUBDIR_WEBSITE_SETTINGS = 'websiteSettings';
  const SUBDIR_PAGE_TYPES = 'pageTypes';
  const SUBDIR_TEMPLATE_SNIPPETS = 'templateSnippets';
  const SUBDIR_MODULES = 'modules';

  /**
   * @return string
   */
  protected function getManifestFileName()
  {
    return 'pkg.json';
  }

  /**
   * returns all Packages of the given source
   *
   * @param PackageSource $packageSource
   *
   * @return  DataPackage[]
   */
  public function getAll(PackageSource $packageSource)
  {
    return $this->internalGetAll($packageSource);
  }

  /**
   * @return PackageIdValidator
   */
  protected function createIdValidator()
  {
    return new PackageIdValidator();
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
   * @return DataPackage
   */
  protected function loadDataObject($websiteId, $id, SourceItem $sourceItem, \stdClass $manifest)
  {
    $package = new DataPackage();

    $package->setWebsiteid($websiteId);
    $package->setId($id);
    $package->setReadonly($sourceItem->isReadonly());
    $package->setSourceType($sourceItem->getType());
    $package->setSource($sourceItem);

    if (property_exists($manifest, 'name') && is_object($manifest->name)) {
      $package->setName($manifest->name);
    }
    if (property_exists($manifest, 'description') && is_object($manifest->description)) {
      $package->setDescription($manifest->description);
    }
    if (property_exists($manifest, 'version')) {
      $package->setVersion($manifest->version);
    }
    if (property_exists($manifest, 'websiteSettings') && is_array($manifest->websiteSettings)) {
      $package->setWebsiteSettings($manifest->websiteSettings);
    }
    if (property_exists($manifest, 'pageTypes') && is_array($manifest->pageTypes)) {
      $package->setPageTypes($manifest->pageTypes);
    }
    if (property_exists($manifest, 'templateSnippets') && is_array($manifest->templateSnippets)) {
      $package->setTemplateSnippets($manifest->templateSnippets);
    }
    if (property_exists($manifest, 'modules') && is_array($manifest->modules)) {
      $package->setModules($manifest->modules);
    }

    $this->setWebsiteSettingsSource($package, $sourceItem);
    $this->setPageTypesSource($package, $sourceItem);
    $this->setTemplateSnippetsSource($package, $sourceItem);
    $this->setModulesSource($package, $sourceItem);

    return $package;
  }

  /**
   * @param DataPackage $package
   * @param SourceItem  $sourceItem
   */
  protected function setWebsiteSettingsSource(DataPackage $package, SourceItem $sourceItem)
  {
    $sources = array();
    foreach ($package->getWebsiteSettings() as $websiteSettingsId) {
      $sources[] = $this->createSubdirSourceItem($sourceItem, $websiteSettingsId, self::SUBDIR_WEBSITE_SETTINGS);
    }
    $package->setWebsiteSettingsSource($sources);
  }

  /**
   * @param DataPackage $package
   * @param SourceItem  $sourceItem
   */
  protected function setPageTypesSource(DataPackage $package, SourceItem $sourceItem)
  {
    $sources = array();
    foreach ($package->getPageTypes() as $pageTypeId) {
      $sources[] = $this->createSubdirSourceItem($sourceItem, $pageTypeId, self::SUBDIR_PAGE_TYPES);
    }
    $package->setPageTypesSource($sources);
  }

  /**
   * @param DataPackage $package
   * @param SourceItem  $sourceItem
   */
  protected function setTemplateSnippetsSource(DataPackage $package, SourceItem $sourceItem)
  {
    $sources = array();
    foreach ($package->getTemplateSnippets() as $snippetsId) {
      $sources[] = $this->createSubdirSourceItem($sourceItem, $snippetsId, self::SUBDIR_TEMPLATE_SNIPPETS);
    }
    $package->setTemplateSnippetsSource($sources);
  }

  /**
   * @param DataPackage $package
   * @param SourceItem  $sourceItem
   */
  protected function setModulesSource(DataPackage $package, SourceItem $sourceItem)
  {
    $sources = array();
    foreach ($package->getModules() as $modulesId) {
      $sources[] = $this->createSubdirSourceItem($sourceItem, $modulesId, self::SUBDIR_MODULES);
    }
    $package->setModulesSource($sources);
  }

  /**
   * @param SourceItem $sourceItem
   * @param string     $newId
   * @param string     $subdirectory
   *
   * @return SourceItem
   */
  public function createSubdirSourceItem(SourceItem $sourceItem, $newId, $subdirectory)
  {
    return new SourceItem(
        $newId,
        FS::joinPath($sourceItem->getDirectory(), $subdirectory, $newId),
        $sourceItem->getUrl() . '/' . $subdirectory . '/' . $newId,
        $sourceItem->getType(),
        $sourceItem->isReadonly(),
        false
    );
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
    throw new CmsException(2602, $method, $line, $data);
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
    throw new CmsException(2603, $method, $line, $data);
  }

  /**
   * @return string
   */
  protected static function getCacheSectionName()
  {
    return __CLASS__;
  }
}
