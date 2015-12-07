<?php


namespace Cms\Publisher\Type;


use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\TransactionTestCase;

class ExternalrukzukserviceTest extends TransactionTestCase
{
  /**
   * @test
   * @group small
   * @group library
   */
  public function test_publishShouldSetStatusFromJsonResponseAsExpected()
  {
    // ARRANGE
    $publisherResponse = array(
      'id' => 'RECEIVED_ID',
      'status' => 'RECEIVED_STATUS',
      'timestamp' => microtime(),
      'percent' => 75,
      'remaining' => 123,
    );
    $websiteId = 'THIS_IS_THE_WEBSITE_ID';
    $publishingId = 'THIS_IS_THE_PUBLISHING_ID';
    $publishingFilePath = 'THIS_IS_THE_PUBLISHING_FILE_PATH';
    $publishConfig = array(
      'type' => 'internal',
      'cname' => 'my.live.domain',
    );
    $serviceUrls = array(
      'download' => '/service/endpoint/for/download/website/zip',
      'status'   => '/service/endpoint/for/status/request',
    );

    $httpClientMock = $this->getHttpClientMock();
    $httpClientMock->mock_addMethodReturn('callUrl', 200, array());
    $httpClientMock->mock_addMethodReturn('callUrl', 200, array('responseBody' => json_encode($publisherResponse)));
    $publisherMock = $this->getPublisherMock($httpClientMock);

    // ACT
    $actualPublishedStatus = $publisherMock->publish($websiteId, $publishingId, $publishingFilePath,
      $publishConfig, $serviceUrls);

    // ASSERT
    $this->assertEquals($publishingId, $actualPublishedStatus->getId());
    $this->assertEquals($publisherResponse['status'], $actualPublishedStatus->getStatus());
    $this->assertEquals($publisherResponse['timestamp'], $actualPublishedStatus->getTimestamp());
    $this->assertEquals($publisherResponse['percent'], $actualPublishedStatus->getPercent());
    $this->assertEquals($publisherResponse['remaining'], $actualPublishedStatus->getRemaining());
  }

  /**
   * @test
   * @group small
   * @group library
   *
   * @dataProvider test_publishShouldCallUrlsAsExpectedProvider
   */
  public function test_publishShouldCallUrlsAsExpected($websiteId, $publishingId, $publishingFilePath,
                                                      $publishConfig, $serviceUrls, $expectedToken)
  {
    // ARRANGE
    $expectedHost = 'TEST_PUBLISHER_HOST';
    $expectedEndpoints = array(
      'publish' => array(
        'url' => '/THIS/IS/THE/PUBLISHER/ADD/URL/',
        'timeout' => 11,
        'maxRedirects' => 11,
      ),
      'status' => array(
        'url' => '/THIS/IS/THE/PUBLISHER/STATUS/URL/',
        'timeout' => 22,
        'maxRedirects' => 22,
      ),
    );
    ConfigHelper::removeValue(array('publisher', 'externalrukzukservice', 'hosts'));
    ConfigHelper::mergeIntoConfig(array('publisher' => array('externalrukzukservice' => array(
      'hosts' => array($expectedHost),
      'endpoint' => $expectedEndpoints
    ))));
    $httpClientMock = $this->getHttpClientMock();
    $httpClientMock->mock_addMethodReturn('callUrl', 200, array());
    $httpClientMock->mock_addMethodReturn('callUrl', 200, array());
    $publisherMock = $this->getPublisherMock($httpClientMock);
    $publisherVersion = $publisherMock::VERSION;

    // ACT
    $actualPublishedStatus = $publisherMock->publish($websiteId, $publishingId, $publishingFilePath,
      $publishConfig, $serviceUrls);

    // ASSERT
    $methodCalls = $httpClientMock->mock_getMethodCalls();
    $this->assertCount(2, $methodCalls);

    // assert publish call
    $publishRequest = $expectedEndpoints['publish'];
    $publishRequest['params'] = array(
      'data' => json_encode($publishConfig),
      'client_version' => $publisherVersion,
      'token' => $expectedToken,
      'download_url' => $serviceUrls['download'],
      'status_url' => $serviceUrls['status'],
    );
    $this->assertHttpClientCall($methodCalls[0], array(
      'host' => $expectedHost,
      'request' => $publishRequest
    ));

    // assert status call
    $statusRequest = $expectedEndpoints['status'];
    $statusRequest['params'] = array(
      'client_version' => $publisherVersion,
      'token' => $expectedToken,
      'download_url' => $serviceUrls['download'],
      'status_url' => $serviceUrls['status'],
    );
    $this->assertHttpClientCall($methodCalls[1], array(
      'host' => $expectedHost,
      'request' => $statusRequest,
    ));
  }

  /**
   * @return array
   */
  public function test_publishShouldCallUrlsAsExpectedProvider()
  {
    return array(
      array(
        'INTERNAL_THIS_IS_THE_WEBSITE_ID',
        'INTERNAL_THIS_IS_THE_PUBLISHING_ID',
        'INTERNAL_THIS_IS_THE_PUBLISHING_FILE_PATH',
        array(
          'type' => 'internal',
          'cname' => 'internal.my.live.domain.intern',
        ),
        array(
          'download' => '/INTERNAL/service/endpoint/for/download/website/zip',
          'status'   => '/INTERNAL/service/endpoint/for/status/request',
        ),
        'THIS_IS_THE_PUBLISHER_TEST_TOKEN_FOR_TYPE_INTERNAL',
      ),
      array(
        'EXTERNAL_THIS_IS_THE_WEBSITE_ID',
        'EXTERNAL_THIS_IS_THE_PUBLISHING_ID',
        'EXTERNAL_THIS_IS_THE_PUBLISHING_FILE_PATH',
        array(
          'type' => 'external',
          'protocol' => 'sftp',
          'host' => 'external.my.external.host',
        ),
        array(
          'download' => '/EXTERNA/service/endpoint/for/download/website/zip',
          'status'   => '/EXTERNA/service/endpoint/for/status/request',
        ),
        'THIS_IS_THE_PUBLISHER_TEST_TOKEN_FOR_TYPE_EXTERNAL',
      )
    );
  }

  /**
   * @param \Test\Rukzuk\Mock\Seitenbau\HttpMock $httpClientMock
   *
   * @return \Cms\Publisher\Type\Externalrukzukservice
   */
  protected function getPublisherMock($httpClientMock)
  {
    $publishMock = $this->getMockBuilder('\Cms\Publisher\Type\Externalrukzukservice')
      ->setMethods(array('getHttpClient'))
      ->getMock();
    $publishMock->expects($this->any())->method('getHttpClient')
      ->will($this->returnValue($httpClientMock));
    return $publishMock;
  }

  /**
   * @return \Test\Rukzuk\Mock\Seitenbau\HttpMock
   */
  protected function getHttpClientMock()
  {
    return new \Test\Rukzuk\Mock\Seitenbau\HttpMock();
  }

  /**
   * @param array $actualCall
   * @param array $expectedCall
   */
  protected function assertHttpClientCall($actualCall, $expectedCall)
  {
    $this->assertCount(6, $actualCall);
    $this->assertEquals('callUrl', $actualCall[0]);
    $this->assertEquals($expectedCall['host'], $actualCall[1]);
    $this->assertNull($actualCall[3]);
    $this->assertNull($actualCall[4]);
    $this->assertEquals('POST', $actualCall[5]);
    $actualCallRequest = $actualCall[2];
    $this->assertEquals($expectedCall['request'], $actualCallRequest);
  }

}
 