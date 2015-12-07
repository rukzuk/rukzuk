<?php


namespace Render\MediaUrlHelper\ValidationHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaCDNHelper\MediaRequest;

class LiveValidationHelper extends SecureFileValidationHelper
{
  /**
   * @var string
   */
  private $mediaModListFilePath;
  /**
   * @var array
   */
  private $mediaModList;

  /**
   * @param string     $mediaModListFilePath
   * @param MediaCache $mediaCache
   * @param bool       $writeSecureFileActive
   */
  public function __construct(
      $mediaModListFilePath,
      MediaCache $mediaCache,
      $writeSecureFileActive = false
  ) {
    $this->mediaModListFilePath = $mediaModListFilePath;
    parent::__construct($mediaCache, $writeSecureFileActive);
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
    $valid = parent::isValidRequest($mediaItem, $mediaRequest);
    if ($valid) {
      return true;
    }
    return $this->isRequestInMediaModList($mediaItem, $mediaRequest);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param MediaRequest         $mediaRequest
   *
   * @return bool
   */
  protected function isRequestInMediaModList(
      MediaInfoStorageItem $mediaItem,
      MediaRequest $mediaRequest
  ) {
    $mediaModList = $this->getMediaModList();
    $uniqueKey = $this->uniqueKey($mediaItem, $mediaRequest);
    return isset($mediaModList[$uniqueKey]);
  }

  /**
   * @return array
   */
  protected function getMediaModList()
  {
    if (!is_array($this->mediaModList)) {
      if (file_exists($this->mediaModListFilePath)) {
        /** @noinspection PhpIncludeInspection */
        $this->mediaModList = @include($this->mediaModListFilePath);
      }
      if (!is_array($this->mediaModList)) {
        $this->mediaModList = array();
      }
    }
    return $this->mediaModList;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param MediaRequest         $mediaRequest
   *
   * @return string
   */
  private function uniqueKey(
      MediaInfoStorageItem $mediaItem,
      MediaRequest $mediaRequest
  ) {
    return md5(json_encode(array(
      'id' => $mediaItem->getId(),
      'type' => $mediaRequest->getCdnType(),
      'operations' => $mediaRequest->getOperations(),
    )));
  }
}
