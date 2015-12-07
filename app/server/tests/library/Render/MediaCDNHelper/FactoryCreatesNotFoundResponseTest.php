<?php


namespace Render\MediaCDNHelper;

use Test\Render\MediaCDNHelper\AbstractMediaResponseFactoryTestCase;
use Seitenbau\FileSystem as FS;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists;

class FactoryCreatesNotFoundResponseTest extends AbstractMediaResponseFactoryTestCase
{

  /**
   * @return array
   */
  public function provider_test_createResponseShouldReturnExpectedResponse()
  {
    return array(
      $this->createTestDataForItemNotExists(),
      $this->createTestDataForMediaItemFileNotExists(),
    );
  }

  /**
   * @return array
   */
  protected function createTestDataForItemNotExists()
  {
    $infoStorageMock = $this->createNotExitingItemInfoStorageMock();
    $mediaContext = $this->createMediaContext($infoStorageMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest('', MediaRequest::TYPE_STREAM);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\NotFoundResponse'
    );
  }

  /**
   * @return array
   */
  protected function createTestDataForMediaItemFileNotExists()
  {
    $websiteId = $this->websiteId;
    $mediaId = 'MEDIA-00000000-0000-0000-0000-00not0exists-MEDIA';
    $mediaItem = $this->createMediaItem($websiteId, $mediaId,
      'filenotexists.jpg', time(), 10, time(),
      FS::joinPath($this->getMediaFileDirectory($websiteId), 'filenotexists.jpg'),
      FS::joinPath($this->getMediaFileDirectory($this->websiteId), 'iconnotexists.jpg')
    );
    $infoStorageMock = $this->createMediaItemInfoStorageMock(array(
      $mediaItem->getId() => $mediaItem));
    $mediaContext = $this->createMediaContext($infoStorageMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest($mediaId, MediaRequest::TYPE_STREAM);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\NotFoundResponse'
    );
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createNotExitingItemInfoStorageMock()
  {
    $infoStorage = $this->getMockBuilder('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage')
      ->disableOriginalConstructor()->getMock();
    $infoStorage->expects($this->any())
      ->method('getItem')
      ->will($this->throwException(new MediaInfoStorageItemDoesNotExists()));
    return $infoStorage;
  }
}