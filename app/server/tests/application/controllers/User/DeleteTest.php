<?php
namespace Application\Controller\User;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * DeleteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class DeleteTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidUserIdsProvider
   */
  public function deleteUserShouldReturnValidationErrorForInvalidUserIds($userId)
  {
    $request = sprintf(
      '/user/delete/params/{"id":"%s"}',
      $userId
    );
    $this->dispatch($request);
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
   */
  public function deleteUserShouldDeleteAssociatedOptinAsExpected()
  {
    $userId = 'USER-de67e2cf-1175-45e9-bcbd-a25cee8a74o0-USER';
    
    $optinService = new \Cms\Service\Optin('Optin');
    $passwordOptin = $optinService->getDao()->getByUserId($userId);
    $this->assertInstanceOf('\Orm\Entity\OptIn', $passwordOptin);
    $this->assertEquals(\Orm\Entity\OptIn::MODE_PASSWORD, $passwordOptin->getMode());
    
    $request = sprintf(
      '/user/delete/params/{"id":"%s"}',
      $userId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
    
    try {
      $optinService->getDao()->getByUserId($userId);
      $this->fail('An expected exception has not been raised.');
    } catch (\Exception $e) {
      $this->assertInstanceOf('Cms\Exception', $e);
    }
  }  
  /**
   * @test
   * @group integration
   */
  public function deleteUserShouldDeleteDeletableUserAsExpected()
  {
    $userId = 'USER-de67e2cf-1175-45e9-bcbd-a25cee8a74c1-USER';
    $request = sprintf(
      '/user/delete/params/{"id":"%s"}',
      $userId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
  }
  
  /**
   * @test
   * @group integration
   */
  public function deleteUserShouldAlsoRemoveUserFromGroupUsersJson()
  {
    $websiteId = 'SITE-0db7eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $userId = 'USER-de67e2cf-1175-45e9-bcbd-a25ce48a1ngr-USER';
    
    $deleteRequest = sprintf(
      '/user/delete/params/{"id":"%s"}',
      $userId
    );
    $this->dispatch($deleteRequest);
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $getAllGroupsByWebsiteIdRequest = sprintf(
      '/group/getall/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllGroupsByWebsiteIdRequest);
    $response = $this->getResponseBody();
    
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('groups', $responseData);
    $actualGroupsAfterDelete = $responseData->groups;
    $this->assertInternalType('array', $actualGroupsAfterDelete);
    
    $userIdsOfGroup = array();
    foreach ($actualGroupsAfterDelete as $group) 
    {
      $this->assertInstanceOf('stdClass', $group);
      $this->assertObjectHasAttribute('users', $group);
      $this->assertInternalType('array', $group->users);
      foreach ($group->users as $user) 
      {
        $this->assertInstanceOf('stdClass', $user);
        $this->assertObjectHasAttribute('id', $user);
        $userIdsOfGroup[] = $user->id;
      }
    }
    $this->assertNotContains($userId, $userIdsOfGroup);
  }  
  
  /**
   * @test
   * @group integration
   */
  public function deleteUserShouldNotDeleteNonDeletableUser()
  {
    $userId = 'USER-nd67e2cf-1175-45e9-bcbd-a25cee8a74c1-USER';
    $request = sprintf(
      '/user/delete/params/{"id":"%s"}',
      $userId
    );
    $this->dispatch($request);
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