<?php
namespace Application\Controller\Lock;

use Test\Seitenbau\LockControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * LockController GetAll Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetAllTest extends LockControllerTestCase
{

  protected function setUp()
  {
    parent::setUp();

    $this->activateGroupCheck();
  }

  protected function tearDown()
  {
    $this->deactivateGroupCheck();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnAllLocks()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $websiteId2 = 'SITE-controll-er00-lock-test-000000000002-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $templateId = 'TPL-00000000-0000-0000-0000-000000000002-TPL';
    $moduleId = 'MODUL-00000000-0000-0000-0000-000000000001-MODUL';
    $userId1 = 'USER-controll-er00-lock-test-000000000001-USER';
    $userId2 = 'USER-controll-er00-lock-test-000000000003-USER';

    // ****************************
    // Lock Page, Template und Modul

    $this->doLogin(1, false);

    // Lock page
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock template
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $templateId, 'type' => 'template');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock module
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $moduleId, 'type' => 'module');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Alle Locks ermitteln
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId);
    $request = '/lock/getAll/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseJsonObj = json_decode($response);
    $this->assertResponseBodySuccess($responseJsonObj);
    $response = new Response($response);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('locks', $responseData);
    $this->assertInternalType('array', $responseData->locks);
    $this->assertGreaterThan(0, count($responseData->locks));

    // Locks pruefen
    $pageLockExists = $templateLockExists = $moduleLockExists = false;
    foreach ($responseData->locks as $nextLock)
    {
      // Page-Lock pruefen
      if ($websiteId == $nextLock->websiteid && $pageId == $nextLock->id
          && 'page' == $nextLock->type && $userId1 == $nextLock->userid
          && $runId == $nextLock->runid)
      {
        // Page-Lock existiert
        $pageLockExists = true;
      }
      // Template-Lock pruefen
      elseif ($websiteId == $nextLock->websiteid && $templateId == $nextLock->id
          && 'template' == $nextLock->type && $userId1 == $nextLock->userid
          && $runId == $nextLock->runid)
      {
        // Template-Lock existiert
        $templateLockExists = true;
      }
      // Modul-Lock pruefen
      elseif ($websiteId == $nextLock->websiteid && $moduleId == $nextLock->id
          && 'module' == $nextLock->type && $userId1 == $nextLock->userid
          && $runId == $nextLock->runid)
      {
        // Modul-Lock existiert
        $moduleLockExists = true;
      }
    }

    // Pruefen
    $this->assertTrue($pageLockExists, "Page-Lock muss vorhanden sein!");
    $this->assertTrue($templateLockExists, "Template-Lock muss vorhanden sein!");
    $this->assertTrue($moduleLockExists, "Modul-Lock muss vorhanden sein!");


    // ****************************
    // Lock website

    $this->doLogin(3, true);

    $params = array( 'runid' => $runId, 'websiteid' => $websiteId2,
        'type' => 'website');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    $this->doLogin(1, true);

    // Alle Locks ermitteln
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId2);
    $request = '/lock/getAll/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseJsonObj = json_decode($response);
    $this->assertResponseBodySuccess($responseJsonObj);
    $response = new Response($response);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('locks', $responseData);
    $this->assertInternalType('array', $responseData->locks);
    $this->assertGreaterThan(0, count($responseData->locks));

    // Locks pruefen
    $websiteLockExists = false;
    foreach ($responseData->locks as $nextLock)
    {
      // Website-Lock pruefen
      if ($websiteId2 == $nextLock->websiteid && 'website' == $nextLock->type
          && (!isset($nextLock->id) || empty($nextLock->id))
          && $userId2 == $nextLock->userid && $runId == $nextLock->runid)
      {
        // Pacge-Lock existiert
        $websiteLockExists = true;
        break;
      }
    }

    // Pruefen
    $this->assertTrue($websiteLockExists, "Website-Lock muss vorhanden sein!");
  }

  /**
   * @test
   * @group integration
   */
  public function getAllMustHaveParamRunIdAndWebsiteId()
  {
    $this->doLogin(1, false);

    // Pflichtparameter
    $params = array( 'runid' => '', 'websiteid' => '');

    // Alle Locks ermitteln
    $request = '/lock/getAll/params/'.json_encode($params);
    $this->dispatch($request);
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
}