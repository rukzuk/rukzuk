<?php
namespace Application\Controller\Builder;

use Seitenbau\Registry as Registry,   
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\BuilderControllerTestCase;

/**
 * BuildWebsiteTest
 *
 * @package      Test
 * @subpackage   Controller 
 */
class BuildWebsiteTest extends BuilderControllerTestCase
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
  public function buildWebsiteShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $request = sprintf(
      '/builder/buildwebsite/params/{"websiteId":"%s"}',
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
   * @dataProvider invalidCommentsProvider
   */
  public function buildWebsiteShouldReturnValidationErrorForInvalidComments($comment)
  {
    $websiteId = 'SITE-bw00fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $request = sprintf(
      '/builder/buildwebsite/params/{"websiteId":"%s","comment":"%s"}',
      $websiteId,
      $comment
    );
    
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    
    $responseError = $response->getError();
    
    $this->assertSame('comment', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function buildWebsiteShouldReturnErrorForNonExistingWebsite()
  {
    $websiteId = 'SITE-ne00fg14-3bbe-4301-ar51-f58f6ef17j8e-SITE';
    $request = sprintf(
      '/builder/buildwebsite/params/{"websiteId":"%s","comment":"%s"}',
      $websiteId,
      'test_website_build_0'
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
  public function buildWebsiteShouldBeRejectedWhenUserHasNoPublishRights()
  {
    $userlogin = 'build.website@sbcms.de';
    $password  = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $password);
    
    $websiteId = 'SITE-bw11fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $comment = 'sometext';
    $request = sprintf(
      '/builder/buildwebsite/params/{"websiteId":"%s","comment":"%s"}',
      $websiteId,
      $comment
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
   * @test
   * @group integration
   */
  public function buildWebsiteShouldReturnExpectedResponseFormat()
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
  public function invalidCommentsProvider()
  {
    return array(
      array(str_repeat('abc', 65535)),
      array(str_repeat('def', 65535)),
    );
  }  
}