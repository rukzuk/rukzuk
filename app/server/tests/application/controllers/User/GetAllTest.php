<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetAllTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function getAllOnUserWithInvalidWebsiteIdShouldReturnError($websiteId)
  {
    $getAllRequest = sprintf(
      '/user/getall/params/{"websiteId":"%s"}',
      $websiteId
    );

    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('websiteid', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllOnUserWithoutWebsiteIdShouldReturnExpectedUsers()
  {
    $getAllRequest = '/user/getall';

    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('users', $responseData);

    $actualUsers = $responseData->users;
    $this->assertInternalType('array', $actualUsers);
    $this->assertTrue(count($actualUsers) > 0);

    $expectedUserFields = array(
      'id',
      'lastname',
      'firstname',
      'gender',
      'email',
      'language',
      'superuser',
      'isdeletable',
      'groups',
      'owner',
      'readonly',
    );
    sort($expectedUserFields);

    foreach ($actualUsers as $actualUser)
    {
      $this->assertInstanceOf('stdClass', $actualUser);
      $actualUserFields = array_keys(get_object_vars($actualUser));
      sort($actualUserFields);
      $this->assertSame($expectedUserFields, $actualUserFields);

      if ($actualUser->groups !== null)
      {
        $this->assertInstanceOf('stdClass', $actualUser->groups);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllOnUserWithWebsiteIdShouldReturnExpectedUsers()
  {
    $websiteId = 'SITE-0r7g62te-0t4c-42c7-8628-f2cb4236e5gw-SITE';
    $getAllRequest = sprintf(
      '/user/getall/params/{"websiteId":"%s"}',
      $websiteId
    );

    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $expectedUsersOfWebsiteCount = 5;
    $expectedUsersIdsOfWebsite = array(
     'USER-re02eaa7-7fc5-464a-bd47-16b3b8af360p-USER',
     'USER-re02eaa7-7fc5-464a-bd47-16b3b8af361p-USER',
     'USER-re02eaa7-7fc5-464a-bd47-16b3b8af362p-USER',
     'USER-re02eaa7-7fc5-464a-bd47-16b3b8af363p-USER',
     'USER-re02eaa7-7fc5-464a-bd47-16b3b8af364p-USER'
    );

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('users', $responseData);
    $actualUsersOfWebsite = $responseData->users;
    $this->assertInternalType('array', $actualUsersOfWebsite);
    $actualUsersOfWebsiteCount = count($actualUsersOfWebsite);

    $this->assertSame($expectedUsersOfWebsiteCount, $actualUsersOfWebsiteCount);

    $actualUsersIdsOfWebsite = array_map(
      function ($user) {
        return $user->id;
      },
      $actualUsersOfWebsite
    );

    sort($expectedUsersIdsOfWebsite);
    sort($actualUsersIdsOfWebsite);

    $this->assertSame($expectedUsersIdsOfWebsite, $actualUsersIdsOfWebsite);
  }

  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
}