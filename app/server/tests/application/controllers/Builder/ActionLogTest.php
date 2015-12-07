<?php
namespace Application\Controller\Builder;

use Cms\Business\Builder as BuilderBusiness,
    Test\Seitenbau\ActionlogControllerTestCase as ActionlogControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Seitenbau\Registry as Registry;
/**
 * ActionLogTest fuer Builder
 *
 * @package      Test
 * @subpackage   Controller
 */
class ActionLogTest extends ActionlogControllerTestCase
{

  protected function setUp()
  {
    $this->markTestSkipped(
      'TODO: Broken with old Creator, try with new creator impl'
    );
  }

  protected function tearDown()
  {
    $this->removeWebsiteBuilds();
    parent::tearDown();
  }
  /**
   * @test
   * @group integration
   */
  public function buildWebsiteShouldBeLogged()
  {
    $websiteId = 'SITE-bw00fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    
    $params = array(
      'websiteId' => $websiteId,
      'comment' => 'test_website_build_1'
    );

    $paramsAsJson = json_encode($params);

    $userlogin = 'log.build@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('builder/buildwebsite/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertActionLogEntry(
      $params['websiteId'],
      $responseData->id,
      $userlogin,
      BuilderBusiness::BUILDER_BUILD_ACTION
    );
    
    $this->removeCreatedWebsite($websiteId);
  }

  protected function removeCreatedWebsite($websiteId, $creatorType=null)
  {
    $config = Registry::getConfig();
    
    if (empty($creatorType)) {
      $creatorType = $config->creator->defaultCreator;
    }
    
    $creatorDirectory = $config->creator->directory;
    $websiteCreatorDir = $creatorDirectory
      . DIRECTORY_SEPARATOR . $websiteId . '-' . $creatorType;
    
    DirectoryHelper::removeRecursiv($websiteCreatorDir, $creatorDirectory);
  }
}