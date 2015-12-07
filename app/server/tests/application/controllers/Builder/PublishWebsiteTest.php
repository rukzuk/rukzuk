<?php
namespace Application\Controller\Builder;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\BuilderControllerTestCase;

/**
 * PublishWebsiteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class PublishWebsiteTest extends BuilderControllerTestCase
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
  public function publishShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $buildId = 'v542121-1312298235';
    $request = sprintf(
      '/builder/publish/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $buildId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('websiteid', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidBuildIdsProvider
   */
  public function publishShouldReturnValidationErrorForInvalidBuildIds($buildId)
  {
    $websiteId = 'SITE-bw99fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $request = sprintf(
      '/builder/publish/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('id', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function publishShouldReturnErrorForNonExistingWebsite()
  {
    $websiteId = 'SITE-bw99fg14-3bno-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v8-1312384635';
    $request = sprintf(
      '/builder/publish/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(602, $responseError[0]->code);
  }

  /**
   * @test
   * @group integration
   */
  public function publishShouldReturnErrorForNonExistingWebsiteBuild()
  {
    $websiteId = 'SITE-bw05fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v8-1312384635';

    $this->copyWebsiteBuildsFromStorageToBuildsDirectory($websiteId);

    $request = sprintf(
      '/builder/publish/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );
    $this->dispatch($request);
    $responseBody = $this->getResponseBody();

    $response = new Response($responseBody);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(622, $responseError[0]->code, $responseBody);
  }

  /**
   * @test
   * @group integration
   */
  public function publishWebsiteShouldBeRejectedWhenUserHasNoPublishRights()
  {
    $userlogin = 'publish.website@sbcms.de';
    $password  = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $password);

    $websiteId = 'SITE-bw11fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v8-1312384635';    
    $request = sprintf(
      '/builder/publish/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $buildId
    );

    $this->activateGroupCheck();

    $this->dispatch($request);
    $responseBody = $this->getResponseBody();

    $this->deactivateGroupCheck();

    $response = new Response($responseBody);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code, $responseBody);
  }

  /**
   * @test
   * @group integration
   */
  public function publishShouldPublishAsExpected()
  {
    $this->markTestSkipped('ToDo: create Builder/publisher tests');
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
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
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
    );
  }
}