<?php
namespace Application\Controller\Lock;

use Test\Seitenbau\LockControllerTestCase;

/**
 * LockController Lock Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class LockTest extends LockControllerTestCase
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
  public function lockShouldLockAsExpected()
  {
    $this->doLogin(1, false);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $userId = 'USER-controll-er00-lock-test-000000000001-USER';

    // Lock darf NICHT vorhanden sein?
    $lockExists = $this->lockExists($runId, $userId, $websiteId, $pageId, 'page');
    $this->assertFalse($lockExists);

    // Lock page
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Ist der Lock vorhanden?
    $lockExists = $this->lockExists($runId, $userId, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);
  }

  /**
   * @test
   * @group integration
   */
  public function lockShouldUpdateLockAsExpected()
  {
    $this->doLogin(1, false);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $userId = 'USER-controll-er00-lock-test-000000000001-USER';

    // Lock darf NICHT vorhanden sein?
    $lockExists = $this->lockExists($runId, $userId, $websiteId, $pageId, 'page');
    $this->assertFalse($lockExists);

    // Lock page
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock ermitteln?
    $lock = $this->lockExists($runId, $userId, $websiteId, $pageId, 'page', true);
    $this->assertInternalType('object', $lock);

    // Mindestens eine Sekunde warten, damit "lastactivity" unterschiedlich
    sleep(1);

    // Nochmal lock durchfuehren
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock nochmlas ermitteln
    $lockNew = $this->lockExists($runId, $userId, $websiteId, $pageId, 'page', true);
    $this->assertInternalType('object', $lockNew);

    // Die ermittelten Locks muessen bis auf "lastactivity" geleich sein
    $this->assertSame($lock->websiteid, $lockNew->websiteid);
    $this->assertSame($lock->userid, $lockNew->userid);
    $this->assertSame($lock->runid, $lockNew->runid);
    $this->assertSame($lock->id, $lockNew->id);
    $this->assertSame($lock->type, $lockNew->type);
    $this->assertSame($lock->starttime, $lockNew->starttime);
    $this->assertNotSame($lock->lastactivity, $lockNew->lastactivity);

  }

  /**
   * @test
   * @group integration
   */
  public function lockShouldOverrideLockAsExpected()
  {
    $runId1 = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $runId2 = 'CMSRUNID-00000000-0000-0000-0000-000000000002-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $userId1 = 'USER-controll-er00-lock-test-000000000001-USER';
    $userId2 = 'USER-controll-er00-lock-test-000000000003-USER'; // !!! Superuser !!!

    // Normaler Benutzer anmelden
    $this->doLogin(1, false);

    // Lock darf NICHT vorhanden sein?
    $lockExists = $this->lockExists($runId1, $userId1, $websiteId, $pageId, 'page');
    $this->assertFalse($lockExists);

    // Lock page
    $params = array( 'runid' => $runId1, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Ist der Lock vorhanden?
    $lockExists = $this->lockExists($runId1, $userId1, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);

    // Superuser anmelden
    $this->doLogin(3, true);

    // Lock page
    $params = array( 'runid' => $runId2, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject, false);

    // Ueberschreiben-Flag muss vorhanden sein
    $this->assertObjectHasAttribute('data', $responseObject);
    $this->assertObjectHasAttribute('overridable', $responseObject->data);
    $this->assertTrue($responseObject->data->overridable);

    // Der Lock des User1 muss immernoch vorhanden sein?
    $lockExists = $this->lockExists($runId1, $userId1, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);

    // Der Lock des Superuser darf NICHT vorhanden sein?
    $lockExists = $this->lockExists($runId2, $userId2, $websiteId, $pageId, 'page');
    $this->assertFalse($lockExists);

    // Lock ueberschreiben
    $params = array( 'runid' => $runId2, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page', 'override' => true);
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject, false);

    // Der Lock des Superuser muss vorhanden sein?
    $lockExists = $this->lockExists($runId2, $userId2, $websiteId, $pageId, 'page');
    $this->assertTrue($lockExists);
  }

  /**
   * @test
   * @group integration
   */
  public function lockShouldOverrideExpiredLockAsExpected()
  {
    $runId1 = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $runId2 = 'CMSRUNID-00000000-0000-0000-0000-000000000002-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $moduleId = 'MODUL-00000000-0000-0000-0000-000000000002-MODUL';
    $userId1 = 'USER-controll-er00-lock-test-000000000001-USER';
    $userId2 = 'USER-controll-er00-lock-test-000000000002-USER';

    // Ueberpruefen ob Alter Lock vorhanden
    $this->doLogin(2, false);

    // Alter Lock muss vorhanden sein?
    $lockExists = $this->lockExists($runId2, $userId2, $websiteId, $moduleId, 'module');
    $this->assertTrue($lockExists);

    // Normaler Benutzer anmelden
    $this->doLogin(1, true);

    // Lock page
    $params = array( 'runid' => $runId1, 'websiteid' => $websiteId,
        'id' => $moduleId, 'type' => 'module');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject, false);

    // Der Alte Lock darf nicht NICHT mehr vorhanden sein?
    $lockExists = $this->lockExists($runId2, $userId2, $websiteId, $moduleId, 'module');
    $this->assertFalse($lockExists);

    // Der Lock des User1 muss jetzt vorhanden sein?
    $lockExists = $this->lockExists($runId1, $userId1, $websiteId, $moduleId, 'module');
    $this->assertTrue($lockExists);
  }

  /**
   * @test
   * @group integration
   * @dataProvider cantLockLockedItemProvider
   */
  public function cantLockLockedItem($lock1, $lock2,
          $expectedErrorCode, $expectedOverridable)
  {
    // User 1: Lock page
    $this->doLogin($lock1['user'], false);
    $params = $lock1;
    unset($params['user']);
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // User 2: Lock page
    $this->doLogin($lock2['user'], true);
    $params = $lock2;
    unset($params['user']);
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject, false);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame($expectedErrorCode, $responseError);

    // Ueberschreiben pruefen
    $this->assertNotNull($responseObject->data);
    $this->assertSame($expectedOverridable, $responseObject->data->overridable);
  }

  /**
   * @return array
   */
  public function cantLockLockedItemProvider()
  {
    return array(
      /*
       * Page
       */
      // Gleicher Benutzer editiert, ueberschreiben moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1511,
        true
      ),
      // Anderer Benutzer editiert, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0002-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 2,
            'runid'       => 'CMSRUNID-00000000-0001-0002-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1512,
        false
      ),
      // Anderer Benutzer editiert, ueberschreiben moeglich da Superuser
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0003-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0001-0003-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1512,
        true
      ),
      // Template durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1513,
        false
      ),
      // Template durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 2,
            'runid'       => 'CMSRUNID-00000000-0001-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0001-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1514,
        false
      ),
      // Website durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0001-0005-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0001-0005-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1515,
        false
      ),
      // Website durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0001-0005-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0001-0005-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        1516,
        false
      ),

      /*
       * Template
       */
      // Gleicher Benutzer editiert, ueberschreiben moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1521,
        true
      ),
      // Anderer Benutzer editiert, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0002-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 2,
            'runid'       => 'CMSRUNID-00000000-0002-0002-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1522,
        false
      ),
      // Anderer Benutzer editiert, ueberschreiben moeglich da Superuser
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0003-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0003-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1522,
        true
      ),
      // Page durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1523,
        false
      ),
      // Page durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 2,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1524,
        false
      ),
      // Website durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1525,
        false
      ),
      // Website durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        1526,
        false
      ),

      /*
       * Modul
       */
      // Gleicher Benutzer editiert, ueberschreiben moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        1531,
        true
      ),
      // Anderer Benutzer editiert, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        array(
            'user'        => 2,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        1532,
        false
      ),
      // Anderer Benutzer editiert, ueberschreiben moeglich da Superuser
      array(
        array(
            'user'        => 1,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        1532,
        true
      ),
      // Website durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        1533,
        false
      ),
      // Website durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0004-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        1534,
        false
      ),

      /*
       * Website
       */
      // Gleicher Benutzer editiert, ueberschreiben moeglich
      //  (Nur Superuser haben Website-Rechte)
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1541,
        true
      ),
      // Anderer Benutzer editiert, ueberschreiben NICHT moeglich
      /* Bearbeiten von Websites nur als Superuser moeglich (siehe naechster Test)
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1542,
        false
      ),
      */
      // Anderer Benutzer editiert, ueberschreiben moeglich da Superuser
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1542,
        true
      ),
      // Page durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1543,
        false
      ),
      // Page durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'page',
            'id'          => 'PAGE-00000000-0000-0000-0000-000000000003-PAGE'
        ),
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1544,
        false
      ),
      // Template durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1545,
        false
      ),
      // Template durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'template',
            'id'          => 'TPL-00000000-0000-0000-0000-000000000003-TPL'
        ),
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1546,
        false
      ),
      // Modul durch Benutzer selbst gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1547,
        false
      ),
      // Modul durch anderen Benutzer gesperrt, ueberschreiben NICHT moeglich
      array(
        array(
            'user'        => 3,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0001-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'module',
            'id'          => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL'
        ),
        array(
            'user'        => 4,
            'runid'       => 'CMSRUNID-00000000-0002-0001-0002-000000000000-CMSRUNID',
            'websiteid'   => 'SITE-controll-er00-lock-test-000000000001-SITE',
            'type'        => 'website',
            'id'          => ''
        ),
        1548,
        false
      )
    );
  }

  /**
   * @test
   * @group integration
   */
  public function cantEditLockedPage()
  {
    $runIdUser1 = 'CMSRUNID-00000000-0000-0000-0001-000000000000-CMSRUNID';
    $runIdUser2 = 'CMSRUNID-00000000-0000-0000-0002-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000004-PAGE';
    $type = 'page';

    // User 1: Lock page
    $this->doLogin(1, false);
    $params = array( 'runid' => $runIdUser1, 'websiteid' => $websiteId,
        'type' => $type, 'id' => $pageId );
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // User 2: Page editieren
    $this->doLogin(2, true);
    $params = array( 'runid' => $runIdUser2, 'websiteid' => $websiteId,
        'id' => $pageId, 'name' => 'cantEditLockedPage new Page name');
    $request = '/page/edit/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1512, $responseError);
  }

  /**
   * @test
   * @group integration
   */
  public function cantEditPageIfTemplateLock()
  {
    // Login Test-User 1
    $this->doLogin(1, false);

    $runId = 'CMSRUNID-00000000-0000-0000-0005-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000002-PAGE';
    $templateId = 'TPL-00000000-0000-0000-0000-000000000002-TPL';

    // Template lock
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $templateId, 'type' => 'template');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Page editieren
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'name' => 'cantEditPageIfTemplateLock new Page name');
    $request = '/page/edit/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1513, $responseError);
  }

  /**
   * @test
   * @group integration
   */
  public function cantDeleteLockedPage()
  {
    $runIdUser1 = 'CMSRUNID-00000000-0000-0000-0001-000000000000-CMSRUNID';
    $runIdUser2 = 'CMSRUNID-00000000-0000-0000-0002-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000004-PAGE';
    $type = 'page';

    // User 1: Lock page
    $this->doLogin(1, false);
    $params = array( 'runid' => $runIdUser1, 'websiteid' => $websiteId,
        'type' => $type, 'id' => $pageId );
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // User 2: Page loeschen
    $this->doLogin(2, true);
    $params = array( 'runid' => $runIdUser2, 'websiteid' => $websiteId, 'id' => $pageId);
    $request = '/page/delete/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1512, $responseError);
  }

  /**
   * @test
   * @group integration
   */
  public function cantEditLockedTemplate()
  {
    $runIdUser1 = 'CMSRUNID-00000000-0000-0000-0001-000000000000-CMSRUNID';
    $runIdUser2 = 'CMSRUNID-00000000-0000-0000-0002-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $templateId = 'TPL-00000000-0000-0000-0000-000000000001-TPL';

    // User 1: Lock Template
    $this->doLogin(1, false);
    $params = array( 'runid' => $runIdUser1, 'websiteid' => $websiteId,
        'id' => $templateId, 'type' => 'template' );
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // User 2: Template editieren
    $this->doLogin(2, true);
    $params = array( 'runid' => $runIdUser2, 'websiteid' => $websiteId,
        'id' => $templateId, 'name' => 'cantEditLockedTemplate new Template name');
    $request = '/template/edit/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1522, $responseError);
  }

  /**
   * @test
   * @group integration
   */
  public function cantEditTemplateIfPageLock()
  {
    // Login Test-User 1
    $this->doLogin(1, false);
    
    $runId = 'CMSRUNID-00000000-0000-0000-0004-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $pageId = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $templateId = 'TPL-00000000-0000-0000-0000-000000000001-TPL';
    
    // Page lock
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $pageId, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Template editieren
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId,
        'id' => $templateId, 'name' => 'cantEditTemplateIfPageLock new template name');
    $request = '/template/edit/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1523, $responseError);
  }

  /**
   * @test
   * @group integration
   */
  public function cantDeleteLockedTemplate()
  {
    $runIdUser1 = 'CMSRUNID-00000000-0000-0000-0001-000000000000-CMSRUNID';
    $runIdUser2 = 'CMSRUNID-00000000-0000-0000-0002-000000000000-CMSRUNID';
    $websiteId = 'SITE-controll-er00-lock-test-000000000001-SITE';
    $templateId = 'TPL-00000000-0000-0000-0000-000000000001-TPL';

    // User 1: Lock Template
    $this->doLogin(1, false);
    $params = array( 'runid' => $runIdUser1, 'websiteid' => $websiteId,
        'id' => $templateId, 'type' => 'template' );
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // User 2: Template loeschen
    $this->doLogin(2, true);
    $params = array( 'runid' => $runIdUser2, 'websiteid' => $websiteId, 'id' => $templateId);
    $request = '/template/delete/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);

    // Fehler-Code pruefen
    $responseError = $responseObject->error[0]->code;
    $this->assertSame(1522, $responseError);
  }

  /**
   * Invalide Angabe von Parametern
   *
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($runId, $id, $websiteId, $type, $override, $expectedErrorParams)
  {
    // Login Test-User 1
    $this->doLogin(1, false);

    // Parameter aufbereiten
    $params = array();
    if (isset($runId))      { $params['runid']= $runId; }
    if (isset($id))         { $params['id']= $id; }
    if (isset($websiteId))  { $params['websiteid']= $websiteId; }
    if (isset($type))       { $params['type']= $type; }
    if (isset($override))   { $params['override']= $override; }

    $request = sprintf(
      '/lock/lock/params/%s',
      urlencode(\Zend_JSON::encode($params))
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
        'UNGUELTIGE_ID',
        'UNGUELTIGE_WEBSITE-ID',
        'UNGUELTIGER_TYPE',
        'UNGUELTIGER_OVERRIDE',
        array('runid', 'id', 'websiteid', 'type', 'override')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
        false,
        'page',
        false,
        array('websiteid')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
        false,
        'template',
        false,
        array('websiteid', 'id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'TPL-00000000-0000-0000-0000-000000000001-TPL',
        false,
        'page',
        false,
        array('websiteid', 'id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'MODUL-UNGLUETIGE-ID-MODUL',
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'module',
        null,
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'MODUL-00000000-0000-0000-0000-000000000001-MODUL',
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'page',
        null,
        array('id')
      ),
      array(
        null,
        null,
        null,
        null,
        null,
        array('runid', 'id', 'websiteid', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'PAGE-00000000-0000-0000-0000-000000000001-PAGE',
        'SITE-controll-er00-lock-test-000000000001-SITE',
        null,
        null,
        array('id', 'type')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        null,
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'page',
        null,
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        null,
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'template',
        null,
        array('id')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'SITE-controll-er00-lock-test-000000000001-SITE',
        'website',
        null,
        array('id')
      )
    );
  }
}