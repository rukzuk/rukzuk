<?php


namespace Render\InfoStorage\MediaInfoStorage;

use Render\IconHelper\IIconHelper;
use Render\MediaUrlHelper\IMediaUrlHelper;

abstract class AbstractMediaInfoStorage implements IMediaInfoStorage
{

  /**
   * @var \Render\MediaUrlHelper\IMediaUrlHelper
   */
  private $urlHelper;

  /**
   * @var \Render\IconHelper\IIconHelper
   */
  private $iconHelper;

  public function __construct(
      IMediaUrlHelper $urlHelper,
      IIconHelper $iconHelper
  ) {
    $this->urlHelper = $urlHelper;
    $this->iconHelper = $iconHelper;
  }

  /**
   * @return \Render\IconHelper\IIconHelper
   */
  protected function getIconHelper()
  {
    return $this->iconHelper;
  }

  /**
   * @param string $mediaId
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getUrl($mediaId)
  {
    return $this->urlHelper->getUrl($this->getItem($mediaId));
  }

  /**
   * @param string $mediaId
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getDownloadUrl($mediaId)
  {
    return $this->urlHelper->getDownloadUrl($this->getItem($mediaId));
  }

  /**
   * @param string     $mediaId
   * @param array      $operations
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getImageUrl(
      $mediaId,
      array $operations = array()
  ) {
    return $this->urlHelper->getImageUrl($this->getItem($mediaId), $operations);
  }

  /**
   * @param string     $mediaId
   * @param array      $operations
   *
   * @return string
   */
  public function getIconUrl($mediaId, array $operations = array())
  {
    return $this->urlHelper->getIconUrl(
        $this->getItem($mediaId),
        $this->getItem($mediaId)->getIconFilePath(),
        $operations
    );
  }

  /**
   * @param string     $mediaId
   * @param array      $operations
   *
   * @return string
   */
  public function getPreviewUrl($mediaId, array $operations = array())
  {
    return $this->urlHelper->getPreviewUrl($this->getItem($mediaId), $operations);
  }
}
