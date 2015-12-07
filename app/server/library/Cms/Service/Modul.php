<?php
namespace Cms\Service;

use Cms\Dao\Base\AbstractSourceItem;
use Cms\Dao\Base\DynamicSourceItem;
use Cms\Dao\Base\SourceItem;
use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Dao\Module\Source as ModuleSource;
use Cms\Quota;
use Cms\Data\Modul as DataModule;
use Cms\Exception as CmsException;
use Cms\Service\Module\RelationException;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;

/**
 * Modul service
 *
 * @package      Cms
 * @subpackage   Service
 *
 * @method \Cms\Dao\Module getDao
 */
class Modul extends DaoServiceBase
{
  /**
   * resets the module cache
   */
  public function resetCache()
  {
    $this->getDao()->resetCache();
  }

  /**
   * @param string $websiteId
   *
   * @return array
   */
  public function getAll($websiteId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getAll($moduleSource);
  }

  /**
   * @param $websiteId
   *
   * @return
   */
  public function deleteByWebsiteId($websiteId)
  {
    return $this->getDao()->deleteByWebsiteId($websiteId);
  }

  /**
   * @param string $id
   * @param        $websiteId
   *
   */
  public function delete($id, $websiteId)
  {
    $this->deleteModules(array($id), $websiteId);
  }

  /**
   * @param array  $ids
   * @param string $websiteId
   *
   * @throws Module\RelationException
   * @throws \Exception
   */
  public function deleteModules(array $ids, $websiteId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    foreach ($ids as $id) {
      try {
        $this->checkForNoRelations($moduleSource->getWebsiteId(), $id);
      } catch (RelationException $e) {
        $module = $this->getDao()->getById($moduleSource, $id);
        if (!$module->isOverwritten()) {
          throw $e;
        }
      }
    }
    $this->getDao()->deleteByIds($moduleSource, $ids);
  }

  /**
   * @param string     $websiteId
   * @param DataModule $module
   * @param SourceItem $sourceItem
   *
   * @return DataModule
   * @throws \Exception
   */
  public function create($websiteId, DataModule $newModule, SourceItem $sourceItem = null)
  {
    $this->checkModuleDevelopmentQuota();
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->create($moduleSource, $newModule, $sourceItem);
  }

  /**
   * copy modules to new website
   *
   * @param string $fromWebsiteId
   * @param string $toWebsiteId
   * @param array  $modules
   * @param bool   $onlyLocalModules
   *
   * @return mixed
   */
  public function copyToNewWebsite($fromWebsiteId, $toWebsiteId)
  {
    $moduleSourceFrom = $this->getModuleSourceByWebsiteId($fromWebsiteId);
    $moduleSourceTo = $this->getModuleSourceByWebsiteId($toWebsiteId);
    return $this->getDao()->copyToNewWebsite($moduleSourceFrom, $moduleSourceTo);
  }

  /**
   * gibt ein Modul anhand seiner ID zurueck
   *
   * @param string $id
   * @param string $websiteId
   *
   * @return \Cms\Data\Modul
   */
  public function getById($id, $websiteId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getById($moduleSource, $id);
  }

  /**
   * gibt mehrere Module einer Website anhand ihrer IDs zurueck
   *
   * @param array   $moduleIds
   * @param  string $websiteId
   *
   * @return array[] \Cms\Data\Modul
   */
  public function getByIds(array $moduleIds, $websiteId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getByIds($moduleSource, $moduleIds);
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @param bool   $onlyLocal
   *
   * @return boolean
   */
  public function existsModulAlready($id, $websiteId, $onlyLocal = false)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->existsModule($moduleSource, $id, $onlyLocal);
  }

  /**
   * @param  string $websiteId
   * @param  string $moduleId
   *
   * @return array
   * @throws \Exception
   */
  public function getDataPath($websiteId, $moduleId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getDataPath($moduleSource, $moduleId);
  }

  /**
   * @param  string $websiteId
   * @param         $moduleId
   *
   * @return array
   */
  public function getAssetsPath($websiteId, $moduleId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getAssetsPath($moduleSource, $moduleId);
  }

  /**
   * @param  string $websiteId
   * @param  string $moduleId
   *
   * @return array
   * @throws \Exception
   */
  public function getAssetsUrl($websiteId, $moduleId)
  {
    $moduleSource = $this->getModuleSourceByWebsiteId($websiteId);
    return $this->getDao()->getAssetsUrl($moduleSource, $moduleId);
  }

  /**
   * @param string $websiteId
   * @param        $id
   *
   * @throws \Cms\Exception
   */
  protected function checkForNoRelations($websiteId, $id)
  {
    $relatedTemplates = $this->getService('Template')->findByWebsiteIdAndModuleId($websiteId, $id);
    if (count($relatedTemplates) > 0) {
      throw new RelationException(108, __METHOD__, __LINE__);
    }
  }

  /**
   * @param string $websiteId
   *
   * @return ModuleSource
   */
  protected function getModuleSourceByWebsiteId($websiteId)
  {
    $sources = array();
    try {
      $packageService = $this->getPackageService();
      $packages = $packageService->getAll($websiteId);
      foreach ($packages as $package) {
        $sources = array_merge($sources, $package->getModulesSource());
      }
    } catch (CmsException $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return new ModuleSource($websiteId, $sources);
  }

  /**
   * @return \Cms\Service\Package
   */
  protected function getPackageService()
  {
    return $this->getService('Package');
  }

  /**
   * creates the storage for the website given by id
   *
   * @param string $websiteId
   */
  public function createStorageForWebsite($websiteId)
  {
    $this->getDao()->createStorageForWebsite($websiteId);
  }

  /**
   * Checks if the module developments is allowed. Throws Exception if not!
   *
   * @throws \Cms\Exception
   */
  public function checkModuleDevelopmentQuota()
  {
    $quota = new Quota();
    $moduleQuota = $quota->getModuleQuota();
    if (!$moduleQuota->getEnableDev()) {
      throw new CmsException(2301, __METHOD__, __LINE__);
    }
  }
}
