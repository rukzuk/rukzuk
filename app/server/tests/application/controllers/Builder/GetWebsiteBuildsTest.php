<?php
namespace Application\Controller\Builder;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\BuilderControllerTestCase;

/**
 * GetWebsiteBuildsTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetWebsiteBuildsTest extends BuilderControllerTestCase
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
  public function getWebsiteBuildsShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $request = sprintf(
      '/builder/getwebsitebuilds/params/{"id":"%s"}',
      $websiteId
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
   */
  public function getWebsiteBuildsShouldReturnEmptyBuildsWhenNoBuildsAvailable()
  {
    $websiteId = 'SITE-bw02fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    
    $this->createWebsiteBuildsDirectory($websiteId);
    
    $request = sprintf(
      '/builder/getwebsitebuilds/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('builds', $responseData);
    $this->assertEmpty($responseData->builds);
  }
  
  /**
   * @test
   * @group integration
   */
  public function getWebsiteBuildsShouldReturnBuilds()
  {
    $comment = 'test_website_build_comment';
    $websiteId = 'SITE-bw02fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $config = Registry::getConfig();
    $expectedWebsiteBuildsCount = (int) $config->builds->threshold;
    
    $request = sprintf(
      '/builder/getwebsitebuilds/params/{"websiteid":"%s"}',
      $websiteId
    );
    
    $this->copyWebsiteBuildsFromStorageToBuildsDirectory($websiteId);
    
    $this->dispatch($request);
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $responseData = $response->getData();
    
    $this->assertObjectHasAttribute('builds', $responseData);
    $this->assertNotEmpty($responseData->builds);
    $this->assertInternalType('array', $responseData->builds);
    $this->assertSame($expectedWebsiteBuildsCount, count($responseData->builds));
    foreach ($responseData->builds as $nextBuild) {
      $this->assertObjectHasAttribute('id', $nextBuild);
      $this->assertObjectHasAttribute('version', $nextBuild);
      $this->assertObjectHasAttribute('timestamp', $nextBuild);
      $this->assertObjectHasAttribute('comment', $nextBuild);
      $this->assertObjectHasAttribute('creatorName', $nextBuild);
      $this->assertObjectHasAttribute('lastPublished', $nextBuild);
      $this->assertInternalType('object', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('id', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('status', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('timestamp', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('percent', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('remaining', $nextBuild->lastPublished);
        $this->assertObjectHasAttribute('msg', $nextBuild->lastPublished);
    }
  }
  
  /**
   * @test
   * @group integration
   */
  public function getWebsiteBuildsShouldBeRejectedWhenUserHasNoPublishRights()
  {
    $userlogin = 'getbuilds@sbcms.de';
    $password  = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $password);
    
    $websiteId = 'SITE-bw11fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $request = sprintf(
      '/builder/getwebsitebuilds/params/{"websiteid":"%s"}',
      $websiteId
    );
    
    $this->activateGroupCheck();
    
    $this->dispatch($request);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code);
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
}