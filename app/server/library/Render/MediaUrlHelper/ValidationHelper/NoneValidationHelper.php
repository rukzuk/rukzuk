<?php


namespace Render\MediaUrlHelper\ValidationHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;

class NoneValidationHelper implements ValidationHelperInterface
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
  ) {
    return (count($mediaRequest->getOperations()) <= 0);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeStreamRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    // do nothing (for validation see self::isValidRequest)
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeDownloadRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    // do nothing (for validation see self::isValidRequest)
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeImageRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    // do nothing (for validation see self::isValidRequest)
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeIconRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    // do nothing (for validation see self::isValidRequest)
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makePreviewRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    // do nothing (for validation see self::isValidRequest)
  }
}
