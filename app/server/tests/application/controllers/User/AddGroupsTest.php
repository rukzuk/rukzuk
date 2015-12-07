<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * AddGroupTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class AddGroupsTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function addGroupsOnUserShouldReturnValidationErrorForInvalidUserIds($userId)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $groupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-GROUP"'
    );
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );
    $this->dispatch($addGroupsRequest);
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
  public function addGroupsOnUserShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $groupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ex-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ex-GROUP"'
    );
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );
    $this->dispatch($addGroupsRequest);
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
  public function addGroupsOnUserShouldReturnValidationErrorForNonArrayGroupIds(
    $groupIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":"[%s]"}',
      $userId,
      $websiteId,
      $groupIds
    );
    $this->dispatch($addGroupsRequest);
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
  public function addGroupsOnUserShouldReturnValidationErrorForInvalidGroupIds(
    $groupIds)
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af00rg-SITE';
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03rg-USER';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $groupIds)
    );

    $this->dispatch($addGroupsRequest);
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
  public function addGroupsOnUserShouldAddGroupAsExpected()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-USER';
    $addGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ag-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-SITE';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $addGroupIds)
    );

    $this->dispatch($addGroupsRequest);
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
    if ($actualUserGroups !== null)
    {
      $this->assertInstanceOf('stdClass', $actualUserGroups);
      $actualUserGroups = array_values(get_object_vars($actualUserGroups));
      $actualUserGroups = $actualUserGroups[0];
    }
    $this->assertSame(count($addGroupIds), count($actualUserGroups));

    $expectedUserGroups = array(
      'GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-GROUP',
      'GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ag-GROUP'
    );

    sort($expectedUserGroups);
    sort($actualUserGroups);
    
    $this->assertSame($expectedUserGroups, $actualUserGroups);
  }

  /**
   * @test
   * @group integration
   */
  public function addGroupsOnNonExistingUserShouldReturnError()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8a40gn0-USER';
    $addGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ag-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-SITE';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $addGroupIds)
    );

    $this->dispatch($addGroupsRequest);
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
  public function addGroupsOnNonExistingGroupShouldReturnError()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-USER';
    $addGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8ag00no-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af01ag-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-SITE';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $addGroupIds)
    );

    $this->dispatch($addGroupsRequest);
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
  public function addGroupsWithUserAlreadyInGroupShouldReturnSuccess()
  {
    $userId = 'USER-0db7eaa7-7fc5-464a-bd47-16b3b8af03ai-USER';
    $addGroupIds = array(
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8ag00ai-GROUP"',
      '"GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af07ag-GROUP"'
    );
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-16b3b8af03ag-SITE';
    $addGroupsRequest = sprintf(
      '/user/addgroups/params/{"id":"%s","websiteid":"%s","groupIds":[%s]}',
      $userId,
      $websiteId,
      implode(',', $addGroupIds)
    );

    $this->dispatch($addGroupsRequest);
    $this->getValidatedSuccessResponse();
  }

  /**
   * @return array
   */
  public function invalidGroupIdsProvider()
  {
    return array(
      array(array('"ab"','"cd"')),
      array(array('"1"','"2"')),
      array(array('"MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL", "MODUL-0bin62pr-0t5f-28c9-eg28-f2cb4136ef45-MODUL"'))
    );
  }
  /**
   * @return array
   */
  public function nonArrayGroupIdsProvider()
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
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
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
}