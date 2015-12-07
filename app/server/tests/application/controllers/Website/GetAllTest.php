<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * WebsiteController GetAll Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetAllTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   */
  public function getAllShouldOrderWebsitesByName()
  {
    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('websites', $responseData);

    $websites = $responseData->websites;

    $alphas = range('A', 'Z');
    $indexes = range(0, 25);
    $websitesNames = array();

    foreach ($websites as $index => $website)
    {
      $actualWebsiteName = str_replace(
        $indexes,
        $alphas,
        strtoupper($website->name)
      );
      $websitesNames[] = $actualWebsiteName;
    }

    $sortedWebsiteNames = $websitesNames;
    natsort($sortedWebsiteNames);

    $this->assertEquals($sortedWebsiteNames, $websitesNames);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllRespondsWithAllPagesPrivilegesDefaultingToFalseForNonAuthenticatedUser()
  {
    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // Daten
    $this->assertObjectHasAttribute('websites', $responseObject->data);
    $this->assertInternalType('array', $responseObject->data->websites);
    $this->assertGreaterThan(1, count($responseObject->data->websites));

    $expectedNegativePagePrivileges = array(
      'edit' => false,
      'delete' => false,
      'createChildren' => false,
    );

    foreach ($responseObject->data->websites as $website)
    {
      $this->assertObjectHasAttribute('id', $website);
      $this->assertNotNull($website->id);
      $this->assertObjectHasAttribute('name', $website);
      $this->assertObjectHasAttribute('description', $website);
      $this->assertObjectHasAttribute('navigation', $website);
      $this->assertObjectHasAttribute('publish', $website);
      $this->assertObjectHasAttribute('colorscheme', $website);
      $this->assertObjectHasAttribute('privileges', $website);
      $this->assertObjectHasAttribute('version', $website);
      $this->assertObjectHasAttribute('home', $website);
      $this->assertObjectHasAttribute('screenshot', $website);

      $this->assertNotNull($website->screenshot);

      if (!is_null($website->navigation))
      {
        $this->assertInternalType('array', $website->navigation);
        $this->assertNavigationStructure($website->navigation);

        $websiteNavigationArrayObject = new \ArrayObject($website->navigation);
        $websiteNavigationArray = $websiteNavigationArrayObject->getArrayCopy();

        $pagesOfWebsite = $this->flattenPagesInNavigation($websiteNavigationArray);

        if (count($pagesOfWebsite) > 0) {
          foreach ($pagesOfWebsite as $pageOfWebsite) {
            if (count($pageOfWebsite) > 0) {
              $pagePrivilegesArrayObject = new \ArrayObject($pageOfWebsite['privileges']);
              $pagePrivilegesArray = $pagePrivilegesArrayObject->getArrayCopy();
              $this->assertSame($expectedNegativePagePrivileges, $pagePrivilegesArray);
            }
          }
        }
      }
    }
  }
  /**
   * @test
   * @group integration
   */
  public function getAllRespondsWithAllPagesPrivilegesForAllPageRightsGrantedForSpecificWebsite()
  {
    $userName = 'all.page.rights.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $websiteIdOfAllPageRightsGrantedUser =
      'SITE-ap64e82c-22ap-46cd-a651-fj42dc7dfe57-SITE';
    $this->dispatch('/website/getAll');

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websites', $responseData);
    $this->assertInternalType('array', $responseData->websites);

    foreach ($responseData->websites as $website)
    {
      $this->assertInstanceOf('stdClass', $website);
      $this->assertObjectHasAttribute('id', $website);
      $this->assertObjectHasAttribute('navigation', $website);

      if ($website->id === $websiteIdOfAllPageRightsGrantedUser)
      {
        $expectedPagesCountInNavigation = 4;
        $actualPagesCountInNavigation = count(
          $this->flattenPagesInNavigation($website->navigation)
        );

        $this->assertSame(
          $expectedPagesCountInNavigation,
          $actualPagesCountInNavigation
        );

        $pagesInNavigation = $this->flattenPagesInNavigation(
          $website->navigation
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
          $this->assertSame($expectedPrivilegesKeyCount, count(get_object_vars($page['privileges'])));
          $this->assertSame($expectedNegativePagePrivileges, get_object_vars($page['privileges']));
        }
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllRespondsWithAllPagesPrivilegesForSuperuserForSpecificWebsite()
  {
    $userName = 'all.page.privileges.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $websiteIdOfSuperuser = 'SITE-np64e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $this->dispatch('/website/getAll');

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websites', $responseData);

    foreach ($responseData->websites as $website)
    {
      $this->assertInstanceOf('stdClass', $website);
      $this->assertObjectHasAttribute('id', $website);
      $this->assertObjectHasAttribute('navigation', $website);

      if ($website->id === $websiteIdOfSuperuser)
      {
        $expectedPagesCountInNavigation = 5;
        $actualPagesCountInNavigation = count(
          $this->flattenPagesInNavigation($website->navigation)
        );

        $this->assertSame(
          $expectedPagesCountInNavigation,
          $actualPagesCountInNavigation
        );

        $pagesInNavigation = $this->flattenPagesInNavigation(
          $website->navigation
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
          $this->assertInstanceOf('stdClass', $page['privileges']);
          $this->assertSame($expectedPrivilegesKeyCount, count(get_object_vars($page['privileges'])));
          $this->assertSame($expectedNegativePagePrivileges, get_object_vars($page['privileges']));
        }
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldHaveAllUserPrivilegesSetForSpecificWebsite()
  {
    $userName = 'get.all.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('websites', $responseData);
    $this->assertInternalType('array', $responseData->websites);

    $websiteId = 'SITE-pau7f89c-22af-46cd-a651-fc42dc78fe50-SITE';

    $expectedWebsiteUserRights = array(
      'publish' => true,
      'modules' => true,
      'templates' => true,
      'colorscheme' => false,
      'readlog' => false,
      'allpagerights' => false
    );
    foreach ($responseData->websites as $website)
    {
      $this->assertInstanceOf('stdClass', $website);
      $this->assertObjectHasAttribute('id', $website);
      $this->assertObjectHasAttribute('privileges', $website);

      if ($website->id === $websiteId)
      {
        $this->assertInstanceOf('stdClass', $website->privileges);
        $this->assertSame($expectedWebsiteUserRights, get_object_vars($website->privileges));
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnEmptyResultForUserWithoutWebsite()
  {
    $userName = 'get.all.user.without.websites@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();

    $this->deactivateGroupCheck();

    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $this->assertObjectHasAttribute('websites', $responseObject->data);
    $this->assertInternalType('array', $responseObject->data->websites);
    $this->assertSame(0, count($responseObject->data->websites));
  }

  /**
   * Pruefung des Navigationsbaum
   *
   * @param array $navigation
   */
  protected function assertNavigationStructure(array $navigation)
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
        $this->assertNavigationStructure($navPoint->children);
      }
    }
  }

  /**
   * User darf nur Websites auslesen, zu denen er in einer Gruppe zugeordnet ist
   *
   * @test
   * @group integration
   */
  public function getAllShouldReturnNotAllWebsites()
  {
    $this->activateGroupCheck();
    
    $request = '/website/getAll';

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('get.all.privileges@sbcms.de', 'TEST09');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    $this->assertSame(1, count($responseObject->data->websites));
  }

  /**
   * User darf keine Websites auslesen, da er keiner Gruppe zugeordnet ist
   *
   * @test
   * @group integration
   */
  public function getAllShouldReturnNoWebsites()
  {
    $this->activateGroupCheck();

    $request = '/website/getAll';

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_1@sbcms.de', 'seitenbau');
    
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    $this->assertSame(0, count($responseObject->data->websites));
  }

  /**
   * Super-User darf alle Websites abfragen
   *
   * @test
   * @group integration
   */
  public function superuserGetAllShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $request = '/website/getAll';

    // superuser
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    
    $this->assertGreaterThan(1, count($responseObject->data->websites));
  }
}