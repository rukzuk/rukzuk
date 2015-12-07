<?php
namespace Application\Controller\Page;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * PageController Copy Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class CopyTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function copyPageShouldBeAllowedWhenAuthenticatedUserIsSuperuser()
  {
    $params = array(
      'id' => 'PAGE-co0rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-co64e89c-00af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'copy page test'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'copy.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/copy/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertObjectHasAttribute('navigation', $responseData);

    $params = array(
      'id' => $responseData->id,
      'websiteId' => $params['websiteId']
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
  }  

  /**
   * @test
   * @group integration
   */
  public function copyPageShouldBeAllowedWhenAuthenticatedUserHasAllPagesPrivileges()
  {
    $params = array(
      'id' => 'PAGE-co0rsfb8-0363-47e9-2alr-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-co64e89c-01af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'copy page test'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'copy.page.allrights@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/copy/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertObjectHasAttribute('navigation', $responseData);

    $params = array(
      'id' => $responseData->id,
      'websiteId' => $params['websiteId']
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function copyPageShouldBeAllowedWhenAuthenticatedUserHasSubAllPagesPrivilegeOnParentPage()
  {
    $params = array(
      'id' => 'PAGE-co0rsfb8-0363-47e9-2sal-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-co64e89c-03af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'copy page test'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'copy.page.suball@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/copy/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertObjectHasAttribute('navigation', $responseData);

    $params = array(
      'id' => $responseData->id,
      'websiteId' => $params['websiteId']
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function copyPageShouldBeRejectedWhenAuthenticatedUserHasNoPagesPrivileges()
  {
    $params = array(
      'id' => 'PAGE-co0rsfb8-0363-47e9-0nor-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-co64e89c-02af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'copy page test'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'copy.page.no.page.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/copy/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code);
    $this->assertNull($response->getData());
  }

  /**
   * @test
   * @group integration
   */
  public function copySuccess()
  {
    // ARRANGE
    $pageBusiness = new \Cms\Business\Page('Page');
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $paramsForCopy = array(
      'id' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'websiteId' => $websiteId,
      'name' => 'copy page test'
    );

    // ACT
    $this->dispatch('page/copy/params/'.urlencode(json_encode($paramsForCopy)));
    $response = $this->getValidatedSuccessResponse();

    // ASSERT
    $responseData = $response->getData();
    $this->assertInternalType('object', $responseData);
    $this->assertNotNull($responseData->id);
    $this->assertNotSame($paramsForCopy['id'], $responseData->id);

    // check if page was copied
    $pageIdsAfterCopy = $pageBusiness->getIdsByWebsiteId($websiteId);
    $pageFound = false;
    foreach ($pageIdsAfterCopy as $pageId) {
      $page = $pageBusiness->getById($pageId, $websiteId);
      if ($page->getName() == $paramsForCopy['name']) {
        $pageFound = true;
        break;
      }
    }
    $this->assertTrue($pageFound);
  }
}