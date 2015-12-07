<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;

/**
 * WebsiteController Edit Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class DeleteTest extends ControllerTestCase
{
  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);
    MockManager::activateModuleMock(true);
    MockManager::activatePackageMock(true);

    parent::setUp();
  }

  /**
   * Website loeschen
   *
   * Der Test holt sich erst alle vorhanden Websites, der erste Eintrag
   * wird genutzt um die Pruefung von delete durchzufuehren
   *
   * @test
   * @group integration
   */
  public function success()
  {
    // ARRANGE
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteService = new \Cms\Service\Website('Website');
    $templateService = new \Cms\Service\Template('Template');
    $snippetService = new \Cms\Service\TemplateSnippet('TemplateSnippet');
    $pageService = new \Cms\Service\Page('Page');
    $packageService = new \Cms\Service\Package('Package');
    $moduleService = new \Cms\Service\Modul('Modul');

    // check if items exists
    $this->assertTrue($websiteService->existsWebsiteAlready($websiteId));
    $this->assertNotEmpty($templateService->getAll($websiteId));
    $this->assertNotEmpty($snippetService->getAll($websiteId));
    $this->assertNotEmpty($pageService->getIdsByWebsiteId($websiteId));
    $this->assertNotEmpty($packageService->getAll($websiteId));
    $this->assertNotEmpty($moduleService->getAll($websiteId));

    $params = array('id' => $websiteId,'runId' => $runId);
    $paramsAsJson = json_encode($params);

    // ACT
    $this->dispatch('website/delete/params/' . $paramsAsJson);

    // ASSERT
    $this->getValidatedSuccessResponse();

    // check if website is deleted
    $websiteExists = $websiteService->existsWebsiteAlready($websiteId);
    $this->assertFalse($websiteExists);

    // verify that all templates of the website has been deleted
    $templates = $templateService->getAll($websiteId);
    $this->assertInternalType('array', $templates);
    $this->assertCount(0, $templates);

    // verify that all templates snippets of the website has been deleted
    $snippets = $snippetService->getAll($websiteId);
    $this->assertInternalType('array', $snippets);
    $this->assertCount(0, $snippets);

    // verify that all pages of the website has been deleted
    $pageIds = $pageService->getIdsByWebsiteId($websiteId);
    $this->assertInternalType('array', $pageIds);
    $this->assertCount(0, $pageIds);

    // verify that all packages of the website has been deleted
    $allPackagesAfterDeleteWebsite = $packageService->getAll($websiteId);
    $this->assertCount(0, $allPackagesAfterDeleteWebsite,
      'Failed asserting that deleted website has no packages');

    // verify that all modules of the website has been deleted
    $allModulesAfterDeleteWebsite = $moduleService->getAll($websiteId);
    $this->assertCount(0, $allModulesAfterDeleteWebsite,
      'Failed asserting that deleted website has no modules');
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
    $this->dispatch('website/delete/params/' . $paramsAsJson);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('id', $invalidKeys);
  }

  /**
   * User darf Website loeschen
   *
   * @test
   * @group integration
   */
  public function deleteShouldReturnAccessDenied()
  {
    $params = array(
      'runid' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id'    => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE',
    );
    $paramsAsJson = json_encode($params);
    $request = 'website/delete/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('get.all.privileges@sbcms.de', 'TEST09');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $responseObject = $this->getValidatedErrorResponse();
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf Websites nicht loeschen, auch wenn er der Website zugeordnet ist
   *
   * @test
   * @group integration
   */
  public function deleteByUserWithWebsiteLinkShouldReturnAccessDenied()
  {
    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5s-SITE';
    
    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('testf0@seitenbau.com', 'TEST07');

    // pruefen, ob zuordnung zur website vorhanden ist
    $paramsAsJson = json_encode(array('id' => $websiteId));
    $request = 'website/getbyid/params/' . $paramsAsJson;
    $this->dispatch($request);
    $this->getValidatedSuccessResponse();

    $this->resetResponse();
    
    // eigentliche pruefung: website loeschen
    $params = array(
      'runid' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id'    => $websiteId,
    );
    $paramsAsJson = json_encode($params);
    $request = 'website/delete/params/' . $paramsAsJson;
    $this->dispatch($request);
    $this->deactivateGroupCheck();
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * Super-User darf alle Websites loeschen
   *
   * @test
   * @group integration
   */
  public function superuserDeleteShouldReturnSuccess()
  {
    $params = array('id' => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'runid' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID');
    $paramsAsJson = json_encode($params);
    $request = 'website/delete/params/' . $paramsAsJson;

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