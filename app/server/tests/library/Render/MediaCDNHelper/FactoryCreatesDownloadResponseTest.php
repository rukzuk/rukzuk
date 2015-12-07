<?php


namespace Render\MediaCDNHelper;

use Seitenbau\FileSystem as FS;
use Test\Render\MediaCDNHelper\AbstractMediaResponseFactoryTestCase;

class FactoryCreatesDownloadResponseTest extends AbstractMediaResponseFactoryTestCase
{
  /**
   * @return array
   */
  public function provider_test_createResponseShouldReturnExpectedResponse()
  {
    return array(
      $this->createTestData1ForDownloadResponse(),
      $this->createTestData2ForDownloadResponse(),
      $this->createTestData3ForDownloadResponse(),
    );
  }

  protected function createTestData1ForDownloadResponse()
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
    $imageToolMock = $this->createImageToolMock(true);
    $mediaContext = $this->createMediaContext($infoStorageMock, $imageToolMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_DOWNLOAD,
      $uploadData, $this->websiteId, array(), true);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\DownloadResponse'
    );
  }

  protected function createTestData2ForDownloadResponse()
  {
    $websiteId = $this->websiteId;
    $mediaId = 'MEDIA-00000000-0000-0000-0000-000000000002-MEDIA';
    $uploadData = time();
    $mediaItem = $this->createMediaItem($websiteId, $mediaId,
      'logo.png', $uploadData, 10, $uploadData,
      FS::joinPath($this->getMediaFileDirectory($websiteId), 'logo.png'),
      FS::joinPath($this->getMediaFileDirectory($websiteId), '/icon_png.png')
    );
    $infoStorageMock = $this->createMediaItemInfoStorageMock(array(
      $mediaItem->getId() => $mediaItem));
    $imageToolMock = $this->createImageToolMock(true);
    $mediaContext = $this->createMediaContext($infoStorageMock, $imageToolMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_DOWNLOAD,
      $uploadData, $this->websiteId, array(),true);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\DownloadResponse'
    );
  }

  /**
   * @return array
   */
  protected function createTestData3ForDownloadResponse()
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
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_DOWNLOAD,
      $uploadData, $this->websiteId, array(),true);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\DownloadResponse'
    );
  }
}