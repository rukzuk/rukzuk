<?php


namespace Render\InfoStorage\MediaInfoStorage;

use Render\IconHelper\IIconHelper;
use Render\MediaUrlHelper\IMediaUrlHelper;

/**
 * Class LiveArrayMediaInfoStorage
 *
 * @package Render\InfoStorage\MediaInfoStorage
 */
class LiveArrayMediaInfoStorage extends ArrayBasedMediaInfoStorage
{
  /**
   * @var string
   */
  private $baseMediaDirectory;

  /**
   * @param string                 $baseMediaDirectory
   * @param MediaInfoStorageItem[] $mediaItemMap
   * @param IMediaUrlHelper        $urlHelper
   * @param IIconHelper            $iconHelper
   * @param array                  $albumList
   */
  public function __construct(
      $baseMediaDirectory,
      array $mediaItemMap,
      IMediaUrlHelper $urlHelper,
      IIconHelper $iconHelper,
      array $albumList = array()
  ) {
    $this->baseMediaDirectory = $baseMediaDirectory;
    parent::__construct($mediaItemMap, $urlHelper, $iconHelper, $albumList);
  }

  /**
   * Returns the MediaInfoStorageItem for the given mediaId
   *
   * @param string $mediaID
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return MediaInfoStorageItem
   */
  public function getItem($mediaID)
  {
    $itemData = $this->getMediaDataFromMap($mediaID);
    $filePath = $this->baseMediaDirectory . DIRECTORY_SEPARATOR .
      $itemData['file'];
    return new MediaInfoStorageItem(
        $itemData['id'],
        $filePath,
        $itemData['name'],
        $itemData['size'],
        $itemData['lastModified'],
        $this->getIconHelper()->getIconFilePath($filePath)
    );
  }
}
