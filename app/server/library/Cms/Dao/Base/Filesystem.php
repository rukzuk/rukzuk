<?php


namespace Cms\Dao\Base;

use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Log as SbLog;
use Cms\Exception as CmsException;

/**
 * Base filesystem dao
 *
 * @package Cms\Dao\Base
 */
abstract class Filesystem
{
  const CACHE_PREFIX_INFO = 'info::';
  const CACHE_PREFIX_ENTITY = 'entity::';

  /**
   * @var array
   */
  static protected $internalCache = array();

  /**
   * @var \Zend_Validate_Abstract|null
   */
  protected $idValidator;

  /**
   * @param bool $resetCache
   */
  public function __construct($resetCache = false)
  {
    if ($resetCache) {
      static::_resetCacheInternal();
    } else {
      static::_initCacheInternal();
    }
  }

  /**
   * reset the internal cache
   */
  protected static function _resetCacheInternal()
  {
    $sectionName = static::getCacheSectionName();
    if (is_string($sectionName)) {
      self::$internalCache[$sectionName] = array();
    } else {
      self::$internalCache = array();
    }
  }

  /**
   * init the internal cache
   */
  protected static function _initCacheInternal()
  {
    $sectionName = static::getCacheSectionName();
    if (!is_string($sectionName)) {
      return;
    }
    if (!isset(self::$internalCache[$sectionName])) {
      self::$internalCache[$sectionName] = array();
    }
  }

  /**
   * @return string|null
   */
  protected static function getCacheSectionName()
  {
    return null;
  }

