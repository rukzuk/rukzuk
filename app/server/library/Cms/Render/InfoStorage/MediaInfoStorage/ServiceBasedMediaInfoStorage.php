<?php


namespace Cms\Render\InfoStorage\MediaInfoStorage;

use \Cms\Service\Media as MediaService;
use Render\IconHelper\IIconHelper;
use Render\InfoStorage\MediaInfoStorage\AbstractMediaInfoStorage;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists;
use Render\MediaUrlHelper\IMediaUrlHelper;

class ServiceBasedMediaInfoStorage extends AbstractMediaInfoStorage
{

  /**
   * @var \Cms\Service\Media
   */
  private $mediaService;

  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var
   */
  private $mediaDirectory;

  /**
   * @var array()
   */
  private $cache = array();

  /**
   * @param string          $websiteId
   * @param string          $mediaDirectory
   * @param MediaService    $mediaService
   * @param IMediaUrlHelper $urlHelper
   * @param IIconHelper     $iconHelper
   */
  public function __construct(
      $websiteId,
      $mediaDirectory,
      MediaService $mediaService,
      IMediaUrlHelper $urlHelper,
      IIconHelper $iconHelper
  ) {
    parent::__construct($urlHelper, $iconHelper);
    $this->mediaService = $mediaService;
    $this->websiteId = $websiteId;
    $this->mediaDirectory = $mediaDirectory;
  }

  /**
   * @param string $mediaID
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return MediaInfoStorageItem
   */
  public function getItem($mediaID)
  {
    if (isset($this->cache[$mediaID])) {
      return $this->cache[$mediaID];
    }
    try {
      $mediaItem = $this->mediaService->getById($mediaID, $this->websiteId);
      $infoStorageItem = $this->createMediaInfoStorageItem($mediaItem);
      $this->cache[$mediaItem->getId()] = $infoStorageItem;
      return $infoStorageItem;
    } catch (\Exception $ignore) {
      throw new MediaInfoStorageItemDoesNotExists();
    }
  }

  /**
   * @param string $albumId
   *
   * @return array
   */
  public function getMediaIdsByAlbumIds($albumId)
  {
    $mediaItems = $this->mediaService->getByWebsiteIdAndFilter(
        $this->websiteId,
        array('albumid' => $albumId),
        true
    );
    $mediaIds = array();
    foreach ($mediaItems as $mediaItem) {
      $mediaIds[] = $mediaItem->getId();
    }
    return $mediaIds;
  }

  /**
   * @param array $mediaIds
   */
  public function preloadMediaItems(array $mediaIds)
  {
    if (count($mediaIds) <= 0) {
      return;
    }
    $firstHundredMediaIds = array_slice($mediaIds, 0, 100);
    try {
      $mediaItems = $this->mediaService->getMultipleByIds($firstHundredMediaIds, $this->websiteId);
      foreach ($mediaItems as $mediaItem) {
        if (isset($this->cache[$mediaItem->getId()])) {
          continue;
        }
        $infoStorageItem = $this->createMediaInfoStorageItem($mediaItem);
        $this->cache[$mediaItem->getId()] = $infoStorageItem;
      }
    } catch (\Exception $doNothing) {
    }
  }

  /**
   * @param \Cms\Data\Media $mediaItem
   *
   * @return MediaInfoStorageItem
   */
  protected function createMediaInfoStorageItem($mediaItem)
  {
    $filePath = $this->mediaDirectory . DIRECTORY_SEPARATOR . $mediaItem->getFile();
    return new MediaInfoStorageItem(
        $mediaItem->getId(),
        $filePath,
        $mediaItem->getName(),
        $mediaItem->getSize(),
        $mediaItem->getLastUpdate(),
        $this->getIconHelper()->getIconFilePath($filePath),
        $this->websiteId
    );
  }
}
