<?php
namespace Application\Controller\Website;

use Cms\Business\Website as WebsiteBusiness,
    Test\Seitenbau\ActionlogControllerTestCase as ActionlogControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
/**
 * ActionLogTest fuer Website
 *
 * @package      Test
 * @subpackage   Controller
 */
class ActionLogTest extends ActionlogControllerTestCase
{
  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);
    MockManager::activateModuleMock(true);

    parent::setUp();
  }

  /**
   * @test
   * @group integration
   */
  public function deleteWebsiteShouldBeLogged()
  {
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $params = array('id' => $websiteId, 'runId' => $runId);
    $paramsAsJson = json_encode($params);

    $userlogin = 'log.website@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('website/delete/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->assertActionLogEntry(
      $websiteId,
      $websiteId,
      $userlogin,
      WebsiteBusiness::WEBSITE_DELETE_ACTION
    );
  }

  /**
   * @test
   * @group integration
   */
  public function editWebsiteShouldBeLogged()
  {
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $params = array(
      'id' => $websiteId,
      'name' => 'new name',
      'publish' => array(
        'type' => 'internal',
        'host' => 'xxxxx',
      ),
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(255,255,255,1)'
        )
      )
    );
    $paramsAsJson = json_encode($params);

    $userlogin = 'log.website@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('website/edit/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->assertActionLogEntry(
      $websiteId,
      $websiteId,
      $userlogin,
      WebsiteBusiness::WEBSITE_EDIT_ACTION
    );
  }

  /**
   * @test
   * @group integration
   */
  public function createWebsiteShouldBeLogged()
  {
    $params = array(
      'name' => 'test create success',
      'publish' => array(
        'type' => 'internal',
        'cname' => 'my.domain.tld',
      ),
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(255,255,255,1)'
        ),
        array(
          'id' => '2',
          'name' => 'farbe2',
          'value' => 'rgba(200,255,255,1)'
        )
      )
    );
    $paramsAsJson = json_encode($params);

    $userlogin = 'log.website@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('website/create/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);

    $websiteId = $responseData->id;

    $this->assertActionLogEntry(
      $websiteId,
      $websiteId,
      $userlogin,
      WebsiteBusiness::WEBSITE_CREATE_ACTION
    );
  }

  /**
   * @test
   * @group integration
   */
  public function copyWebsiteShouldBeLogged()
  {
    $sourceWebsiteId = 'SITE-1964e89c-0002-cows-a651-fc42dc78fe50-SITE';

    $copyRequest = sprintf(
      'website/copy/params/{"id":"%s","name":"%s"}',
      $sourceWebsiteId,
      'copied_website_with_copied_albums'
    );

    $userlogin = 'log.website@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch($copyRequest);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $copiedWebsiteId = $responseData->id;

    $this->assertActionLogEntry(
      $copiedWebsiteId,
      $copiedWebsiteId,
      $userlogin,
      WebsiteBusiness::WEBSITE_COPY_ACTION,
      array('sourceId' => $sourceWebsiteId)
    );
  }
}