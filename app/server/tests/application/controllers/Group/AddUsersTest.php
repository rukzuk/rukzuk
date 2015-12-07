<?php
namespace Application\Controller\Group;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * AddUsersTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class AddUsersTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function addUsersOnGroupShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userIds = array(
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-USER"',
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-USER"'
    );
    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );
    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('id', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function addUsersOnGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {

    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';
    $userIds = array(
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-USER"',
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-USER"'
    );
    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );
    $this->dispatch($addUsersRequest);

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
   * @dataProvider nonArrayUserIdsProvider
   */
  public function addUsersOnGroupShouldReturnValidationErrorForNonArrayUserIds(
    $userIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":"[%s]"}',
      $groupId,
      $websiteId,
      $userIds
    );
    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('userids', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function addUsersOnGroupShouldReturnValidationErrorForInvalidUserIds(
    $userIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('userid', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function addUsersOnNonExistingGroupShouldReturnError()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af00no-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af00no-SITE';
    $userIds = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }
  /**
   * @test
   * @group integration
   */
  public function addUsersWithNonExistingUsersShouldReturnError()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-nd47-16b3b8af00de-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-nd47-16b3b8af00fr-SITE';
    $userIdsToAdd = array(
      '"USER-af00eno7-7fc5-464a-bd47-n6b3b8af00kl-USER"',
      '"USER-ag01eno8-7fc5-464a-bd47-n6b3b8af01zk-USER"',
      '"USER-ah02eno9-7fc5-464a-bd47-n6b3b8af01zk-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToAdd)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function addUsersOnGroupShouldAddUsersAsExpectedOnNonExistingUsers()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-nd47-16b3b8af00de-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-nd47-16b3b8af00fr-SITE';
    $userIdsToAdd = array(
      '"USER-au00ega7-7fc5-464a-bd47-n6b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-n6b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-n6b3b8af01zk-USER"'
    );

    $expectedUserCount = count($userIdsToAdd);

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToAdd)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualGroup = $response->getData();
    $actualGroupUsers = $actualGroup->users;

    $this->assertSame($expectedUserCount, count($actualGroupUsers));

    $expectedGroupUsersIds = $userIdsToAdd;
    $expectedGroupUsersIds = array_map(
      function ($id) {
        return str_replace('"','', $id);
      },
    $expectedGroupUsersIds);

    $actualGroupUsersIds = array_map(
      function ($user) {
        return $user->id;
      },
    $actualGroupUsers);

    sort($actualGroupUsersIds);
    sort($expectedGroupUsersIds);

    $this->assertSame($expectedGroupUsersIds, $actualGroupUsersIds);
  }
  /**
   * @test
   * @group integration
   */
  public function addUsersOnGroupShouldAddUsersAsExpectedOnExistingUsers()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af00de-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af00fr-SITE';
    $userIdsToAdd = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );
    $alreadyExistingGroupUserIds = array(
      'USER-au00ega0-7fc5-464a-bd47-16b3b8af00kl-USER',
      'USER-au00ega1-7fc5-464a-bd47-16b3b8af00kl-USER'
    );

    $expectedUserCount = count($userIdsToAdd) + count($alreadyExistingGroupUserIds);

    $addUsersRequest = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToAdd)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualGroup = $response->getData();
    $actualGroupUsers = $actualGroup->users;
    $this->assertSame($expectedUserCount ,count($actualGroupUsers));

    $expectedGroupUsersIds = array_merge(
      $userIdsToAdd,
      $alreadyExistingGroupUserIds
    );
    $expectedGroupUsersIds = array_map(
      function ($id) {
        return str_replace('"','', $id);
      },
    $expectedGroupUsersIds);

    $actualGroupUsersIds = array_map(
      function ($user) {
        return $user->id;
      },
    $actualGroupUsers);

    sort($actualGroupUsersIds);
    sort($expectedGroupUsersIds);

    $this->assertSame($expectedGroupUsersIds, $actualGroupUsersIds);
  }

  /**
   * Standard-User darf keine neuen User zuordnen
   *
   * @test
   * @group integration
   */
  public function addUsersShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af00de-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af00fr-SITE';
    $userIdsToAdd = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );

    $request = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToAdd)
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
   * Super-User darf neue User zuordnen
   *
   * @test
   * @group integration
   */
  public function superuserAddUsersShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af00de-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af00fr-SITE';
    $userIdsToAdd = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );

    $request = sprintf(
      '/group/addusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToAdd)
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
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('another_test_value'),
      array('PAGE-0rap62te-0t4c-42c7-8628-f2cb4236eb45-PAGE'),
    );
  }
  /**
   * @return array
   */
  public function invalidGroupIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('another_test_value'),
      array('USER-0rap62te-0t4c-42c7-8628-f2cb4236eb45-USER'),
    );
  }
  /**
   * @return array
   */
  public function nonArrayUserIdsProvider()
  {
    return array(
      array('[]'),
      array('quark'),
      array(null),
      array(15)
    );
  }
  /**
   * @return array
   */
  public function invalidUserIdsProvider()
  {
    return array(
      array(array('"ab"','"cd"')),
      array(array('"1"','"2"')),
      array(array('"MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL", "MODUL-0bin62pr-0t5f-28c9-eg28-f2cb4136ef45-MODUL"'))
    );
  }
}