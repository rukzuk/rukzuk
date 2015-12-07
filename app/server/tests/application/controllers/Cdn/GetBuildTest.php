<?php
namespace Application\Controller\Cdn;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\BuilderControllerTestCase;
/**
 * GetBuildTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetBuildTest extends BuilderControllerTestCase
{
  protected function tearDown()
  {
    $this->removeWebsiteBuilds();
    parent::tearDown();
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function getBuildShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $buildId = 'v1-1312298235';
    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );

    $this->dispatch($request);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('websiteid', $errorData[0]->param->field);

    $this->assertEquals(0, $this->getResponse()->getTestCallbackCallCount());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidBuildIdsProvider
   */
  public function getBuildShouldReturnValidationErrorForInvalidBuildIds($buildId)
  {
    $websiteId = 'SITE-463abae8-nd4c-43ce-8323-4e699d6671e7-SITE';
    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );

    $this->dispatch($request);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('id', $errorData[0]->param->field);

    $this->assertEquals(0, $this->getResponse()->getTestCallbackCallCount());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidBuildnamesProvider
   */
  public function getBuildShouldReturnValidationErrorForInvalidBuildnames($name)
  {
    $websiteId = 'SITE-463abae8-nd4c-43ce-8323-4e699d6671e7-SITE';
    $buildId = 'v1-1312298235';
    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $buildId,
      $name
    );

    $this->dispatch($request);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('name', $errorData[0]->param->field);

    $this->assertEquals(0, $this->getResponse()->getTestCallbackCallCount());
  }
  /**
   * @test
   * @group integration
   */
  public function getBuildShouldReturnErrorForNonExistingWebsite()
  {
    $nonExistingWebsiteId = 'SITE-463abae8-nooo-43ce-8323-4e699d6671e7-SITE';
    
    $buildId = 'v1-1312298235';
    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s"}',
      $nonExistingWebsiteId,
      $buildId
    );

    $this->dispatch($request);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(602, $responseError[0]->code);

    $this->assertEquals(0, $this->getResponse()->getTestCallbackCallCount());
  }
  /**
   * @test
   * @group integration
   */
  public function getBuildShouldReturnErrorForNonExistingBuildId()
  {
    $websiteId = 'SITE-bw13fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $nonExistingBuildId = 'v6-1312298235';

    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $nonExistingBuildId
    );

    $this->dispatch($request);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(622, $responseError[0]->code);

    $this->assertEquals(0, $this->getResponse()->getTestCallbackCallCount());
  }
  /**
   * @test
   * @group integration
   */
  public function getBuildShouldReturnExpectedBuildfileUnnamed()
  {
    $websiteId = 'SITE-bw13fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v2-1312125435';
    $expectedBuildFilePath = Registry::getConfig()->builds->directory
      . DIRECTORY_SEPARATOR . $websiteId .
      DIRECTORY_SEPARATOR . $buildId . '.zip';

    $this->copyWebsiteBuildsFromStorageToBuildsDirectory($websiteId);

    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );

    $this->dispatch($request);

    $response = $this->getResponse();

    $this->assertSame(200, $response->getHttpResponseCode());
    $reponseHeaders = $response->getHeaders();

    $expectedHeaders = array(
      'Content-Type' => 'application/zip',
      'Content-Disposition' => 'inline; filename="v2-1312125435.zip"',
    );

    $actualHeadersLeaned = array();
    foreach ($reponseHeaders as $reponseHeader) {
      $actualHeadersLeaned[$reponseHeader['name']] = $reponseHeader['value'];
    }

    foreach ($expectedHeaders as $expectedHeaderName => $expectedHeaderValue) {
      $this->assertArrayHasKey($expectedHeaderName, $actualHeadersLeaned);
      $this->assertSame(
        $expectedHeaderValue,
        $actualHeadersLeaned[$expectedHeaderName]
      );
    }

    $this->assertEquals(1, $this->getResponse()->getTestCallbackCallCount());

    $callbackOutput = $response->getTestCallbackOutput();
    $this->assertEquals(file_get_contents($expectedBuildFilePath), $callbackOutput[0]);
  }
  /**
   * @test
   * @group integration
   */
  public function getBuildShouldReturnExpectedBuildfileNamed()
  {
    $websiteId = 'SITE-bw13fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v4-1311693435';
    $name = 'text_example_build.v1';
    $expectedBuildFilePath = Registry::getConfig()->builds->directory
      . DIRECTORY_SEPARATOR . $websiteId .
      DIRECTORY_SEPARATOR . $buildId . '.zip';

    $this->copyWebsiteBuildsFromStorageToBuildsDirectory($websiteId);

    $request = sprintf(
      '/cdn/getbuild/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $buildId,
      $name
    );

    $this->dispatch($request);

    $response = $this->getResponse();

    $this->assertSame(200, $response->getHttpResponseCode());
    $reponseHeaders = $response->getHeaders();

    $expectedHeaders = array(
      'Content-Type' => 'application/zip',
      'Content-Disposition' => 'inline; filename="' . $name . '.zip"',
    );

    $actualHeadersLeaned = array();
    foreach ($reponseHeaders as $reponseHeader) {
      $actualHeadersLeaned[$reponseHeader['name']] = $reponseHeader['value'];
    }

    foreach ($expectedHeaders as $expectedHeaderName => $expectedHeaderValue) {
      $this->assertArrayHasKey($expectedHeaderName, $actualHeadersLeaned);
      $this->assertSame(
        $expectedHeaderValue,
        $actualHeadersLeaned[$expectedHeaderName]
      );
    }

    $this->assertEquals(1, $this->getResponse()->getTestCallbackCallCount());

    $callbackOutput = $response->getTestCallbackOutput();
    $this->assertEquals(file_get_contents($expectedBuildFilePath), $callbackOutput[0]);
  }

  /**
   * @return array
   */
  public function invalidBuildIdsProvider()
  {
    return array(
      array(''),
      array(null),
      array('v0-1312298235'),
      array('vagsbd-1312298235'),
      array('v12-red298235'),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
  /**
   * @return array
   */
  public function invalidBuildnamesProvider()
  {
    return array(
      array(str_repeat('1234567890', 25).'123456'), // 256 Zeichen
    );
  }
}