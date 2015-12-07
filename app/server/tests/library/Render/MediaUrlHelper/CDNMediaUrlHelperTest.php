<?php


namespace Render\MediaUrlHelper;


use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaUrlHelper\ValidationHelper\NoneValidationHelper;

class CDNMediaUrlHelperTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getUrl()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = 123456789;
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    // ACT
    $url = $cdnMediaUrlHelper->getUrl($this->createMediaInfoStorageItem($mediaId,
      $fileName, $lastModified, $websiteId));
    // ASSERT
    $params = array();
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(3, count($parameter));
    $this->assertEquals($parameter['id'], $mediaId);
    $this->assertEquals($parameter['type'], MediaRequest::TYPE_STREAM);
    $this->assertEquals($parameter['date'], $lastModified);
    $this->assertTrue($result == true);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getDownloadUrl()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    // ACT
    $url = $cdnMediaUrlHelper->getDownloadUrl($this->createMediaInfoStorageItem(
      $mediaId, $fileName, $lastModified, $websiteId));
    // ASSERT
    $params = array();
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(3, count($parameter));
    $this->assertEquals($parameter['id'], $mediaId);
    $this->assertEquals($parameter['type'], MediaRequest::TYPE_DOWNLOAD);
    $this->assertEquals($parameter['date'], $lastModified);
    $this->assertTrue($result == true);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\MediaUrlHelper\UnknownOperation
   */
  public function test_getImageUrl_invalid()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    $operations = array(array('invalid'));
    // ACT
    $cdnMediaUrlHelper->getImageUrl($this->createMediaInfoStorageItem($mediaId,
      $fileName, $lastModified, $websiteId), $operations);
    // ASSERT an exception here
    $this->assertFalse(true);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getImageUrl_just_quality()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    $q = 70;
    $operations = array(array('quality', $q));
    // ACT
    $url = $cdnMediaUrlHelper->getImageUrl($this->createMediaInfoStorageItem(
      $mediaId, $fileName, $lastModified, $websiteId), $operations);
    // ASSERT
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $this->assertTrue($result == true); // convert 1 to true (I hate php)
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(4, count($parameter));
    $chain = $parameter['chain'];
    $this->assertEquals('q' . $q, $chain);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getImageUrl_just_interlaced()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    $i = true;
    $operations = array(array('interlace', $i));
    // ACT
    $url = $cdnMediaUrlHelper->getImageUrl($this->createMediaInfoStorageItem(
      $mediaId, $fileName, $lastModified, $websiteId), $operations);
    // ASSERT
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $this->assertTrue($result == true); // convert 1 to true (I hate php)
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(4, count($parameter));
    $chain = $parameter['chain'];
    $this->assertEquals('i1', $chain);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getImageUrl_chain()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    $operations = array(
      array('crop', 1, 2, 3, 4),
      array('resize', 5, 6, 1),
      array('crop', 7, 8, 9, 10)
    );
    // ACT
    $url = $cdnMediaUrlHelper->getImageUrl($this->createMediaInfoStorageItem(
      $mediaId, $fileName, $lastModified, $websiteId), $operations);
    // ASSERT
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $this->assertTrue($result == true); // convert 1 to true (I hate php)
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(4, count($parameter));
    $chain = $parameter['chain'];
    $this->assertEquals('c1_2_3_4.r5_6_t1.c7_8_9_10', $chain);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   */
  public function test_getImageUrl_full()
  {
    // ARRANGE
    $websiteId = 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE';
    $cdnUrl = 'http://localhost/cdn/get';
    $requestParameterName = 'params';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);
    $fileName = 'testfile.zip';
    $lastModified = '123456789';
    $mediaId = 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA';
    $operations = array(
      array('crop', 1, 2, 3, 4),
      array('resize', 5, 6, 2),
      array('crop', 7, 8, 9, 10),
      array('resize', 11, 12, 0),
      array('quality', 95),
      array('interlace', false),
    );
    // ACT
    $url = $cdnMediaUrlHelper->getImageUrl($this->createMediaInfoStorageItem(
        $mediaId, $fileName, $lastModified, $websiteId), $operations);
    // ASSERT
    $regex = '^' . $cdnUrl . '\?' . $requestParameterName .
            '=([^/]+)&' . $fileName . '$';
    $result = preg_match('|' . $regex . '|i', $url, $params);
    $this->assertTrue($result == true); // convert 1 to true (I hate php)
    $parameter = json_decode(urldecode($params[1]), true);
    $this->assertNotEmpty($parameter);
    $this->assertEquals(4, count($parameter));
    $chain = explode('.', $parameter['chain']);
    $this->assertEquals(6, count($chain));
    $this->assertContains('i0', $chain);
    $this->assertContains('q95', $chain);
    unset($chain[array_search('i0', $chain)]);
    unset($chain[array_search('q95', $chain)]);
    $this->assertEquals(array('c1_2_3_4', 'r5_6_t2', 'c7_8_9_10', 'r11_12_t0'), $chain);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getMediaRequest
   */
  public function test_getMediaRequest($websiteId, $requestParameterName,
                                       $requestParams, $expectedMediaRequest)
  {
    // ARRANGE
    $cdnUrl = 'http://localhost/cdn/get';
    $cdnMediaUrlHelper = $this->createCDNMediaUrlHelper($cdnUrl, $requestParameterName);

    $httpRequest = $this->getMockBuilder('\Render\RequestHelper\HttpRequestInterface')
      ->disableOriginalConstructor()->getMock();
    $httpRequest->expects($this->once())
      ->method('getParam')
      ->with($this->equalTo($requestParameterName))
      ->will($this->returnValue(\json_encode($requestParams)));

    // ACT
    $actualMediaRequest = $cdnMediaUrlHelper->getMediaRequest($httpRequest);

    // ASSERT
    $this->assertInstanceOf('\Render\MediaCDNHelper\MediaRequest', $actualMediaRequest);
    $this->assertEquals($expectedMediaRequest->getMediaId(), $actualMediaRequest->getMediaId());
    $this->assertEquals($expectedMediaRequest->getCdnType(), $actualMediaRequest->getCdnType());
    $this->assertEquals($expectedMediaRequest->getDate(), $actualMediaRequest->getDate());
    $this->assertEquals($expectedMediaRequest->getWebsiteId(), $actualMediaRequest->getWebsiteId());
    $this->assertEquals($expectedMediaRequest->getOperations(), $actualMediaRequest->getOperations());
  }

  /**
   * @return array
   */
  public function provider_test_getMediaRequest()
  {
    $test1Params = array(
      'id'        => 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA',
      'websiteid' => 'SITE-d91a5894-860f-4082-9072-a1f2bdb635e0-SITE',
      'type'      => 'image',
      'date'      => time(),
      'chain'     => 'r1_2_t0.c3_4_5_6.r7_8_t1.c9_10_11_12.r13_14_t2.max15_16.q17.i1',
    );
    $test1 = array(
      $test1Params['websiteid'], 'params', $test1Params,
      new MediaRequest(
        $test1Params['id'], $test1Params['type'],
        $test1Params['date'], $test1Params['websiteid'],
        array(
          array('resize', 1, 2, 0),
          array('crop', 3, 4, 5, 6),
          array('resize', 7, 8, 1),
          array('crop', 9, 10, 11, 12),
          array('resize', 13, 14, 2),
          array('maxsize', 15, 16),
          array('quality', 17),
          array('interlace', 1))
        ),
    );

    $test2Params = array(
      'id'    => 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA',
      'type'  => 'download',
      'date'  => time()+100,
    );
    $test2 = array(
      null, 'foo', $test2Params,
      new MediaRequest(
        $test2Params['id'], $test2Params['type'],
        $test2Params['date'], null, array()
      ),
    );

    $test3Params = array(
      'id'    => 'MEDIA-bfcaf483-88a6-4b0c-b4d7-db42b29e7b74-MEDIA',
    );
    $test3 = array(
      null, 'foo', $test3Params,
      new MediaRequest(
        $test3Params['id'], 'stream',
        0, null, array()
      ),
    );
    return array($test1, $test2, $test3);
  }

  /**
   * @param $mediaId
   * @param $fileName
   * @param $lastModified
   * @param $websiteId
   *
   * @return MediaInfoStorageItem
   */
  protected function createMediaInfoStorageItem($mediaId, $fileName, $lastModified, $websiteId)
  {
    return $mediaItem = new MediaInfoStorageItem($mediaId, $fileName,
      $fileName, 0, $lastModified, 'icon.png', $websiteId);
  }

  /**
   * @param $cdnUrl
   * @param $requestParameterName
   *
   * @return CDNMediaUrlHelper
   */
  protected function createCDNMediaUrlHelper($cdnUrl, $requestParameterName)
  {
    $validationHelper = new NoneValidationHelper();
    return new CDNMediaUrlHelper($validationHelper, $cdnUrl,
      $requestParameterName);
  }
}