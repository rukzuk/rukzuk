<?php
namespace Application\Controller\User;

use Seitenbau\Registry as Registry,
    Orm\Data\User as DataUser,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * InfoTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class InfoTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function infoShouldRespondWithErrorWhenUserIsNotAuthenticated()
  {
    $this->activateGroupCheck();

    $infoRequest = '/user/info';
    $this->dispatch($infoRequest);
    $response = $this->getResponseBody();

    $this->deactivateGroupCheck();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();

    $expectedErrorCode = 5;
    $actualErrorCode = $responseError[0]->code;

    $assertionMessage = sprintf(
      "Actual error code '%s' doesn't match expected error code '%s'",
      $actualErrorCode,
      $expectedErrorCode
    );
    $this->assertSame($expectedErrorCode, $actualErrorCode);
  }

  /**
   * @test
   * @group integration
   */
  public function infoShouldRespondWithExpectedUserJsonWithoutGroupAssociation()
  {
    $userName = 'login.test0@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $infoRequest = '/user/info';
    $this->dispatch($infoRequest);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('userInfo', $responseData);

    $expectedUserInfo = array(
      'id' => 'USER-lo01eaa7-7fc5-464a-bd47-16b3b8af36c0-USER',
      'lastname' => 'login_lastname_0',
      'firstname' => 'login_firstname_0',
      'gender' => 'm',
      'email' => 'login.test0@sbcms.de',
      'language' => null,
      'superuser' => true,
      'owner' => false,
      'readonly' => false,
      'dashboardUrl' => null,
      'upgradeUrl' => null,
      'groups' => array(),
      'privilege' => array()
    );
    $actualUserInfo = get_object_vars($responseData->userInfo);

    $this->assertSame($expectedUserInfo, $actualUserInfo);
  }

  /**
   * @test
   * @group integration
   */
  public function infoShouldRespondWithExpectedUserJsonWithDataInAllFields()
  {
    $userName = 'login.test1@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->activateGroupCheck();

    $infoRequest = '/user/info';
    $this->dispatch($infoRequest);

    $this->deactivateGroupCheck();

    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('userInfo', $responseData);

    $expectedGroupIds = array(
      'SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE' => array(
        'GROUP-ui0kd214-56s2-vd14-98cn-ma1s452xmkar-GROUP',
        'GROUP-ui1kd214-56s2-vd14-98cn-ma1s452xmkar-GROUP',
        'GROUP-ui2kd214-56s2-vd14-98cn-ma1s452xmkar-GROUP',
      )
    );

    $expectedUserInfo = array(
      'id' => 'USER-lo02eaa7-7fc5-464a-bd47-16b3b8af36c0-USER',
      'lastname' => 'login_lastname_1',
      'firstname' => 'login_firstname_1',
      'gender' => 'f',
      'email' => 'login.test1@sbcms.de',
      'language' => null,
      'superuser' => false,
      'owner' => false,
      'readonly' => false,
      'dashboardUrl' => null,
      'upgradeUrl' => null,
      'groups' => $expectedGroupIds,
      'privilege' => array(
        'SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE' => array(
          'publish' => false,
          'modules' => false,
          'templates' => false,
          'colorscheme' => false,
          'readlog' => false,
          'allpagerights' => false
        )
      )
    );

    foreach ($expectedUserInfo['groups'] as $siteId => $groups)
    {
      sort($groups);
      $expectedUserInfo['groups'][$siteId] = $groups;
    }
    foreach ($expectedUserInfo['privilege'] as $siteId => $privileges)
    {
      arsort($privileges);
      $expectedUserInfo['privilege'][$siteId] = $privileges;
    }

    $actualUserInfo = get_object_vars($responseData->userInfo);
    $actualUserInforGroups = get_object_vars($actualUserInfo['groups']);
    $actualUserInfo['groups'] = array();
    foreach ($actualUserInforGroups as $siteId => $groups)
    {
      sort($groups);
      $actualUserInfo['groups'][$siteId] = $groups;
    }
    $actualUserInforGroups = get_object_vars($actualUserInfo['privilege']);
    $actualUserInfo['privilege'] = array();
    foreach ($actualUserInforGroups as $siteId => $privileges)
    {
      $privilegesAsArray = get_object_vars($privileges);
      arsort($privilegesAsArray);

      $actualUserInfo['privilege'][$siteId] = $privilegesAsArray;
    }

    $this->assertSame($expectedUserInfo, $actualUserInfo);
  }

  /**
   * @test
   * @group integration
   * @ticket  SBCMS-556
   * @dataProvider differentUserPrivilegeAttributesProvider
   */
  public function testUserPrivilegeAttribut($username, $userpassword, $expectedPrivilegesAsString)
  {
    $this->assertSuccessfulLogin($username, $userpassword);

    $this->activateGroupCheck();

    $infoRequest = '/user/info';
    $this->dispatch($infoRequest);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $this->assertObjectHasAttribute('privilege', $responseObject->data->userInfo);
    $this->assertInstanceOf('stdClass', $responseObject->data->userInfo->privilege);

    $responsePrivilegesAsString = json_encode($responseObject->data->userInfo->privilege);
    $this->assertSame($expectedPrivilegesAsString, $responsePrivilegesAsString);
  }

  /**
   * Liefert verschiedene Testfaelle fuer die Rechtepruefung zurueck
   *
   * username | userpassword | erwartete Rechte als JSON-String
   *
   * @return array
   */
  public function differentUserPrivilegeAttributesProvider()
  {
    return array(
      array('login.test2@sbcms.de', 'TEST09', '{"SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE":{"publish":true,"modules":true,"templates":false,"colorscheme":false,"readlog":true,"allpagerights":true}}'),
      array('login.test3@sbcms.de', 'TEST09', '{"SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE":{"publish":false,"modules":false,"templates":false,"colorscheme":false,"readlog":false,"allpagerights":false}}'),
      array('login.test4@sbcms.de', 'TEST09', '{"SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE":{"publish":true,"modules":false,"templates":true,"colorscheme":false,"readlog":false,"allpagerights":false}}'),
      array('login.test5@sbcms.de', 'TEST09', '{"SITE-ui6m241a-l981-njaq-81c7-mjaq12kce4ar-SITE":{"publish":false,"modules":false,"templates":true,"colorscheme":false,"readlog":false,"allpagerights":true}}')
    );
  }

  /**
   * @test
   * @group integration
   */
  public function test_infoShouldRespondWithExpectedOwner()
  {
    // ARRANGE
    $ownerData = array(
      'dashboardUrl' => 'https://dashboardUrl/',
      'upgradeUrl' => 'https://upgradeUrl/',
    );
    $expectedOwner = ConfigHelper::setOwner($ownerData);
    $expectedUserInfo = array(
      'id' => $expectedOwner['id'],
      'lastname' => $expectedOwner['lastname'],
      'firstname' => $expectedOwner['firstname'],
      'email' => $expectedOwner['email'],
      'language' => $expectedOwner['language'],
      'superuser' => true,
      'owner' => true,
      'readonly' => true,
      'dashboardUrl' => $expectedOwner['dashboardUrl'],
      'upgradeUrl' => $expectedOwner['upgradeUrl'],
      'gender' => null,
      'groups' => array(),
      'privilege' => array()
    );
    $this->assertSuccessfulLogin($expectedOwner['email'], '123');

    // ACT
    $this->activateGroupCheck();
    $infoRequest = '/user/info';
    $this->dispatch($infoRequest);
    $this->deactivateGroupCheck();

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('userInfo', $responseData);
    $actualUserInfo = get_object_vars($responseData->userInfo);
    ksort($expectedUserInfo);
    ksort($actualUserInfo);
    $this->assertSame($expectedUserInfo, $actualUserInfo);
  }
}