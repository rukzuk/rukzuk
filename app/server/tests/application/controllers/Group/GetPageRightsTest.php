<?php
namespace Application\Controller\Group;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * GetPageRightsTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetPageRightsTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   */
  public function checkRequiredParams()
  {
    $this->dispatch('/group/getpagerights/');
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $errors = $responseObject->error;

    $invalidParams = array();
    foreach ($errors as $error)
    {
      $this->assertSame(3, $error->code);
      $invalidParams[] = $error->param->field;
    }

    $requiredParams = array('websiteid', 'id');

    foreach ($requiredParams as $requiredParam)
    {
      $this->assertContains($requiredParam, $invalidParams);
      $requiredParamKey = array_search($requiredParam, $invalidParams);
      unset($invalidParams[$requiredParamKey]);
    }

    $this->assertSame(0, count($invalidParams), '"' . implode(', ', $invalidParams) . '" darf/duerfen keine Pflichtparamter sein');
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($id, $websiteId, $expectedErrorParams)
  {
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
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
   * @test
   * @group integration
   */
  public function success()
  {
    $id = 'GROUP-k1ckd214-56s2-vd14-98cn-ma1s452xmk9v-GROUP';
    $websiteId = 'SITE-jusm241a-l981-njaq-81c7-mjaq12kce5gw-SITE';
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // Daten Abschnitt
    $data = $responseObject->data;

    $this->assertObjectHasAttribute('navigation', $data);
    $this->assertInternalType('array', $data->navigation);

    $this->assertGreaterThan(0, count($data->navigation), 'Navigation JSON kann nicht geprueft werden ohne Navigationspunkte');

    $config = \Seitenbau\Registry::getConfig();
    $pagePrivilegeConfig = $config->group->rights->pages;
    foreach ($data->navigation as $navigationUnit)
    {
      $this->assertInstanceOf('stdClass', $navigationUnit);
      $this->assertObjectHasAttribute('id', $navigationUnit);
      $this->assertObjectHasAttribute('name', $navigationUnit);
      $this->assertObjectHasAttribute('rights', $navigationUnit);
      $this->assertInstanceOf('stdClass', $navigationUnit);

      foreach ($pagePrivilegeConfig as $pagePrivileg)
      {
        if ($pagePrivileg === 'none')
        {
          continue;
        }
        $this->assertObjectHasAttribute($pagePrivileg, $navigationUnit->rights);
        $this->assertInstanceOf('stdClass', $navigationUnit->rights->$pagePrivileg);
        $this->assertObjectHasAttribute('value', $navigationUnit->rights->$pagePrivileg);
        $this->assertInternalType('boolean', $navigationUnit->rights->$pagePrivileg->value);
        $this->assertObjectHasAttribute('inherited', $navigationUnit->rights->$pagePrivileg);
        $this->assertInternalType('boolean', $navigationUnit->rights->$pagePrivileg->inherited);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getPageRightsShouldHaveAllRightsWithValueFalseInResponseWhenAllRightsNotSetOnGroup()
  {
    $id = 'GROUP-j1ckd214-56s2-vd14-98cn-ma1s452xmkar-GROUP';
    $websiteId = 'SITE-jusm241a-l981-njaq-81c7-mjaq12kce4ar-SITE';
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $responseData = $responseObject->data;
    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('navigation', $responseData);
    $this->assertObjectHasAttribute('allRights', $responseData);
    $expectedAllRightsValue = false;
    $actualAllRightsValue = $responseData->allRights;
    $this->assertInternalType('boolean', $actualAllRightsValue);
    $this->assertSame($expectedAllRightsValue, $actualAllRightsValue);
  }

  /**
   * @test
   * @group integration
   */
  public function getPageRightsShouldHaveAllRightsWithValueTrueInResponse()
  {
    $id = 'GROUP-k1ckd214-56s2-vd14-98cn-ma1s452xmkar-GROUP';
    $websiteId = 'SITE-jusm241a-l981-njaq-81c7-mjaq12kce5ar-SITE';
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $responseData = $responseObject->data;
    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('navigation', $responseData);
    $this->assertObjectHasAttribute('allRights', $responseData);
    $expectedAllRightsValue = true;
    $actualAllRightsValue = $responseData->allRights;
    $this->assertInternalType('boolean', $actualAllRightsValue);
    $this->assertSame($expectedAllRightsValue, $actualAllRightsValue);
  }
  
  /**
   * Standard-User darf keine Gruppe-Rechte abfragen
   *
   * @test
   * @group integration
   */
  public function getPageRightsShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $id = 'GROUP-k1ckd214-56s2-vd14-98cn-ma1s452xmkar-GROUP';
    $websiteId = 'SITE-jusm241a-l981-njaq-81c7-mjaq12kce5ar-SITE';
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );
    
    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_1@sbcms.de', 'seitenbau');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * Super-User darf alle Gruppen-Rechte abfragen
   *
   * @test
   * @group integration
   */
  public function superuserGetPageRightsShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $id = 'GROUP-k1ckd214-56s2-vd14-98cn-ma1s452xmkar-GROUP';
    $websiteId = 'SITE-jusm241a-l981-njaq-81c7-mjaq12kce5ar-SITE';
    $request = sprintf(
      '/group/getpagerights/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );

    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    return array(
      array(null, null, array('id', 'websiteid')),
      array('123456', '123456', array('id', 'websiteid')),
      array('123456', 'SITE-4demch31-10ac-4e1e-951f-307e4b8765db-SITE', array('id')),
      array('GROUP-kixm2d21-a3c4-4fdh-ard4-72ebb0878rf7-GROUP', 'abcdefg', array('websiteid')),
    );
  }
}
