<?php
namespace Application\Controller\Lock;

use Test\Seitenbau\LockControllerTestCase;

/**
 * LockController Lock Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class UnlockTest extends LockControllerTestCase
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
  public function unlockShouldUnlockAsExpected()
  {
    $this->doLogin(3, false);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId1 = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $websiteId2 = 'SITE-controll-er00-lock-test-000000000002-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000002-PAGE';
    $userId = 'USER-controll-er00-lock-test-000000000003-USER';

    // Lock page
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId1,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock website (muss als Superuser ausgefuehrt werden)
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId2,
        'type' => 'website');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Sind die Locks vorhanden?
    $pageLockExists = $this->lockExists($runId, $userId, $websiteId1, $pageId, 'page');
    $this->assertTrue($pageLockExists);

    // Sind die Locks vorhanden?
    $websiteLockExists = $this->lockExists($runId, $userId, $websiteId2, null, 'website');
    $this->assertTrue($websiteLockExists);

    // Unlock Page
    $params = array( 'runid' => $runId,
                     'items' => array(
                         array (
                          'websiteId' => $websiteId2,
                          'type' => 'website'),
                         array (
                          'websiteId' => $websiteId1,
                          'id' => $pageId,
                          'type' => 'page')
                    ));
    $request = '/lock/unlock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Page-Lock darf nicht merh vorhanden sein
    $pageLockExists = $this->lockExists($runId, $userId, $websiteId1, $pageId, 'page');
    $this->assertFalse($pageLockExists);

    // Website-Lock darf nicht merh vorhanden sein
    $websiteLockExists = $this->lockExists($runId, $userId, $websiteId2, null, 'website');
    $this->assertFalse($websiteLockExists);

  }

  /**
   * @test
   * @group integration
   */
  public function cantUnlockLockedPageWithDifferentRunId()
  {
    $this->doLogin(1, false);

    $runId1 = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $runId2 = 'CMSRUNID-00000000-0000-0000-0000-000000000002-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000002-PAGE';
    $userId = 'USER-controll-er00-lock-test-000000000001-USER';

    // Lock page
    $params = array( 'runid' => $runId1, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Locks vorhanden?
    $lockExists = $this->lockExists($runId1, $userId, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);

    // Unlock Page mit anderer runId
    $params = array( 'runid' => $runId2,
                     'items' => array(
                         array (
                          'websiteId' => $websiteId,
                          'id' => $pageId,
                          'type' => 'page')
                    ));
    $request = '/lock/unlock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Locks muss immernoch vorhanden sein?
    $lockExists = $this->lockExists($runId1, $userId, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);
  }

  /**
   * Invalide Angabe von Parametern
   *
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($runId, $items, $expectedErrorParams)
  {
    // Login Test-User 1
    $this->doLogin(1, false);

    // Parameter aufbereiten
    $params = array();
    if (isset($runId))      { $params['runid']= $runId; }
    if (isset($items))      { $params['items']= $items; }

    $request = sprintf(
      '/lock/unlock/params/%s',
      urlencode(\Seitenbau\Json::encode($params))
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

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
      $this->assertInstanceOf('stdClass', $error->param);
      $this->assertObjectHasAttribute('field', $error->param);
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
      array(
        'UNGUELTIGE_RUNID',
        array(
          'id'        => 'UNGUELTIGE_ID',
          'websiteid' => 'UNGUELTIGE_WEBSITE-ID',
          'type'      => 'UNGUELTIGER_TYPE'
        ),
        array('runid', 'items')
      ),
      array(
        'UNGUELTIGE_RUNID',
        array(
          array(
            'id'        => 'UNGUELTIGE_ID',
            'websiteId' => 'UNGUELTIGE_WEBSITE-ID',
            'type'      => 'UNGUELTIGER_TYPE'
          )
        ),
        array('runid', 'id', 'websiteid', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'page'
          )
        ),
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
            'type'      => 'page'
          )
        ),
        array('websiteid')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
          )
        ),
        array('id', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
            'websiteId' => false,
            'type'      => 'page'
          )
        ),
        array('websiteid')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
            'websiteId' => false,
            'type'      => 'template'
          )
        ),
        array('websiteid', 'id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'TPL-00000000-0000-0000-0000-000000000001-TPL',
            'websiteId' => false,
            'type'      => 'page'
          )
        ),
        array('websiteid', 'id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'MODUL-UNGLUETIGE-ID-MODUL',
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'module'
          )
        ),
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL',
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'page'
          )
        ),
        array('id')
      ),
      array(
        null,
        array(
          array(
            'id'        => null,
            'websiteId' => null,
            'type'      => null
          )
        ),
        array('runid', 'id', 'websiteid', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => null
          )
        ),
        array('id', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => null,
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'page'
          )
        ),
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => null,
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'template'
          )
        ),
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        array(
          array(
            'id'        => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'websiteId' => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'      => 'website'
          )
        ),
        array('id')
      )
    );
  }
}