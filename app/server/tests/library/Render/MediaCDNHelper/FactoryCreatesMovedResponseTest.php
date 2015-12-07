<?php


namespace Render\MediaCDNHelper;

use Seitenbau\FileSystem as FS;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Test\Render\MediaCDNHelper\AbstractMediaResponseFactoryTestCase;

class FactoryCreatesMovedResponseTest extends AbstractMediaResponseFactoryTestCase
{
  /**
   * @return array
   */
  public function provider_test_createResponseShouldReturnExpectedResponse()
  {
    return array(
      $this->createTestDataForMovedResponse(),
    );
  }

  /**
   * @return array
   */
  protected function createTestDataForMovedResponse()
  {
    $websiteId = $this->websiteId;
    $mediaId = 'MEDIA-00000000-0000-0000-0000-000000000001-MEDIA';
    $uploadData = time()-3600;
    $mediaItem = new MediaInfoStorageItem($mediaId,
      FS::joinPath($this->getMediaFileDirectory($websiteId), 'logo.jpg'),
      'logo.jpg', time(), 10, time(),
      FS::joinPath($this->getMediaFileDirectory($websiteId), '/icon_jpg.png'),
      $websiteId);
    $infoStorageMock = $this->createMediaItemInfoStorageMock(array(
      $mediaItem->getId() => $mediaItem));
    $mediaContext = $this->createMediaContext($infoStorageMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_STREAM,
      $uploadData-1);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\MovedResponse'
    );
  }
}