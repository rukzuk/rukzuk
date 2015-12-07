<?php


namespace Render\MediaUrlHelper\ValidationHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaUrlHelper\UnknownOperation;
use Render\MediaCDNHelper\MediaRequest;

class SecureFileValidationHelper implements ValidationHelperInterface
{
  /**
   * @var bool
   */
  private $writeSecureFileActive;
  /**
   * @var \Render\MediaCDNHelper\MediaCache
   */
  private $mediaCache;

  /**
   * @param MediaCache $mediaCache
   * @param bool       $writeSecureFileActive
   */
  public function __construct(
      MediaCache $mediaCache,
      $writeSecureFileActive = false
  ) {
    $this->mediaCache = $mediaCache;
    $this->writeSecureFileActive = $writeSecureFileActive;
  }

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
    $operations = $mediaRequest->getOperations();
    if (count($operations) <= 0) {
      return true;
    }
    return $this->secureFileExists(
        $mediaItem,
        $operations,
        $mediaRequest->getCdnType()
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeStreamRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->makeRequestValid(
        $mediaItem,
        $operations,
        MediaRequest::TYPE_STREAM
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeDownloadRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->makeRequestValid(
        $mediaItem,
        $operations,
        MediaRequest::TYPE_DOWNLOAD
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeImageRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->makeRequestValid(
        $mediaItem,
        $operations,
        MediaRequest::TYPE_IMAGE
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makeIconRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->makeRequestValid(
        $mediaItem,
        $operations,
        MediaRequest::TYPE_ICON
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   */
  public function makePreviewRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->makeRequestValid(
        $mediaItem,
        $operations,
        MediaRequest::TYPE_PREVIEW
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param string               $cdnType
   */
  protected function makeRequestValid(
      MediaInfoStorageItem $mediaItem,
      array $operations,
      $cdnType
  ) {
    if (!$this->isWriteSecureFileActive()) {
      return;
    }
    if (count($operations) <= 0) {
      return;
    }
    $this->createSecureFile($mediaItem, $operations, $cdnType);
  }

  /**
   * @return boolean
   */
  protected function isWriteSecureFileActive()
  {
    return $this->writeSecureFileActive;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operation
   * @param string               $cdnType
   *
   * @return bool
   */
  protected function secureFileExists(
      MediaInfoStorageItem $mediaItem,
      array $operation,
      $cdnType
  ) {
    $secureFilePath = $this->getSecureFilePath(
        $mediaItem,
        $operation,
        $cdnType
    );
    if (is_file($secureFilePath)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param string               $cdnType
   *
   * @return string
   */
  protected function getSecureFilePath(
      MediaInfoStorageItem $mediaItem,
      array $operations,
      $cdnType
  ) {
    if ($cdnType == MediaRequest::TYPE_ICON) {
      $isIcon = true;
    } else {
      $isIcon = false;
    }
    return $this->mediaCache->getCacheFilePath($mediaItem, $operations, $isIcon);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   * @param string               $cdnType
   */
  protected function createSecureFile(
      MediaInfoStorageItem $mediaItem,
      array $operations,
      $cdnType
  ) {
    $this->prepareSecureFileDirectory($mediaItem);
    $secureFilePath = $this->getSecureFilePath(
        $mediaItem,
        $operations,
        $cdnType
    );
    if (!file_exists($secureFilePath)) {
      $FH = fopen($secureFilePath, 'x');
      if ($FH) {
        fclose($FH);
        // important: clear the file state cache to get the right values for file calls
        clearstatcache(true, $secureFilePath);
      }
    }
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   */
  protected function prepareSecureFileDirectory(MediaInfoStorageItem $mediaItem)
  {
    $this->mediaCache->prepareCache($mediaItem);
  }
}
