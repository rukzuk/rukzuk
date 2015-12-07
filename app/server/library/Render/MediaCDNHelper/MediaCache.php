<?php


namespace Render\MediaCDNHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;

class MediaCache
{
  /**
   * @var string
   */
  private $cacheBaseDirectory;

  /**
   * @param string $cacheBaseDirectory
   */
  public function __construct($cacheBaseDirectory)
  {
    $this->cacheBaseDirectory = $cacheBaseDirectory;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param bool                 $useIcon
   *
   * @return string
   */
  public function getCacheFilePath(
      MediaInfoStorageItem $mediaItem,
      array $operations = array(),
      $useIcon = false
  ) {
    $baseFilename = $this->getBaseFilenameForCache($mediaItem, $useIcon);
    if (count($operations) > 0) {
      $chainString = $this->convertOperationsToChain($operations);
      $cacheFileName = basename(str_replace('.', '.' . $chainString . '.', $baseFilename));
    } else {
      $cacheFileName = $baseFilename;
    }
    return $this->getCacheDirectory($mediaItem->getWebsiteId()) . $cacheFileName;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param bool                 $useIcon
   * @param string               $content
   *
   * @return bool
   */
  public function createCacheFile(
      MediaInfoStorageItem $mediaItem,
      array $operations = array(),
      $useIcon = false,
      $content = ''
  ) {
    $this->prepareCache($mediaItem);
    $cacheFile = $this->getCacheFilePath($mediaItem, $operations, $useIcon);
    $this->removeCacheFile($cacheFile);
    return (file_put_contents($cacheFile, $content) !== false);
  }

  /**
   * @param \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem $mediaItem
   */
  public function prepareCache(MediaInfoStorageItem $mediaItem)
  {
    $cacheDirectory = $this->getCacheDirectory($mediaItem->getWebsiteId());
    if (!file_exists($cacheDirectory)) {
      mkdir($cacheDirectory);
    }
  }

  /**
   * @param string $cacheFile
   */
  protected function removeCacheFile($cacheFile)
  {
    if (!is_file($cacheFile)) {
      return;
    }
    if (strpos($cacheFile, $this->getCacheBaseDirectory()) !== 0) {
      return;
    }
    unlink($cacheFile);
  }

  /**
   * @return string
   */
  protected function getCacheBaseDirectory()
  {
    return $this->cacheBaseDirectory;
  }

  /**
   * @param string $websiteId
   *
   * @return string
   */
  protected function getCacheDirectory($websiteId)
  {
    return $this->getCacheBaseDirectory() . DIRECTORY_SEPARATOR .
    $websiteId . DIRECTORY_SEPARATOR;
  }

  /**
   * @param $operations
   *
   * @return string
   */
  protected function convertOperationsToChain($operations)
  {
    $chain = array();
    foreach ($operations as $operation) {
      $chain[] = implode('_', $operation);
    }
    return implode('.', $chain);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param                      $useIcon
   *
   * @return string
   */
  private function getBaseFilenameForCache(
      MediaInfoStorageItem $mediaItem,
      $useIcon
  ) {
    if ($useIcon) {
      return basename($mediaItem->getIconFilePath());
    } else {
      $extension = $this->getExtensionFromFilename(
          basename($mediaItem->getFilePath())
      );
      return $mediaItem->getId() . '.' . $extension;
    }
  }

  /**
   * @param $filename
   *
   * @return string
   */
  protected function getExtensionFromFilename($filename)
  {
    return substr(strrchr(strtolower($filename), '.'), 1);
  }
}
