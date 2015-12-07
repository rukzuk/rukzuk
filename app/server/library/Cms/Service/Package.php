<?php


namespace Cms\Service;

use Cms\Dao\Base\AbstractSourceItem;
use Cms\Dao\Base\DynamicSourceItem;
use Cms\Dao\Package\Source as PackageSource;
use Cms\Exception;
use Cms\Service\Base\Dao as DaoServiceBase;
use Seitenbau\Cache\StaticCache;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;

/**
 * Class Package
 *
 * @package Cms\Service
 *
 * @method \Cms\Dao\Package getDao
 */
class Package extends DaoServiceBase
{
  const SUBDIR_REPO = '_packages';
  const SUBDIR_LOCAL = 'packages';

  const CACHE_PREFIX_SOURCE = 'src::';

  /**
   * @var StaticCache
   */
  protected $cache;

  /**
   * resets the package cache
   */
  public function resetCache()
  {
    $this->getCache()->resetCache();
    $this->getDao()->resetCache();
  }

  /**
   * returns all packages of the given website
   *
   * @param   string $websiteId
   *
   * @return  \Cms\Data\Package[]
   */
  public function getAll($websiteId)
  {
    $source = $this->getPackageSource($websiteId);
    return $this->getDao()->getAll($source);
  }

  /**
   * @param $websiteId
   */
  public function deleteByWebsiteId($websiteId)
  {
    $sources = $this->getLocalSources($websiteId);
    foreach ($sources as $sourceItem) {
      $sourceItemDirectory = $sourceItem->getDirectory();
      if (!empty($sourceItemDirectory) && file_exists($sourceItemDirectory)) {
        FS::rmdir($sourceItemDirectory);
      }
    }
    $this->getDao()->resetCache();
  }

  /**
   * @param string $fromWebsiteId
   * @param string $toWebsiteId
   */
  public function copyToNewWebsite($fromWebsiteId, $toWebsiteId)
  {
    $moduleConfig = Registry::getConfig()->item->data;
    $dataDirectory = $moduleConfig->directory;
    $sourceDirectory = FS::joinPath($dataDirectory, $fromWebsiteId, self::SUBDIR_LOCAL);
    $destinationDirectory = FS::joinPath($dataDirectory, $toWebsiteId, self::SUBDIR_LOCAL);
    FS::createDirIfNotExists($destinationDirectory, true);
    if (is_dir($sourceDirectory)) {
      FS::copyDir($sourceDirectory, $destinationDirectory);
    }
    $this->getDao()->resetCache();
  }

  /**
   * @param $websiteId
   *
   * @return string
   */
  public function getDirectoryFormImportingLocalPackages($websiteId)
  {
    $dataDirectory = Registry::getConfig()->item->data->directory;
    return FS::joinPath($dataDirectory, $websiteId, self::SUBDIR_LOCAL);
  }

  /**
   * @param string $websiteId
   *
   * @return PackageSource
   */
  protected function getPackageSource($websiteId)
  {
    $cacheKey = self::CACHE_PREFIX_SOURCE . $websiteId;
    $packageSource = $this->getCache()->getValue($cacheKey);
    if (isset($packageSource)) {
      return $packageSource;
    }
    $globalSources = $this->getGlobalSources($websiteId);
    $localSources = $this->getLocalSources($websiteId);
    $sources = array_merge($globalSources, $localSources);
    $packageSource = new PackageSource($websiteId, $sources);
    $this->getCache()->setValue($cacheKey, $packageSource);
    return $packageSource;
  }

  /**
   * @param string $websiteId
   *
   * @return array
   */
  protected function getGlobalSources($websiteId)
  {
    $sources = array();
    try {
      $globalSetSource = $this->getUsedSetSource($websiteId);
      foreach ($globalSetSource->getSources() as $globalSource) {
        $sources[] = new DynamicSourceItem(
            $globalSource->getDirectory(),
            $globalSource->getUrl(),
            $globalSource->getType(),
            $globalSource->isReadonly(),
            $globalSource->isExportable()
        );
      }
    } catch (Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return $sources;
  }

  /**
   * @param $websiteId
   *
   * @return \Cms\Dao\Website\GlobalSetSource
   */
  protected function getUsedSetSource($websiteId)
  {
    /** @var \Cms\Service\Website $websiteService */
    $websiteService = $this->getService('Website');
    return $websiteService->getUsedSetSource($websiteId);
  }

  /**
   * @param $websiteId
   *
   * @return AbstractSourceItem[]
   */
  protected function getLocalSources($websiteId)
  {
    $sources = array();
    $moduleConfig = Registry::getConfig()->item->data;
    $dataDirectory = $moduleConfig->directory;
    $dataWebPath = $moduleConfig->webpath;
    $sources[] = new DynamicSourceItem(
        FS::joinPath($dataDirectory, $websiteId, self::SUBDIR_LOCAL),
        $dataWebPath . '/' . $websiteId . '/' . self::SUBDIR_LOCAL,
        DynamicSourceItem::SOURCE_LOCAL,
        true,
        true
    );
    return $sources;
  }

  /**
   * @return StaticCache
   */
  protected function getCache()
  {
    if (!isset($this->cache)) {
      $this->cache = new StaticCache(__CLASS__);
    }
    return $this->cache;
  }
}
