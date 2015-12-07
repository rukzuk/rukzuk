<?php


namespace Render\MediaUrlHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\RequestHelper\HttpRequestInterface;

interface IMediaUrlHelper
{

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return string
   */
  public function getUrl(MediaInfoStorageItem $mediaItem);

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return string
   */
  public function getDownloadUrl(MediaInfoStorageItem $mediaItem);

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   *
   * @return string
   */
  public function getImageUrl(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param string               $iconFilePath
   * @param array                $operations
   *
   * @return string
   */
  public function getIconUrl(
      MediaInfoStorageItem $mediaItem,
      $iconFilePath,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   *
   * @return string
   */
  public function getPreviewUrl(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param HttpRequestInterface $httpRequest
   *
   * @return MediaRequest
   */
  public function getMediaRequest(HttpRequestInterface $httpRequest);
}