  /**
   * @param string $key
   *
   * @return bool
   */
  protected function hasCacheValue($key)
  {
    $sectionName = static::getCacheSectionName();
    if (!is_string($sectionName)) {
      return false;
    }
    if (!isset(self::$internalCache[$sectionName])) {
      return false;
    }
    if (isset(self::$internalCache[$sectionName][$key])
      || array_key_exists($key, self::$internalCache[$sectionName])) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @param string $key
   * @param mixed  $default
   *
   * @return mixed
   */
  protected function getCacheValue($key, $default = null)
  {
    if (!$this->hasCacheValue($key)) {
      return $default;
    }
    $section = static::getCacheSectionName();
    return self::$internalCache[$section][$key];
  }

  /**
   * @param string $key
   * @param mixed  $value
   */
  protected function setCacheValue($key, $value)
  {
    $sectionName = static::getCacheSectionName();
    if (!is_string($sectionName)) {
      return;
    }
    self::$internalCache[$sectionName][$key] = $value;
  }

  /**
   * resets the internal cache
   */
  public function resetCache()
  {
    static::_resetCacheInternal();
  }

  /**
   * @param AbstractSource $source
   * @param array          $additionalData
   *
   * @return array
   */
  protected function internalGetAll(AbstractSource $source, array $additionalData = array())
  {
    $entities = array();
    $websiteId = $source->getWebsiteId();
    $allBaseInfo = $this->getAllBaseInfo($source);
    foreach ($allBaseInfo as $id => $baseInfo) {
      try {
        $entities[$id] = $this->loadCachedEntity($websiteId, $id, $baseInfo, $additionalData);
      } catch (\Exception $e) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $e, SbLog::ERR);
      }
    }
    return $entities;
  }

  /**
   * @param AbstractSource $source
   * @param string         $id
   * @param array          $additionalData
   *
   * @return array
   * @throws CmsException
   */
  protected function internalGetById(AbstractSource $source, $id, array $additionalData = array())
  {
    $baseInfo = $this->getBaseInfo($source, $id);
    try {
      return $this->loadCachedEntity($source->getWebsiteId(), $id, $baseInfo, $additionalData);
    } catch (\Exception $e) {
      $errorIdData = array('id' => $id, 'websiteid' => $source->getWebsiteId());
      $this->throwGetByIdErrorException(__METHOD__, __LINE__, $errorIdData);
    }
  }

  /**
   * Checks if there is a entity under the given id and Website-Id
   *
   * @param AbstractSource $source
   * @param string         $id
   *
   * @return boolean
   */
  protected function internalExists(AbstractSource $source, $id)
  {
    try {
      $allBaseInfo = $this->getAllBaseInfo($source);
      return isset($allBaseInfo[$id]);
    } catch (\Exception $logOnly) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
    }
    return false;
  }

  /**
   * @param AbstractSource $source
   * @param string         $id
   *
   * @return SourceItem
   * @throws CmsException
   */
  protected function getBaseInfo(AbstractSource $source, $id)
  {
    $baseInfo = $this->getAllBaseInfo($source);
    if (!isset($baseInfo[$id]) || empty($baseInfo[$id])) {
      $errorIdData = array('id' => $id, 'websiteid' => $source->getWebsiteId());
      $this->throwNotExistsException(__METHOD__, __LINE__, $errorIdData);
    }
    return $baseInfo[$id];
  }

  /**
   * @param AbstractSource $source
   *
   * @return SourceItem[]
   */
  protected function getAllBaseInfo(AbstractSource $source)
  {
    $cacheKey = self::CACHE_PREFIX_INFO . $source->getCacheKey();
    $cachedAllBaseInfo = $this->getCacheValue($cacheKey, null);
    if (is_array($cachedAllBaseInfo)) {
      return $cachedAllBaseInfo;
    }

    $allBaseInfo = array();
    foreach ($source->getSources() as $sourceItem) {
      if ($sourceItem instanceof DynamicSourceItem) {
        $this->parseDirectoryForBaseInfo($allBaseInfo, $sourceItem);
      } else {
        $this->validateAndAddSourceItem($allBaseInfo, $sourceItem);
      }
    }

    $this->setCacheValue($cacheKey, $allBaseInfo);
    return $allBaseInfo;
  }

  /**
   * @param SourceItem[] $baseInfo
   * @param SourceItem   $sourceItem
   */
  protected function validateAndAddSourceItem(array &$baseInfo, SourceItem $sourceItem)
  {
    if (!is_dir($sourceItem->getDirectory())) {
      return;
    }

    $entityId = $this->validateDirectoryAndReturnId($sourceItem->getId(), $sourceItem->getDirectory());
    if (empty($entityId)) {
      return;
    }

    if (isset($baseInfo[$entityId])) {
      $sourceItem->setOverwritten(true);
    }
    $baseInfo[$entityId] = $sourceItem;
  }

  /**
   * @param SourceItem[]      $baseInfo
   * @param DynamicSourceItem $sourceItem
   */
  protected function parseDirectoryForBaseInfo(array &$baseInfo, DynamicSourceItem $sourceItem)
  {
    if (!is_dir($sourceItem->getDirectory())) {
      return;
    }

    $iterator = new \DirectoryIterator($sourceItem->getDirectory());
    foreach ($iterator as $entry) {
      if ($entry->isDot() || !$entry->isDir()) {
        continue;
      }
      $newSourceItem = new SourceItem(
          $entry->getFilename(),
          $entry->getPathname(),
          $sourceItem->getUrl() . '/' . $entry->getFilename(),
          $sourceItem->getType(),
          $sourceItem->isReadonly(),
          $sourceItem->isExportable()
      );

      $this->validateAndAddSourceItem($baseInfo, $newSourceItem);
    }
  }

  /**
   * validate directory and return id
   *
   * @param string $directoryName
   * @param string $pathName
   *
   * @return null|string
   */
  protected function validateDirectoryAndReturnId($directoryName, $pathName)
  {
    $manifestFile = FS::joinPath($pathName, $this->getManifestFileName());
    if (!file_exists($manifestFile)) {
      return null;
    }

    $idValidator = $this->getIdValidator();
    if ($idValidator instanceof \Zend_Validate_Abstract) {
      if (!$idValidator->isValid($directoryName)) {
        return null;
      }
    }

    return $directoryName;
  }

  /**
   * @return \Zend_Validate_Abstract|null
   */
  protected function getIdValidator()
  {
    if (!isset($this->idValidator)) {
      $this->idValidator = $this->createIdValidator();
    }
    return $this->idValidator;
  }

  /**
   * @param SourceItem $sourceItem
   *
   * @return object
   * @throws \Exception
   */
  protected function loadManifestFile(SourceItem $sourceItem)
  {
    $manifestFileName = $this->getManifestFileName();
    $manifest = $this->loadJsonFile($sourceItem->getDirectory(), $manifestFileName, new \stdClass());
    if (!($manifest instanceof \stdClass)) {
      throw new \Exception("wrong format of " . $manifestFileName);
    }
    return $manifest;
  }

  /**
   * @param SourceItem $sourceItem
   *
   * @return int
   * @throws \Exception
   */
  protected function getLastModifyTime(SourceItem $sourceItem)
  {
    $manifestFileName = $this->getManifestFileName();
    return $this->getFileModifyTime($sourceItem->getDirectory(), $manifestFileName);
  }

  /**
   * @param string     $directory
   * @param string     $filename
   * @param mixed|null $default
   *
   * @return mixed|null
   * @throws \Exception
   */
  protected function loadJsonFile($directory, $filename, $default = null)
  {
    $dataJson = $this->getFileContent($directory, $filename);
    if (empty($dataJson)) {
      return $default;
    }
    return json_decode($dataJson, false);
  }

  /**
   * @param string $directory
   * @param string $filename
   *
   * @return null|string
   * @throws \Exception
   */
  protected function getFileContent($directory, $filename)
  {
    $filePath = FS::joinPath($directory, $filename);
    if (!file_exists($filePath)) {
      return null;
    }
    return FS::readContentFromFile($filePath);
  }

  /**
   * @param string $directory
   * @param string $filename
   *
   * @return int
   */
  protected function getFileModifyTime($directory, $filename)
  {
    $filePath = FS::joinPath($directory, $filename);
    if (file_exists($filePath)) {
      return filemtime($filePath);
    }
    return 0;
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param array      $additionalData
   *
   * @return object
   */
  protected function loadCachedEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData)
  {
    $cacheKey = self::CACHE_PREFIX_ENTITY . $websiteId . $sourceItem->getCacheKey();
    $cachedEntity = $this->getCacheValue($cacheKey, null);
    if (!is_null($cachedEntity)) {
      return $cachedEntity;
    }
    $entity = $this->loadEntity($websiteId, $id, $sourceItem, $additionalData);
    $this->setCacheValue($cacheKey, $entity);
    return $entity;
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
  abstract protected function loadEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData);

  /**
   * @return string
   */
  abstract protected function getManifestFileName();

  /**
   * @return \Zend_Validate_Abstract|null
   */
  abstract protected function createIdValidator();

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  abstract protected function throwNotExistsException($method, $line, $data);

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  abstract protected function throwGetByIdErrorException($method, $line, $data);
}
