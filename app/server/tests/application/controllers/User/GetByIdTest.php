<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * GetByIdTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetByIdTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function getByIdOnUserShouldReturnValidationErrorForInvalidUserIds($userId)
  {
    $getByIdRequest = sprintf('/user/getbyid/params/{"id":"%s"}',
      $userId
    );

    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('id', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function getByIdOnExistingUserShouldReturnUserAsExpected()
  {
    $userId = 'USER-gbi7e2cf-11r5-45e9-bc6d-a25cee8a74c1-USER';
    $getByIdRequest = sprintf('/user/getbyid/params/{"id":"%s"}',
      $userId
    );

    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualUserData = $response->getData();
    $this->assertNotEmpty($actualUserData);

    $actualUserFields = array_keys(get_object_vars($actualUserData));
    $minimalExpectedFieldCount = 11;
    $this->assertTrue(count($actualUserFields) === $minimalExpectedFieldCount);

    $expectedUserValues = array(
      'id' => $userId,
      'lastname' => 'lastname_get_by_id',
      'firstname' => 'firstname_get_by_id',
      'gender' => 'm',
      'email' => 'sbcms.test.getbyid@seitenbau.com',
      'language' => null,
      'superuser' => false,
      'isdeletable' => true,
      'owner' => false,
      'readonly' => false,
      'groups' => array(
        'SITE-ga37e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE' => array(
          'GROUP-gi00fg03-a3c4-4fdh-ard4-72ebb0878rf7-GROUP',
          'GROUP-gi00fg03-a3c4-4fdh-3rd5-72ebb0878rf7-GROUP',
          'GROUP-gi00fg03-a3c4-4fdh-3r5d-72ebb0878rf7-GROUP'
        )
      )
    );

    arsort($expectedUserValues['groups']);
    foreach($expectedUserValues['groups'] as $siteId => $websiteGroups)
    {
      sort($websiteGroups);
      $expectedUserValues['groups'][$siteId] = $websiteGroups;
    }
    
    $this->assertObjectHasAttribute('groups', $actualUserData);
    $this->assertInstanceOf('stdClass', $actualUserData->groups);
    $actualUserGroupData = get_object_vars($actualUserData->groups);
    $actualUserData->groups = array();
    foreach($actualUserGroupData as $siteId => $websiteGroups)
    {
      sort($websiteGroups);
      $actualUserData->groups[$siteId] = $websiteGroups;
    }

    foreach ($actualUserData as $key => $value)
    {
      $this->assertSame($expectedUserValues[$key], $value);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdOnNonExistingUserShouldReturnError()
  {
    $userId = 'USER-gbi7e2no-11r5-45e9-bc6d-a25cee8a74c1-USER';
    $getByIdRequest = sprintf('/user/getbyid/params/{"id":"%s"}',
      $userId
    );

    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @return array
   */
  public function invalidUserIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
  }
}