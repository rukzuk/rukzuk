<?php
namespace Application\Controller\Page;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * PageController Delete Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class DeleteTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function deleteSuccess()
  {
    $this->checkPageExist();

    $this->pageDelete();

    $this->checkPageDelete();

    $this->checkNavigationSubpagesDelete();
  }

  protected function checkPageExist()
  {
    // Pruefen, ob Page geloescht ist
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-0cf7e096-07b3-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('page/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
  }

  protected function pageDelete()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-0cf7e096-07b3-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );

    $paramsAsJson = json_encode($params);

    $this->dispatch('page/delete/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
  }

  protected function checkPageDelete()
  {
    // Pruefen, ob Page geloescht ist
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-0cf7e096-07b3-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('page/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
  }

  /**
   * @test
   * @group integration
   */
  public function deletePageShouldBeAllowedWhenAuthenticatedIsSuperuser()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-de45d096-07su-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-de0dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($params['id'], $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($params['websiteId'], $responseData->websiteId);

    $userName = 'delete.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/delete/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function deletePageShouldBeAllowedWhenAuthenticatedUserHasAllPagesPrivilege()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-de45d096-00ur-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-de1dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($params['id'], $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($params['websiteId'], $responseData->websiteId);

    $userName = 'delete.page.allrights@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/delete/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function deletePageShouldBeAllowedWhenAuthenticatedUserHasSubAllPagesPrivilegeOnParentPage()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-de45d096-02sa-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-de2dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($params['id'], $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($params['websiteId'], $responseData->websiteId);

    $userName = 'delete.page.suball@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/delete/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function deletePageShouldBeRejectedWhenAuthenticatedUserHasNoPagesPrivileges()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-de45d096-00np-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-de3dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($params['id'], $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($params['websiteId'], $responseData->websiteId);

    $userName = 'delete.page.no.page.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/delete/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code);
    $this->assertNull($response->getData());

    $this->dispatch('page/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($params['id'], $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($params['websiteId'], $responseData->websiteId);
  }

  protected function checkNavigationSubpagesDelete()
  {
    // Pruefen, ob Page geloescht ist
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-11667bc5-fdee-435b-8c2e-8f286dc0e545-PAGE',
      'websiteId' => 'SITE-125dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);
    try
    {
      $this->dispatch('page/getbyid/params/' . $paramsAsJson);
      $response = $this->getResponseBody();
    }
    catch (\Exception $e)
    {
      $errorCode = $e->getCode();
    }
    $this->assertSame(702, $errorCode);
  }
}