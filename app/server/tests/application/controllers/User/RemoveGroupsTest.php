<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * RemoveGroupsTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class RemoveGroupsTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function removeGroupsOnUserShouldReturnValidationErrorForInvalidUserIds($userId)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-GROUP"'
    );
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );
    $this->dispatch($removeGroupsRequest);
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
  public function removeGroupsOnUserShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $groupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-GROUP"'
    );
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );
    $this->dispatch($removeGroupsRequest);
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
   * @dataProvider nonArrayGroupIdsProvider
   */
  public function removeGroupsOnUserShouldReturnValidationErrorForNonArrayGroupIds(
    $groupIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":"[%s]"}',
      $userId,
      $websiteId,
      $groupIds
    );
    $this->dispatch($removeGroupsRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('groupids', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function removeGroupsOnUserShouldReturnValidationErrorForInvalidGroupIds(
    $groupIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );

    $this->dispatch($removeGroupsRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('groupid', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function removeGroupsOnNonExistingUserShouldReturnSuccess()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8a40gn0-USER';
    $removeGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ag-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-SITE';
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $removeGroupIds)
    );

    $this->dispatch($removeGroupsRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function removeGroupsOnNonExistingGroupShouldReturnError()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-06b3b8af03rg-USER';
    $removeGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8ag00no-GROUP"',
      '"GROUP-1fb7eaa7-7fc5-464a-bd47-16b3b8af07rg-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-06b3b8af00rg-SITE';
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $removeGroupIds)
    );

    $this->dispatch($removeGroupsRequest);
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
  public function removeGroupsWithUserNotInGroupShouldReturnSuccess()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-06b3b8af03rg-USER';
    $removeGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-nob3b8ag00rg-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-nob3b8af07rg-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-06b3b8af00rg-SITE';
    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $removeGroupIds)
    );

    $this->dispatch($removeGroupsRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function removeGroupsOnUserShouldRemoveGroupsAsExpected()
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-06b3b8af00rg-SITE';
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-06b3b8af03rg-USER';
    $removeGroupIds = array(
      '"GROUP-1fb7eaa7-7fc5-464a-bd47-16b3b8ag00rg-GROUP"',
      '"GROUP-1fb7eaa7-7fc5-464a-bd47-16b3b8af07rg-GROUP"'
    );

    $removeGroupsRequest = sprintf(
      '/user/removegroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $removeGroupIds)
    );

    $this->dispatch($removeGroupsRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

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
    $this->assertObjectHasAttribute('groups', $actualUserData);
    $actualUserGroups = $actualUserData->groups;
    $this->assertNull($actualUserGroups);
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
      array('SITE-0rap62te-0t4c-42c7-8628-f2cb4236eb45-SITE'),
    );
  }
  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(''),
      array(15),
      array('something'),
      array('PAGE-0rap62te-0t4c-42c7-8628-f2cb4236eb45-PAGE'),
    );
  }
  /**
   * @return array
   */
  public function nonArrayGroupIdsProvider()
  {
    return array(
      array(''),
      array('[]'),
      array('quark'),
      array(null),
      array(15)
    );
  }
  /**
   * @return array
   */
  public function invalidGroupIdsProvider()
  {
    return array(
      array(array('""', '""')),
      array(array('"ab"', '"cd"')),
      array(array('"1"', '"2"')),
      array(array('"TPL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-TPL", "TPL-0bin62pr-0t5f-28c9-eg28-f2cb4136ef45-TPL"'))
    );
  }
}