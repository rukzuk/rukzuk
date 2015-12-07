<?php


namespace Render\InfoStorage\MediaInfoStorage;

use Render\IconHelper\IIconHelper;
use Render\MediaUrlHelper\IMediaUrlHelper;

class ArrayBasedMediaInfoStorage extends AbstractMediaInfoStorage
{

  /**
   * @var MediaInfoStorageItem[]
   */
  private $mediaItemMap;
  /**
   * @var array
   */
  private $albumList;

  /**
   * @param MediaInfoStorageItem[] $mediaItemMap
   * @param IMediaUrlHelper        $urlHelper
   * @param IIconHelper            $iconHelper
   * @param array                  $albumList
   */
  public function __construct(
      array $mediaItemMap,
      IMediaUrlHelper $urlHelper,
      IIconHelper $iconHelper,
      array $albumList = array()
  ) {
    parent::__construct($urlHelper, $iconHelper);
    $this->mediaItemMap = $mediaItemMap;
    $this->albumList = $albumList;
  }

  /**
   * Returns the MediaInfoStorageItem for the given mediaId or null
   * when no such item exists.
   *
   * @param string $mediaID MediaID of the item
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return MediaInfoStorageItem
   */
  public function getItem($mediaID)
  {
    $itemData = $this->getMediaDataFromMap($mediaID);
    return new MediaInfoStorageItem(
        $itemData['id'],
        $itemData['filePath'],
        $itemData['name'],
        $itemData['size'],
        $itemData['lastModified'],
        $this->getIconHelper()->getIconFilePath($itemData['filePath'])
    );
  }

  /**
   * @param string $albumId
   *
   * @return array
   */
  public function getMediaIdsByAlbumIds($albumId)
  {
    if (!isset($this->albumList[$albumId])) {
      return array();
    }
    return $this->albumList[$albumId];
  }

  /**
   * @param string $mediaID
   *
   * @return MediaInfoStorageItem
   * @throws MediaInfoStorageItemDoesNotExists
   */
  protected function getMediaDataFromMap($mediaID)
  {
    if (!isset($this->mediaItemMap[$mediaID])) {
      throw new MediaInfoStorageItemDoesNotExists();
    }
    return $this->mediaItemMap[$mediaID];
  }
}
