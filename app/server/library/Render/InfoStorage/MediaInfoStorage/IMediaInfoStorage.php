<?php


namespace Render\InfoStorage\MediaInfoStorage;

interface IMediaInfoStorage
{

  /**
   * @param string $mediaID
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return MediaInfoStorageItem
   */
  public function getItem($mediaID);

  /**
   * @param string      $mediaId
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getUrl($mediaId);

  /**
   * @param string      $mediaId
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getDownloadUrl($mediaId);

  /**
   * @param string      $mediaId
   * @param array       $operations
   *
   * @throws MediaInfoStorageItemDoesNotExists
   * @return string
   */
  public function getImageUrl(
      $mediaId,
      array $operations = array()
  );

  /**
   * Icon of the file type
   * @param string $mediaId
   * @param array $operations
   *
   * @return string
   */
  public function getIconUrl($mediaId, array $operations = array());


  /**
   * Preview of the image (or icon if non-image type)
   * @param $mediaId
   * @param array $operations
   * @return mixed
   */
  public function getPreviewUrl($mediaId, array $operations = array());

  /**
   * @param string $albumId
   *
   * @return array
   */
  public function getMediaIdsByAlbumIds($albumId);
}
