<?php


namespace Render\APIs\APIv1;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaContext;

/**
 * Media database item representation of the APIv1
 *
 * @package Render\APIs\APIv1
 */
class MediaItem
{
  /**
   * @var MediaContext
   */
  private $mediaContext;

  /**
   * @var string
   */
  private $mediaId;

  /**
   * @var MediaInfoStorageItem
   */
  private $mediaInfoStorageItem;

  /**
   * @param MediaContext $mediaContext
   * @param string       $mediaId
   */
  public function __construct(MediaContext $mediaContext, $mediaId)
  {
    $this->mediaContext = $mediaContext;
    $this->mediaId = $mediaId;
    $this->mediaInfoStorageItem = $mediaContext->getMediaInfoStorage()
            ->getItem($this->mediaId);
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->mediaId;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->getMediaInfoStorageItem()->getName();
  }

  /**
   * @return int
   */
  public function getSize()
  {
    return $this->getMediaInfoStorageItem()->getSize();
  }

  /**
   * @return string
   */
  public function getLastModified()
  {
    return $this->getMediaInfoStorageItem()->getLastModified();
  }

  /**
   * @return string
   */
  public function getFilePath()
  {
    return $this->getMediaInfoStorageItem()->getFilePath();
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->getMediaContext()->getMediaInfoStorage()
            ->getUrl($this->getId());
  }

  /**
   * @return MediaInfoStorageItem
   */
  protected function getMediaInfoStorageItem()
  {
    return $this->mediaInfoStorageItem;
  }

  /**
   * @return string
   */
  public function getDownloadUrl()
  {
    return $this->getMediaContext()->getMediaInfoStorage()->getDownloadUrl($this->getId());
  }

  /**
   * Returns an image of the media item.
   *
   * @return MediaImage
   */
  public function getImage()
  {
    return new MediaImage($this->getMediaContext(), $this);
  }

  /**
   * Returns a preview image of the media item or when there is no preview
   * possible an icon for the current media type is shown.
   *
   * @return MediaImage
   */
  public function getPreview()
  {
    try {
      return new MediaImage($this->getMediaContext(), $this);
    } catch (MediaImageInvalidException $_e) {
      return new MediaIcon($this->getMediaContext(), $this);
    }
  }

  /**
   * @return \Render\MediaContext
   */
  protected function getMediaContext()
  {
    return $this->mediaContext;
  }
}
