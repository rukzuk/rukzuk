<?php


namespace Render\MediaUrlHelper\ValidationHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;

interface ValidationHelperInterface
{
  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param MediaRequest         $mediaRequest
   *
   * @return bool
   */
  public function isValidRequest(
      MediaInfoStorageItem $mediaItem,
      MediaRequest $mediaRequest
  );


  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeStreamRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeDownloadRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeImageRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeIconRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makePreviewRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  );
}
