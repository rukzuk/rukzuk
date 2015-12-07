<?php
namespace Cms\Dao;

use Cms\Dao\Base\SourceItem;
use Cms\Dao\Module\Source as ModuleSource;
use Cms\Data\Modul as DataModule;

interface Module
{
  /**
   * resets the internal cache
   */
  public function resetCache();

  /**
   * returns a specified module
   *
   * @param ModuleSource $source
   * @param string       $id
   *
   * @return DataModule
   */
  public function getById(ModuleSource $source, $id);

  /**
   * returns the specified modules given by $moduleIds
   *
   * @param ModuleSource $source
   * @param array        $moduleIds
   *
   * @return DataModule[]
   */
  public function getByIds(ModuleSource $source, array $ids);

  /**
   * returns all modules
   *
   * @param ModuleSource $source
   *
   * @return DataModule[]
   */
  public function getAll(ModuleSource $source);

  /**
   * Checks if there is a module under the given id and website id or global repository
   *
   * @param ModuleSource $source
   * @param string       $id
   * @param bool         $onlyLocal
   *
   * @return boolean
   */
  public function existsModule(ModuleSource $source, $id, $onlyLocal = false);

  /**
   * returns the data path for the given module
   *
   * @param ModuleSource $source
   * @param string       $id
   *
   * @return boolean
   */
  public function getDataPath(ModuleSource $source, $id);

  /**
   * returns the assets path for the given module
   *
   * @param ModuleSource $source
   * @param string       $id
   *
   * @return boolean
   */
  public function getAssetsPath(ModuleSource $source, $id);

  /**
   * returns the asset url of the given module
   *
   * @param ModuleSource $source
   * @param string       $id
   *
   * @return string
   */
  public function getAssetsUrl(ModuleSource $source, $id);

  /**
   * deletes all Modules of the given website id
   *
   * @param string $websiteId
   */
  public function deleteByWebsiteId($websiteId);

  /**
   * create a new module
   *
   * @param ModuleSource $moduleSource
   * @param DataModule   $module
   * @param SourceItem   $sourceItem
   *
   * @return DataModule
   */
  public function create(ModuleSource $moduleSource, DataModule $module, SourceItem $sourceItem = null);

  /**
   * deletes the modules given by $ids
   *
   * @param ModuleSource $moduleSource
   * @param string[]     $ids
   *
   * @return
   */
  public function deleteByIds(ModuleSource $moduleSource, array $ids);

  /**
   * creates the storage for the local website modules given by websiteId
   *
   * @param string $websiteId
   */
  public function createStorageForWebsite($websiteId);

  /**
   * @param ModuleSource $moduleSourceFrom
   * @param ModuleSource $moduleSourceTo
   */
  public function copyToNewWebsite(ModuleSource $moduleSourceFrom, ModuleSource $moduleSourceTo);
}
