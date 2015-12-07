<?php
namespace Application\Controller\Group;

use Orm\Data\Group as DataGroup,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * GetAllTest
 *
 * @package      $package
 * @subpackage   $subpackage
 */
class GetAllTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function getAllOnGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
    
    $this->assertSame('websiteid', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllOnGroupWithUnassociatedWebsiteIdShouldReturnError()
  {
    $websiteId = 'SITE-no37e89c-r2no-18cf-a6n9-ff45ds98f6no-SITE';
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
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
  public function getAllOnGroupShouldReturnUserNotJustUserIds()
  {
    $expectedGroupsCount = 2;
    $expectedUserCountPerGroups = array(2, 4);

    $expectedUserFields = array(
      'id',
      'lastname',
      'firstname',
      'gender',
      'email',
      'language',
      'superuser',
      'isdeletable',
      'owner',
      'readonly',
      'groups',
    );
    $websiteId = 'SITE-ga37e89c-r2ag-18cf-a6n9-ff45ds98fusr-SITE';

    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );

    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    $responseData = $responseObject->data;

    $this->assertObjectHasAttribute('groups', $responseData);
    $this->assertInternalType('array', $responseData->groups);
    
    $actualGroups = $responseData->groups;
    $this->assertSame($expectedGroupsCount, count($actualGroups));

    for ($i = 0; $i < $expectedGroupsCount; $i++) 
    {
      $this->assertInstanceOf('stdClass', $actualGroups[$i]);
      $this->assertObjectHasAttribute('users', $actualGroups[$i]);
      $usersOfGroup = $actualGroups[$i]->users;
      $this->assertInternalType('array', $usersOfGroup);
      $this->assertSame($expectedUserCountPerGroups[$i], count($usersOfGroup));
      foreach ($usersOfGroup as $user) 
      {
        $this->assertInstanceOf('stdClass', $user);
        $actualUserFields = array_keys(get_object_vars($user));
        sort($actualUserFields);
        sort($expectedUserFields);
        $this->assertSame($expectedUserFields, $actualUserFields);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllOnGroupShouldReturnGroupsAsExpected()
  {
    $websiteId = 'SITE-ga37e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE';
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $responseData = $responseObject->data;
    $this->assertObjectHasAttribute('groups', $responseData);
    $actualGroups = $responseData->groups;
    $this->assertInternalType('array', $actualGroups);
    $this->assertTrue(count($actualGroups) > 0);

    $expectedGroupFields = array(
      'id',
      'websiteId',
      'name',
      'rights',
      'users'
    );
    sort($expectedGroupFields);

    foreach ($actualGroups as $actualGroup) 
    {
      $this->assertInstanceOf('stdClass', $actualGroup);
      $actualGroupFields = array_keys(get_object_vars($actualGroup));
      sort($actualGroupFields);
      $this->assertSame($expectedGroupFields, $actualGroupFields);
      $this->assertObjectHasAttribute('users', $actualGroup);
      $this->assertInternalType('array', $actualGroup->users);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllOnGroupShouldReturnExpectedUsers()
  {
    $expectedGroupUserIds = array(
      'GROUP-0db7eag7-7fc5-464a-bd47-nob3b8ag00uv-GROUP' => array(
        'USER-0ex7e4a7-7fc5-464a-bd47-06b3b8af03kd-USER',
        'USER-2ex7e2cf-1175-45e9-bcbd-a25ce48a1ngr-USER'
      ),
      'GROUP-0db7eag7-7fc5-464a-bd49-nob3b8ag01uv-GROUP' => array(
        'USER-1ex7e4a7-7fc5-464a-bd47-06b3b8af03kd-USER',
        'USER-0ex7e4a7-7fc5-464a-bd47-06b3b8af03kd-USER',
        'USER-3ex7e2cf-1175-45e9-bcbd-a25ce48a1ngr-USER',
        'USER-2ex7e2cf-1175-45e9-bcbd-a25ce48a1ngr-USER'
      )
    );
    $expectedGroupCount = count(array_keys($expectedGroupUserIds));

    $websiteId = 'SITE-ga37e89c-r2ag-18cf-a6n9-ff45ds98fusr-SITE';
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $responseData = $responseObject->data;
    $this->assertObjectHasAttribute('groups', $responseData);
    $actualGroups = $responseData->groups;
    $this->assertInternalType('array', $actualGroups);
    $this->assertSame($expectedGroupCount, count($actualGroups));

    foreach ($actualGroups as $actualGroup)
    {
      $this->assertInstanceOf('stdClass', $actualGroup);
      $this->assertObjectHasAttribute('id', $actualGroup);
      $this->assertObjectHasAttribute('users', $actualGroup);
      $this->assertInternalType('array', $actualGroup->users);
      $expectedUserIds = $expectedGroupUserIds[$actualGroup->id];
      $actualUserIds = array();
      $actualUsers = $actualGroup->users;

      foreach ($actualUsers as $user) 
      {
        $this->assertInstanceOf('stdClass', $user);
        $this->assertObjectHasAttribute('id', $user);
        $actualUserIds[] = $user->id;
      }

      sort($expectedUserIds);
      sort($actualUserIds);

      $this->assertSame($expectedUserIds, $actualUserIds);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllObGroupShouldReturnEmptyArrayIfNoGroupExist()
  {
    $websiteId = 'SITE-jhs812md-4s25-x1sa-bn5g-ki98m2cde5gw-SITE';
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    $responseData = $responseObject->data;
    $this->assertObjectHasAttribute('groups', $responseData);
    $actualGroups = $responseData->groups;
    $this->assertInternalType('array', $actualGroups);
    $this->assertSame(0, count($actualGroups));
  }

  /**
   * Standard-User darf keine Gruppe abfragen
   *
   * @test
   * @group integration
   */
  public function getAllShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-ga37e89c-r2ag-18cf-a6n9-ff45ds98fusr-SITE';
    $request = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
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
   * Super-User darf alle Gruppen abfragen
   *
   * @test
   * @group integration
   */
  public function superuserGetAllShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-ga37e89c-r2ag-18cf-a6n9-ff45ds98fusr-SITE';
    $request = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
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
      array('some_test_value'),
      array('MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-MODUL'),
    );
  }
}