<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * WebsiteController GetById Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetByIdTest extends ControllerTestCase
{
  /**
   * Der Test holt sich erst alle vorhanden Websites, der erste Eintrag
   * wird genutzt um die Pruefung von getById durchzufuehren
   *
   * @test
   * @group integration
   */
  public function success()
  {
    $params = array('id' => 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // Daten Abschnitt
    $this->assertObjectHasAttribute('id', $responseObject->data);
    $this->assertSame($params['id'], $responseObject->data->id);
    $this->assertObjectHasAttribute('name', $responseObject->data);
    $this->assertObjectHasAttribute('description', $responseObject->data);
    $this->assertObjectHasAttribute('navigation', $responseObject->data);
    $this->assertObjectHasAttribute('screenshot', $responseObject->data);
    $this->assertObjectHasAttribute('version', $responseObject->data);
    $this->assertObjectHasAttribute('colorscheme', $responseObject->data);
    $this->assertObjectHasAttribute('resolutions', $responseObject->data);
    $this->assertObjectHasAttribute('home', $responseObject->data);

    $this->assertNotNull($responseObject->data->colorscheme);
    $this->assertNotNull($responseObject->data->resolutions);
    $this->assertNotNull($responseObject->data->screenshot);
    $this->assertObjectHasAttribute('publishingEnabled', $responseObject->data);
    $this->assertTrue($responseObject->data->publishingEnabled);
    $this->assertObjectHasAttribute('publish', $responseObject->data);
    $this->assertObjectHasAttribute('password', $responseObject->data->publish);
    $this->assertSame('*****', $responseObject->data->publish->password);


    if (!is_null($responseObject->data->navigation))
    {
      $this->assertInternalType('array', $responseObject->data->navigation);
      $this->checkNavigation($responseObject->data->navigation);
    }
  }
  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveOnlyPositivePagePrivilegesForSuperuser()
  {
    $userName = 'all.page.privileges.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-np64e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 5;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedNegativePagePrivileges = array(
      'edit' => true,
      'delete' => true,
      'createChildren' => true,
    );

    foreach ($pagesInNavigation as $page)
    {
      $this->assertArrayHasKey('privileges', $page);

      if ($page['privileges'] !== null)
      {
        $this->assertInstanceOf('stdClass', $page['privileges']);
        $privilegesAsArray = get_object_vars($page['privileges']);

        $this->assertSame($expectedPrivilegesKeyCount, count($privilegesAsArray));
        $this->assertSame($expectedNegativePagePrivileges, $privilegesAsArray);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveOnlyNegativePagePrivilegesForNonPrivilegedUser()
  {
    $userName = 'no.page.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-np64e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 5;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedNegativePagePrivileges = array(
      'edit' => false,
      'delete' => false,
      'createChildren' => false,
    );

    foreach ($pagesInNavigation as $page)
    {
      $this->assertArrayHasKey('privileges', $page);
      $this->assertSame($expectedPrivilegesKeyCount, count(get_object_vars($page['privileges'])));
      $this->assertSame($expectedNegativePagePrivileges, get_object_vars($page['privileges']));
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHavePositivePagePrivilegesForAllPagesRightsPrivilegedUser()
  {
    $userName = 'all.page.rights.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-ap64e82c-22ap-46cd-a651-fj42dc7dfe57-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 4;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedNegativePagePrivileges = array(
      'edit' => true,
      'delete' => true,
      'createChildren' => true,
    );

    foreach ($pagesInNavigation as $page) {
      $this->assertArrayHasKey('privileges', $page);
      $privilegesAsArray = get_object_vars($page['privileges']);
      $this->assertSame($expectedPrivilegesKeyCount, count($privilegesAsArray));
      $this->assertSame($expectedNegativePagePrivileges, $privilegesAsArray);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHavePositiveEditPagePrivilegesForPrivilegedUser()
  {
    $userName = 'edit.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-ep64e82c-22ap-46cd-a651-fj42dc7dfe57-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 4;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedPositiveEditPagePrivileges = array(
      'edit' => true,
      'delete' => false,
      'createChildren' => false,
    );

    foreach ($pagesInNavigation as $page)
    {
      $this->assertArrayHasKey('privileges', $page);
      $privilegesAsArray = get_object_vars($page['privileges']);
      $this->assertSame($expectedPrivilegesKeyCount, count($privilegesAsArray));
      $this->assertSame($expectedPositiveEditPagePrivileges, $privilegesAsArray);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveAllPositivePrivilegesForSubAllChildPagesForPrivilegedUser()
  {
    $userName = 'suball.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-dp64e82c-22ap-46cd-a651-fj42dc7dfe57-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 5;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedPositiveChildPagePrivileges = array(
      'edit' => true,
      'delete' => true,
      'createChildren' => true,
    );
    $expectedChildPageIdsWithPositiveChildPagePrivileges = array(
      'PAGE-dpr02eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'PAGE-dpr03eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
    );

    $expectedNegativeParentPagePrivileges = array(
      'edit' => false,
      'delete' => false,
      'createChildren' => false,
    );
    $expectedParentOrPlainPageIdsWithNegativePagePrivileges = array(
      'PAGE-dpr00eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'PAGE-dpr04eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
    );

    $expectedPositiveParentPageCreateChildrenPrivileges = array(
      'edit' => false,
      'delete' => false,
      'createChildren' => true,
    );
    $expectedParentPageIdsWithPositiveCreateChildrenPrivileges = array(
      'PAGE-dpr01eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
    );

    foreach ($pagesInNavigation as $page)
    {
      $this->assertArrayHasKey('privileges', $page);
      $privilegesAsArray = get_object_vars($page['privileges']);
      $this->assertSame($expectedPrivilegesKeyCount, count($privilegesAsArray));
      if (in_array($page['id'], $expectedChildPageIdsWithPositiveChildPagePrivileges))
      {
        $this->assertSame($expectedPositiveChildPagePrivileges, $privilegesAsArray);
      }
      if (in_array($page['id'], $expectedParentOrPlainPageIdsWithNegativePagePrivileges))
      {
        $this->assertSame($expectedNegativeParentPagePrivileges, $privilegesAsArray);
      }
      if (in_array($page['id'], $expectedParentPageIdsWithPositiveCreateChildrenPrivileges))
      {
        $this->assertSame($expectedPositiveParentPageCreateChildrenPrivileges, $privilegesAsArray);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHavePositiveEditPrivilegesForSubEditChildPagesForPrivilegedUser()
  {
    $userName = 'subedit.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $params = array('id' => 'SITE-se64e82c-22ap-46cd-a651-fj42dc7dfe57-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('navigation', $responseData);

    $expectedPagesCountInNavigation = 5;
    $actualPagesCountInNavigation = count(
      $this->flattenPagesInNavigation($responseData->navigation)
    );

    $this->assertSame(
      $expectedPagesCountInNavigation,
      $actualPagesCountInNavigation
    );

    $pagesInNavigation = $this->flattenPagesInNavigation(
      $responseData->navigation
    );

    $expectedPrivilegesKeyCount = 3;
    $expectedPositiveChildPageEditPrivileges = array(
      'edit' => true,
      'delete' => false,
      'createChildren' => false,
    );
    $expectedChildPageIdsWithPositiveChildPageEditPrivileges = array(
      'PAGE-ser02eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'PAGE-ser03eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'PAGE-ser04eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
    );

    $expectedNegativeParentPagePrivileges = array(
      'edit' => false,
      'delete' => false,
      'createChildren' => false,
    );
    $expectedParentOrPlainPageIdsWithNegativePagePrivileges = array(
      'PAGE-ser00eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'PAGE-ser01eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
    );

    foreach ($pagesInNavigation as $page)
    {
      $this->assertArrayHasKey('privileges', $page);
      $privilegesAsArray = get_object_vars($page['privileges']);
      $this->assertSame($expectedPrivilegesKeyCount, count($privilegesAsArray));
      if (in_array($page['id'], $expectedChildPageIdsWithPositiveChildPageEditPrivileges))
      {
        $this->assertSame($expectedPositiveChildPageEditPrivileges, $privilegesAsArray);
      }
      if (in_array($page['id'], $expectedParentOrPlainPageIdsWithNegativePagePrivileges))
      {
        $this->assertSame($expectedNegativeParentPagePrivileges, $privilegesAsArray);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveDefaultNegativeUserPrivileges()
  {
    $params = array('id' => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('privileges', $responseData);

    $expectedDefaultNegativeUserPrivileges = array(
      'publish' => false,
      'modules' => false,
      'templates' => false,
      'colorscheme' => false,
      'readlog' => false,
      'allpagerights' => false
    );
    $actualUserPrivileges = get_object_vars($responseData->privileges);
    $this->assertSame(
      $expectedDefaultNegativeUserPrivileges,
      $actualUserPrivileges
    );
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveAnUserPrivilegeWithOnlyPublishEnabled()
  {
    $userName = 'login.privileges1@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $params = array('id' => 'SITE-ppu4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('privileges', $responseData);

    $expectedUserPrivilegesWithPublishEnabled = array(
      'publish' => true,
      'modules' => false,
      'templates' => false,
      'colorscheme' => false,
      'readlog' => false,
      'allpagerights' => false
    );
    $actualUserPrivileges = get_object_vars($responseData->privileges);
    $this->assertSame(
      $expectedUserPrivilegesWithPublishEnabled,
      $actualUserPrivileges
    );
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveAnUserPrivilegeWithOnlyEditModulesEnabled()
  {
    $userName = 'login.privileges2@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $params = array('id' => 'SITE-ppu4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('privileges', $responseData);

    $expectedUserPrivilegesWithEditModuleEnabled = array(
      'publish' => false,
      'modules' => true,
      'templates' => false,
      'colorscheme' => false,
      'readlog' => false,
      'allpagerights' => false
    );
    $actualUserPrivileges = get_object_vars($responseData->privileges);
    $this->assertSame(
      $expectedUserPrivilegesWithEditModuleEnabled,
      $actualUserPrivileges
    );
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveAnUserPrivilegeWithOnlyEditTemplatesEnabled()
  {
    $userName = 'login.privileges3@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $params = array('id' => 'SITE-ppu4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('privileges', $responseData);

    $expectedUserPrivilegesWithEditTemplatesEnabled = array(
      'publish' => false,
      'modules' => false,
      'templates' => true,
      'colorscheme' => false,
      'readlog' => false,
      'allpagerights' => false
    );
    $actualUserPrivileges = get_object_vars($responseData->privileges);
    $this->assertSame(
      $expectedUserPrivilegesWithEditTemplatesEnabled,
      $actualUserPrivileges
    );
  }
  /**
   * @test
   * @group integration
   */
  public function getByIdShouldHaveAllUserPrivilegeEnabled()
  {
    $userName = 'login.privileges4@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $params = array('id' => 'SITE-ppu4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);

    $response = $this->getValidatedSuccessResponse();

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('privileges', $responseData);

    $expectedUserPrivileges = array(
      'publish' => true,
      'modules' => true,
      'templates' => true,
      'colorscheme' => true,
      'readlog' => true,
      'allpagerights' => false
    );
    $actualUserPrivileges = get_object_vars($responseData->privileges);
    $this->assertSame(
      $expectedUserPrivileges,
      $actualUserPrivileges
    );
  }

  /**
   * Invalide Angabe von Parametern
   *
   * @test
   * @group integration
   */
  public function invalidParams()
  {
    $params = array('id' => 'UNGUELTIGE_ID');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    foreach ($responseObject->error as $error)
    {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }

  /**
   * Website existiert nicht
   *
   * @test
   * @group integration
   */
  public function websiteNotFound()
  {
    $params = array('id' => 'SITE-11111111-1111-1111-1111-1111111111111-SITE');
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/getbyid/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Error Daten
    $errors = $responseObject->error;
    $this->assertNotNull($errors[0]->code);
    $this->assertNotNull($errors[0]->logid);
  }

  /**
   * Pruefung des Navigationsbaum
   *
   * @param array $navigation
   */
  protected function checkNavigation(array $navigation)
  {
    foreach ($navigation as $navPoint)
    {
      if (is_array($navPoint)) continue;
      $this->assertObjectHasAttribute('id', $navPoint);
      $this->assertObjectHasAttribute('name', $navPoint);
      $this->assertObjectHasAttribute('privileges', $navPoint);
      $this->assertObjectHasAttribute('edit', $navPoint->privileges);
      $this->assertObjectHasAttribute('delete', $navPoint->privileges);
      $this->assertObjectHasAttribute('createChildren', $navPoint->privileges);
      if (isset($navPoint->children) && !is_null($navPoint->children))
      {
        $this->assertInternalType('array', $navPoint->children);
        $this->checkNavigation($navPoint->children);
      }
    }
  }

  /**
   * User darf Website ohne Zugehoerigkeit ueber eine Gruppe nicht abfragen
   *
   * @test
   * @group integration
   */
  public function getByIdShouldReturnAccessDenied()
  {
    $params = array('id' => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $request = 'website/getbyid/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('get.all.privileges@sbcms.de', 'TEST09');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf Websites auslesen, da er der Gruppe zugeordnet ist
   *
   * @test
   * @group integration
   */
  public function getByIdShouldReturnWebsite()
  {
    $params = array('id' => 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5s-SITE');
    $paramsAsJson = json_encode($params);
    $request = 'website/getbyid/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('testf0@seitenbau.com', 'TEST07');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * Super-User darf alle Websites abfragen
   *
   * @test
   * @group integration
   */
  public function superuserGetByIdShouldReturnSuccess()
  {
    $params = array('id' => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE');
    $paramsAsJson = json_encode($params);
    $request = 'website/getbyid/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }
}