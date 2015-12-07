<?php
namespace Application\Controller\Heartbeat;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * HeartbeatController Poll Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class PollTest extends ControllerTestCase
{

  public $sqlFixtures = array('HeartbeatPollTest.json');

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
  public function pollShouldPollAsExpected()
  {
    $this->doLogin(1, false);

    $runId            = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';

    $websiteId1       = 'SITE-heartbea-t000-test-0000-000000000001-SITE';
    $pageLocked1      = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $pageExpired1     = 'PAGE-00000000-0000-0000-0000-000000000002-PAGE';
    $pageInvalid1     = 'PAGE-00000000-0000-0000-0000-000000000003-PAGE';
    $templateInvalid1 = 'TPL-00000000-0000-0000-0000-000000000002-TPL';
    $templateLocked1  = 'TPL-00000000-0000-0000-0000-000000000006-TPL';
    $moduleInvalid1   = 'MODUL-00000000-0000-0000-0000-000000000001-MODUL';

    $websiteId2       = 'SITE-heartbea-t000-test-0000-000000000002-SITE';
    $templateInvalid2 = 'TPL-00000000-0000-0000-0000-000000000003-TPL';
    $templateExpired2 = 'TPL-00000000-0000-0000-0000-000000000004-TPL';
    $templateInvalid3 = 'TPL-00000000-0000-0000-0000-000000000005-TPL';
    $moduleLock2      = 'MODUL-00000000-0000-0000-0000-000000000002-MODUL';
    $moduleExpired2   = 'MODUL-00000000-0000-0000-0000-000000000003-MODUL';

    $openItems = array(
        $websiteId1 => array(
            'pages'     => array($pageLocked1, $pageExpired1, $pageInvalid1),
            'templates' => array($templateInvalid1, $templateLocked1),
            'modules' => array($moduleInvalid1)
        ),
        $websiteId2 => array(
            'templates' => array($templateInvalid2, $templateExpired2, $templateInvalid3),
            'modules' => array($moduleLock2, $moduleExpired2)
        )
    );

    // Lock page
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId1,
        'id' => $pageLocked1, 'type' => 'page');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock template
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId1,
        'id' => $templateLocked1, 'type' => 'template');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Lock module
    $params = array( 'runid' => $runId, 'websiteid' => $websiteId2,
        'id' => $moduleLock2, 'type' => 'module');
    $request = '/lock/lock/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Heartbeat duchfuehren
    $params = array( 'runid' => $runId, 'openItems' => $openItems);
    $request = '/heartbeat/poll/params/'.json_encode($params);
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);

    // Data-Attribut ueberpruefen
    $this->assertInternalType('object', $responseObject->data);
    $this->assertObjectHasAttribute('expired', $responseObject->data);
    $this->assertInternalType('object', $responseObject->data->expired);
    $this->assertObjectHasAttribute('invalid', $responseObject->data);
    $this->assertInternalType('object', $responseObject->data->invalid);
    
    // Abgelaufene Locks pruefen
    foreach ($responseObject->data->expired as $nextWebsiteId => $expired)
    {
      // Werte umwandeln
      $this->assertInternalType('object', $expired);
      $expired = get_object_vars($expired);

      // Website 1
      if ($websiteId1 == $nextWebsiteId)
      {
        // Nur pages duerfen hier vorhanden sein
        if (count($expired) != 1 || !isset($expired['pages']))
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur 'pages' im response->data->expired vorhanden sein!");
        }

        // Pages pruefen
        if (count($expired['pages']) != 1 || $expired['pages'][0] != $pageExpired1)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die PageId '".$pageExpired1."' im response->data->expired->pages vorhanden sein!");
        }
      }
      // Website 2
      elseif ($websiteId2 == $nextWebsiteId)
      {
        // Nur templates und modules duerfen hier vorhanden sein
        if (count($expired) != 2 || !isset($expired['templates']) || !isset($expired['modules']))
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' duerfen nur 'templates' und 'modules' im response->data->expired vorhanden sein!");
        }

        // tempaltes pruefen
        if (count($expired['templates']) != 1 || $expired['templates'][0] != $templateExpired2)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die TemplateId '".$templateExpired2."' im response->data->expired->templates vorhanden sein!");
        }

        // modules pruefen
        if (count($expired['modules']) != 1 || $expired['modules'][0] != $moduleExpired2)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die ModuleId '".$moduleExpired2."' im response->data->expired->modules vorhanden sein!");
        }

      }
      // Es duerfen keine weiteren Websites vorhaden sein
      else
      {
        $this->fail("Es darf kein Eintrag fuer die Website '".$nextWebsiteId."' im response->data->expired vorhanden sein!");
      }
    }

    // Abgelaufene Locks pruefen
    foreach ($responseObject->data->invalid as $nextWebsiteId => $invalid)
    {
      // Werte umwandeln
      $this->assertInternalType('object', $invalid);
      $invalid = get_object_vars($invalid);

      // Website 1
      if ($websiteId1 == $nextWebsiteId)
      {
        // Nur pages, templates und modules duerfen hier vorhanden sein
        if (count($invalid) != 3 || !isset($invalid['pages'])
           || !isset($invalid['templates'])  || !isset($invalid['modules']))
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur 'pages', 'templates' und 'modules' im response->data->invalid vorhanden sein!");
        }

        // pages pruefen
        if (count($invalid['pages']) != 1 || $invalid['pages'][0] != $pageInvalid1)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die PageId '".$pageInvalid1."' im response->data->expired->invalid->pages vorhanden sein!");
        }

        // templates pruefen
        if (count($invalid['templates']) != 1 || $invalid['templates'][0] != $templateInvalid1)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die TemplateId '".$templateInvalid1."' im response->data->expired->invalid->templates vorhanden sein!");
        }

        // modules pruefen
        if (count($invalid['modules']) != 1 || $invalid['modules'][0] != $moduleInvalid1)
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die ModuleId '".$moduleInvalid1."' im response->data->expired->invalid->modules vorhanden sein!");
        }
      }
      // Website 2
      elseif ($websiteId2 == $nextWebsiteId)
      {
        // Nur templates darf hier vorhanden sein
        if (count($invalid) != 1 || !isset($invalid['templates']))
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur 'templates' im response->data->invalid vorhanden sein!");
        }

        // templates pruefen
        if (count($invalid['templates']) != 2
            || !( ($invalid['templates'][0] == $templateInvalid2 && $invalid['templates'][1] == $templateInvalid3)
                  || ($invalid['templates'][0] == $templateInvalid3 && $invalid['templates'][1] == $templateInvalid2)
                ))
        {
          $this->fail("Fuer die Website '".$nextWebsiteId."' darf nur die TemplateId '".$templateInvalid2."' und '".$templateInvalid3."' im response->data->expired->invalid->templates vorhanden sein!");
        }
      }
      // Es duerfen keine weiteren Websites vorhaden sein
      else
      {
        $this->fail("Es darf kein Eintrag fuer die Website '".$nextWebsiteId."' im response->data->invalid vorhanden sein!");
      }
    }
  }

  /**
   * Invalide Angabe von Parametern
   *
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($runId, $openItems, $expectedErrorParams)
  {
    // Login Test-User 1
    $this->doLogin(1, false);

    // Parameter aufbereiten
    $params = array();
    if (isset($runId))      { $params['runid'] = $runId; }
    if (isset($openItems))  { $params['openItems'] = $openItems; }

    $request = sprintf(
      '/heartbeat/poll/params/%s',
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
        array(
          'pages'     => array()
        ),
        array('runid', 'openItems[websiteId]', 'openItems')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000002-CMSRUNID',
        array(
          'SITE-heartbea-t000-test-0000-000000000001-SITE' => array(
            'pages'     => array('PAGE-00000000-0000-0000-0000-000000000001-PAGE'),
            'templates' => array('TPL-00000000-0000-0000-0000-000000000002-TPL'),
            'modules'   => array('MODUL-00000000-0000-0000-0000-000000000001-MODUL'),
          ),
          'SITE-heartbea-t000-test-0000-000000000002-SITE' => array(
            'pages'     => array('UNGUELTIGE_ID1', 'UNGUELTIGE_ID2'),
            'templates' => array('UNGUELTIGE_ID1'),
            'modules'   => array('UNGUELTIGE_ID1'),
          )
        ),
        array('openItems->pages[]', 'openItems->pages[]',
            'openItems->templates[]', 'openItems->modules[]')
      ),
      array(
        'CMSRUNID-00000000-0000-0000-0000-000000000002-CMSRUNID',
        array(
          'SITE-heartbea-t000-test-0000-000000000001-SITE' => array(
            'pages'     => 'UNGUELTIGE_ID1',
            'templates' => 'UNGUELTIGE_ID1',
            'modules'   => 'UNGUELTIGE_ID1',
          )
        ),
        array('openItems->pages', 'openItems->templates', 'openItems->modules')
      )
    );
  }

  /**
   * Benutzer am System anmelden
   */
  protected function doLogin($userNr, $logout)
  {
    if ($logout)
    {
      $this->assertSuccessfulLogout();
    }
    $userName = sprintf('heartbeat_test_user_%d@sbcms.de', $userNr);
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->activateGroupCheck();
  }

}