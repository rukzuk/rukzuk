<?php


namespace Render\MediaCDNHelper;

use Test\Render\MediaCDNHelper\AbstractMediaResponseFactoryTestCase;

class FactoryCreatesErrorResponseTest extends AbstractMediaResponseFactoryTestCase
{

  /**
   * @return array
   */
  public function provider_test_createResponseShouldReturnExpectedResponse()
  {
    return array(
      $this->createTestDataForUnknownException(),
    );
  }

  protected function createTestDataForUnknownException()
  {
    $infoStorageMock = $this->createUnknownExceptionInfoStorageMock();
    $mediaContext = $this->createMediaContext($infoStorageMock);
    $httpRequestMock = $this->createHttpRequestMock();
    $mediaRequest = new MediaRequest('', MediaRequest::TYPE_STREAM);
    return array(
      $mediaContext,
      $httpRequestMock,
      $mediaRequest,
      '\Render\MediaCDNHelper\MediaResponse\ErrorResponse'
    );
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  protected function createUnknownExceptionInfoStorageMock()
  {
    $infoStorage = $this->getMockBuilder('\Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage')
      ->disableOriginalConstructor()->getMock();
    $infoStorage->expects($this->any())
      ->method('getItem')
      ->will($this->throwException(new \Exception()));
    return $infoStorage;
  }
} 