<?php


namespace Render\MediaCDNHelper;

use Seitenbau\FileSystem as FS;
use Test\Render\MediaCDNHelper\AbstractMediaResponseFactoryTestCase;

class FactoryCreatesIconResponseTest extends AbstractMediaResponseFactoryTestCase
{
  /**
   * @return array
   */
  public function provider_test_createResponseShouldReturnExpectedResponse()
  {
    return array(
      $this->createTestData1ForIconResponse(),
      $this->createTestData2ForIconResponse(),
    );
  }

  /**
   * @return array
   */
  protected function createTestData1ForIconResponse()
  {
    $websiteId = $this->websiteId;
    $mediaId = 'MEDIA-00000000-0000-0000-0000-000000000001-MEDIA';
    $uploadData = time();
    $mediaItem = $this->createMediaItem($websiteId, $mediaId,
      'logo.jpg', $uploadData, 10, $uploadData,
      FS::joinPath($this->getMediaFileDirectory($websiteId), 'logo.jpg'),
      FS::joinPath($this->getMediaFileDirectory($websiteId), '/icon_jpg.png')
    );
    $infoStorageMock = $this->createMediaItemInfoStorageMock(array(
      $mediaItem->getId() => $mediaItem));
    $imageToolMock = $this->createImageToolMock(false);
    $mediaContext = $this->createMediaContext($infoStorageMock, $imageToolMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_ICON,
      $uploadData, $this->websiteId, array(),true);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\IconResponse'
    );
  }

  /**
   * @return array
   */
  protected function createTestData2ForIconResponse()
  {
    $websiteId = $this->websiteId;
    $mediaId = 'MEDIA-00000000-0000-0000-0000-000000000003-MEDIA';
    $uploadData = time();
    $mediaItem = $this->createMediaItem($websiteId, $mediaId,
      'logo.pdf', $uploadData, 10, $uploadData,
      FS::joinPath($this->getMediaFileDirectory($websiteId), 'logo.pdf'),
      FS::joinPath($this->getMediaFileDirectory($websiteId), '/icon_pdf.png')
    );
    $infoStorageMock = $this->createMediaItemInfoStorageMock(array(
      $mediaItem->getId() => $mediaItem));
    $imageToolMock = $this->createImageToolMock(false);
    $mediaContext = $this->createMediaContext($infoStorageMock, $imageToolMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_ICON,
      $uploadData, $this->websiteId, array(),true);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\IconResponse'
    );
  }
}