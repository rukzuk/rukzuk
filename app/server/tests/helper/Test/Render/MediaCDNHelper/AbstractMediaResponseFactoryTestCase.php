<?php


namespace Test\Render\MediaCDNHelper;


use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaResponseFactory;
use Render\MediaContext;
use Seitenbau\FileSystem as FS;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists;
use Seitenbau\Registry;
use Test\Render\AbstractRenderTestCase;
use Test\Render\ImageToolFactory\ImageToolMock;

abstract class AbstractMediaResponseFactoryTestCase extends AbstractRenderTestCase
{
  /**
   * @var string
   */
  protected $websiteId = 'SITE-render00-test-0000-0000-000000000001-SITE';

  /**
   * @test
   * @group        rendering
   * @group        small
   * @group        dev
   * @dataProvider provider_test_createResponseShouldReturnExpectedResponse
   *
   */
  public function test_createResponseShouldReturnExpectedResponse($mediaContext,
                                                                  $httpRequest,
                                                                  $mediaRequest,
                                                                  $expectedClassName)
  {
    //
    // ARRANGE
    //
    $mediaCache = $this->createMediaCacheMock();
    $mediaValidationHelper = $this->createMediaValidationHelperMock();
    $responseFactory = new MediaResponseFactory($mediaContext, $mediaCache,
      $mediaValidationHelper);
    //
    // ACT
    //
    $mediaResponse = $responseFactory->createResponse($httpRequest, $mediaRequest);
    //
    // ASSERT
    //
    $this->assertInstanceOf($expectedClassName, $mediaResponse);
  }

  /**
   * @return array
   */
  abstract public function provider_test_createResponseShouldReturnExpectedResponse();

  /**
   * @param array $mediaItems
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMediaItemInfoStorageMock(array $mediaItems)
  {
    $infoStorage = $this->getMockBuilder('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage')
      ->disableOriginalConstructor()->getMock();
    $infoStorage->expects($this->any())
      ->method('getItem')->will($this->returnCallback(function ($mediaId) use(&$mediaItems) {
        if (array_key_exists($mediaId, $mediaItems)) {
          return $mediaItems[$mediaId];
        } else {
          new MediaInfoStorageItemDoesNotExists();
        }
      }));
    return $infoStorage;
  }

  /**
   * @param array   $params
   * @param array   $headers
   * @param string  $uri
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createHttpRequestMock(array $params = array(),
                                           array $headers = array(),
                                           $uri = '/current/request/uri')
  {
    $httpRequest = $this->getMockBuilder('\Render\RequestHelper\HttpRequestInterface')
      ->disableOriginalConstructor()->getMock();
    $httpRequest->expects($this->any())
      ->method('getUri')->will($this->returnValue($uri));
    $httpRequest->expects($this->any())
      ->method('getParam')->will($this->returnCallback(function ($key, $default = null) use($params) {
        if (array_key_exists($key, $params)) {
          return $params[$key];
        } else {
          return $default;
        }
      }));
    $httpRequest->expects($this->any())
      ->method('getHeader')->will($this->returnCallback(function ($header) use($headers) {
        if (array_key_exists($header, $headers)) {
          return $headers[$header];
        } else {
          return null;
        }
      }));
    return $httpRequest;
  }

  /**
   * @param $infoStorageMock
   * @param $imageToolMock
   *
   * @return MediaContext
   */
  protected function createMediaContext($infoStorageMock, $imageToolMock=null)
  {
    $imageToolFactoryMock = $this->createImageToolFactoryMock($imageToolMock);
    return new MediaContext($infoStorageMock, $imageToolFactoryMock);
  }

  /**
   * @param $websiteId
   * @param $mediaId
   * @param $name
   * @param $uploadData
   * @param $lastModified
   * @param $size
   * @param $filePath
   * @param $iconPath
   *
   * @return MediaInfoStorageItem
   */
  protected function createMediaItem($websiteId, $mediaId, $name, $uploadData,
                                     $lastModified, $size, $filePath, $iconPath)
  {
    return new MediaInfoStorageItem($mediaId, $filePath, $name,
      $uploadData, $size, $lastModified, $iconPath, $websiteId);
  }

  /**
   * @param $imageTool
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createImageToolFactoryMock($imageTool)
  {
    if (is_null($imageTool)) {
      $imageTool = $this->createImageToolMock();
    }
    $imageToolFactory = $this->getMockBuilder('\Render\ImageToolFactory\IImageToolFactory')
      ->disableOriginalConstructor()->getMock();
    $imageToolFactory->expects($this->any())
      ->method('createImageTool')->will($this->returnValue($imageTool));
    return $imageToolFactory;
  }

  /**
   * @param bool $isImageFile
   *
   * @return ImageToolMock
   */
  protected function createImageToolMock($isImageFile=true)
  {
    $imageTool = new ImageToolMock();
    $imageTool->setIsImageFile($isImageFile);
    return $imageTool;
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMediaCacheMock()
  {
    return $this->getMockBuilder('\Render\MediaCDNHelper\MediaCache')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMediaValidationHelperMock()
  {
    return $this->getMockBuilder('\Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @param $websiteId
   *
   * @return string
   */
  protected function getMediaFileDirectory($websiteId)
  {
    return FS::joinPath(Registry::getConfig()->media->files->directory, $websiteId);
  }


}
