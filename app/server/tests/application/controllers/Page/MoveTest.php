<?php
namespace Application\Controller\Page;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * PageController Move Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class MoveTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function movePageShouldBeAllowedWhenAuthenticatedUserIsSuperuser()
  {
    $params = array(
      'id' => 'PAGE-m01rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'parentId' => 'PAGE-m00rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-mo64e89c-00su-46cd-a651-fc42dc78fe50-SITE'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'move.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/move/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function movePageShouldBeAllowedWhenAuthenticatedUserHasAllPagesPrivileges()
  {
    $params = array(
      'id' => 'PAGE-m01rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'parentId' => 'PAGE-m00rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-mo64e89c-00su-46cd-a651-fc42dc78fe50-SITE'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'move.page.allrights@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/move/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function movePageShouldBeAllowedWhenAuthenticatedUserHasSubAllPagesPrivilegeOnParentPage()
  {
    $params = array(
      'id' => 'PAGE-m01rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'parentId' => 'PAGE-m00rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-mo64e89c-00su-46cd-a651-fc42dc78fe50-SITE'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'move.page.suball@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/move/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function movePageShouldBeRejectedWhenAuthenticatedUserHasNoPagesPrivileges()
  {
    $params = array(
      'id' => 'PAGE-m01rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'parentId' => 'PAGE-m00rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-mo64e89c-00su-46cd-a651-fc42dc78fe50-SITE'
    );

    $paramsAsJson = json_encode($params);

    $userName = 'move.page.no.page.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('page/move/params/' . $paramsAsJson);

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
  public function movePageToParentIdSuccess()
  {
    $params = array(
      'id' => 'PAGE-16439849-9f63-4eab-aa0a-9b27b9d8643d-PAGE',
      'parentId' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE'
    );

    $paramsAsJson = json_encode($params);

    $this->dispatch('page/move/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $this->assertObjectHasAttribute('navigation', $responseObject->data);
    $this->assertInternalType('array', $responseObject->data->navigation);
    $this->assertObjectHasAttribute('id', $responseObject->data->navigation[0]);
    $this->assertObjectHasAttribute('name', $responseObject->data->navigation[0]);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($pageId, $websiteId, $parentId, $insertBeforeId,
    $expectedErrorParams
  ) {
    $request = sprintf(
      '/page/move/params/{"id":"%s","websiteid":"%s","parentid":"%s","insertBeforeId":"%s"}',
      $pageId, $websiteId, $parentId, $insertBeforeId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertFalse($responseObject->success);

    $errors = $responseObject->error;
    $expectedErrorCode = 3;
    $actualErrorCode  = $errors[0]->code;

    $assertionMessage = sprintf(
      "Expected error code '%d' doesn't match actual code '%d'",
      $expectedErrorCode, $actualErrorCode
    );
    $this->assertSame($expectedErrorCode, $actualErrorCode, $assertionMessage);
    
    foreach ($errors as $error)
    {
      $this->assertObjectHasAttribute('param', $error);
      $this->assertObjectHasAttribute('field', $error->param);;
      $this->assertInternalType('string', $error->param->field);
      $this->assertContains($error->param->field, $expectedErrorParams, 'Parameter "' . $error->param->field . '" wird invalid zurückgegeben, obwohl er korrekt ist');
      $key = array_search($error->param->field, $expectedErrorParams);
      unset($expectedErrorParams[$key]);
    }
    $this->assertSame(0, count($expectedErrorParams), 'Parameter "' . implode(', ', $expectedErrorParams) . '" wurde nicht als Fehler zurückgegeben');
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    return array(
      array(null, null, null, null, array('id', 'websiteid', 'parentId')),
      array('123456', '123456', '123456', 'a', array('id', 'websiteid', 'parentId', 'insertBeforeId')),
    );
  }
}