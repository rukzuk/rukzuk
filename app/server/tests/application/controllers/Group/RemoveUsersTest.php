<?php
namespace Application\Controller\Group;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * RemoveUsersTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class RemoveUsersTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function removeUsersOnGroupShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userIds = array(
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-USER"',
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-USER"'
    );
    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );
    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $errorData = $responseObject->error[0];
    $this->assertSame('id', $errorData->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function removeUsersOnGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {

    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';
    $userIds = array(
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-USER"',
      '"USER-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-USER"'
    );
    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );
    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $errorData = $responseObject->error[0];
    $this->assertSame('websiteid', $errorData->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider nonArrayUserIdsProvider
   */
  public function removeUsersOnGroupShouldReturnValidationErrorForNonArrayUserIds(
    $userIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":"[%s]"}',
      $groupId,
      $websiteId,
      $userIds
    );
    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $errorData = $responseObject->error[0];
    $this->assertSame('userids', $errorData->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function removeUsersOnGroupShouldReturnValidationErrorForInvalidUserIds(
    $userIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupId = 'GROUP-0db7eaa7-7fc5-464a-bd47-16b3j8af03rg-GROUP';

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );

    $this->dispatch($addUsersRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $errorData = $responseObject->error[0];
    $this->assertSame('userid', $errorData->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function removeUsersOnNonExistingGroupShouldReturnError()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af00no-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af00no-SITE';
    $userIds = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
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
  public function removeUsersWithNonExistingUsersShouldReturnSuccess()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af1nfu-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af0nfg-SITE';
    $nonxExistingUserIdsToRemove = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8nf00fg-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8nf01fh-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8nf01hi-USER"'
    );

    $removeUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $nonxExistingUserIdsToRemove)
    );

    $this->dispatch($removeUsersRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function removeUsersOnUserlessGroupShouldReturnSuccess()
  {
    $groupId = 'GROUP-au00e5a7-7fc5-464a-bd47-16b3b8af1nou-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8af0nou-SITE';
    $userIds = array(
      '"USER-au00ega7-7fc5-464a-bd47-16b3b8af00kl-USER"',
      '"USER-au01eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"',
      '"USER-au02eaa7-7fc5-464a-bd47-16b3b8af01zk-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIds)
    );

    $this->dispatch($addUsersRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function removeUsersOnUserShouldRemoveUsersAsExpected()
  {
    $groupId = 'GROUP-re00e5a7-7fc5-464a-bd47-16b3b8af15su-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8aff5su-SITE';
    $expectedUserIdsAfterRemove = array(
      'USER-re02eaa7-7fc5-464a-bd47-16b3b8afkeep-USER'
    );
    $expectedUserCountAfterRemove = count($expectedUserIdsAfterRemove);
    $userIdsToRemove = array(
      '"USER-re00ega7-7fc5-464a-bd47-16b3b8af00kr-USER"',
      '"USER-re01eaa7-7fc5-464a-bd47-16b3b8af01zr-USER"',
      '"USER-re02eaa7-7fc5-464a-bd47-16b3b8af01zg-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToRemove)
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

    $this->assertSame($expectedUserCountAfterRemove, count($actualGroupUsers));

    $actualGroupUsersIdsAfterRemove = array_map(
      function ($user) {
        return $user->id;
      },
      $actualGroupUsers
    );

    $this->assertSame($expectedUserIdsAfterRemove, $actualGroupUsersIdsAfterRemove);
  }

  /**
   * @test
   * @group integration
   */
  public function removeUsersOnUserWithUsersNotInGroupShouldReturnSuccess()
  {
    $groupId = 'GROUP-re00e5a7-7fc5-464a-bd47-16b3b8af15nu-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8aff5nu-SITE';
    $expectedUserIdsAfterRemove = array(
      'USER-re02eaa7-7fc5-464a-bd47-16b3b8afkeep-USER'
    );
    $expectedUserCountAfterRemove = count($expectedUserIdsAfterRemove);
    $userIdsToRemove = array(
      '"USER-re00ega7-7fc5-464a-bd47-16b3b8af00nu-USER"',
      '"USER-re01eaa7-7fc5-464a-bd47-16b3b8af01nu-USER"',
      '"USER-re02eaa7-7fc5-464a-bd47-16b3b8af01nu-USER"'
    );

    $addUsersRequest = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToRemove)
    );

    $this->dispatch($addUsersRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * Standard-User darf keine User aus Gruppe loeschen
   *
   * @test
   * @group integration
   */
  public function removeUsersShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-re00e5a7-7fc5-464a-bd47-16b3b8af15su-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8aff5su-SITE';
    $userIdsToRemove = array(
      '"USER-re00ega7-7fc5-464a-bd47-16b3b8af00kr-USER"',
      '"USER-re01eaa7-7fc5-464a-bd47-16b3b8af01zr-USER"',
      '"USER-re02eaa7-7fc5-464a-bd47-16b3b8af01zg-USER"'
    );

    $request = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToRemove)
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
   * Super-User darf alle User aus Gruppen loeschen
   *
   * @test
   * @group integration
   */
  public function superuserRemoveUsersShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-re00e5a7-7fc5-464a-bd47-16b3b8af15su-GROUP';
    $websiteId = 'SITE-au00eaa7-7fc5-464a-bd47-16b3b8aff5su-SITE';
    $userIdsToRemove = array(
      '"USER-re00ega7-7fc5-464a-bd47-16b3b8af00kr-USER"',
      '"USER-re01eaa7-7fc5-464a-bd47-16b3b8af01zr-USER"',
      '"USER-re02eaa7-7fc5-464a-bd47-16b3b8af01zg-USER"'
    );

    $request = sprintf(
      '/group/removeusers/params/{"id":"%s","websiteid":"%s","userIds":[%s]}',
      $groupId,
      $websiteId,
      implode(',', $userIdsToRemove)
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