<?php
namespace Application\Controller\Group;

use Orm\Data\Group as DataGroup,    
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * GetById
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetById extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function getByIdOnGroupShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    
    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
        
    $responseError = $responseObject->error[0];
    $this->assertSame('id', $responseError->param->field);
  }
  
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function getByIdOnGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $groupId = 'GROUP-ce6e702f-10ac-4e1e-951f-307e4b8765al-GROUP';
    
    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
    
    $responseError = $responseObject->error[0];
    $this->assertSame('websiteid', $responseError->param->field);
  }
  
  /**
   * @test
   * @group integration
   */
  public function getByIdOnGroupShouldReturnUsersNotJustUserIds()
  {
    $expectedUserCount = 2;
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
    $groupId = 'GROUP-gbi54g03-a3c4-4fdh-ard4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-gbi7e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE';
    
    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    $responseData = $responseObject->data;
    $this->assertObjectHasAttribute('users', $responseData);
    $actualGroupUsers = $responseData->users;
    
    $this->assertSame($expectedUserCount, count($actualGroupUsers));
    
    foreach ($actualGroupUsers as $user) 
    {
      $this->assertInstanceOf('stdClass', $user);
      $actualUserFields = array_keys(get_object_vars($user));
      sort($actualUserFields);
      sort($expectedUserFields);
      $this->assertSame($expectedUserFields, $actualUserFields);
    }
  }
  
  /**
   * @test
   * @group integration
   */
  public function getByIdOnGroupShouldReturnGroupAsExpected()
  { 
    $groupId = 'GROUP-gbi54g03-a3c4-4fdh-ard4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-gbi7e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE';
    
    $expectedGroupUsers =array(
      'USER-gbi7e2cf-11r5-45e9-bc6d-a25cee8afh01-USER',
      'USER-gbi7e2cf-11r5-45e9-bc6d-a25cee8afh02-USER'
    );
    $expectedGroupRights =array(
      0 => array(
        'area' => 'website',
        'privilege' => 'publish',
        'ids' => null
      ),
      1 => array(
        'area' => 'modules',
        'privilege' => 'all',
        'ids' => null
      ),
      2 => array(
        'area' => 'pages',
        'privilege' => 'edit',
        'ids' => array(
         'PAGE-03565eb8-0363-47e9-a3pu-90ae9d96d3c2-PAGE',
         'PAGE-03565eb8-0363-47e9-a2pu-90ae9d96d3c2-PAGE'   
        )
      )
    );
    
    $expectedGroupName = 'controller_get_by_id_group_name';
    
    $expectedGroupCount = count($expectedGroupUsers);
    
    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    $actualGroup = $responseObject->data;
    $this->assertInstanceOf('stdClass', $actualGroup);
    $actualGroupVars = get_object_vars($actualGroup);
    
    $expectedGroupFieldsAndTheirValues = array(
      'id' => $groupId, 
      'websiteId' => $websiteId, 
      'name' => $expectedGroupName, 
      'rights' => $expectedGroupRights, 
      'users' => $expectedGroupUsers
    );
    
    $expectedGroupFields = array_keys($expectedGroupFieldsAndTheirValues);
    $actualGroupFields = array_keys($actualGroupVars);
    
    sort($expectedGroupFields);
    sort($actualGroupFields);
    
    $assertionMessagePlain = "Expected group fields (%s) doesn't match actual "
      . "group fields (%s)";
    $assertionMessage = sprintf(
      $assertionMessagePlain,
      implode(', ', $expectedGroupFields),
      implode(', ', $actualGroupFields)
    );
    
    $this->assertSame(
      $expectedGroupFields, 
      $actualGroupFields, 
      $assertionMessage
    );
        
    foreach ($actualGroupVars as $key => $values) 
    {
      if ($key == 'users') 
      {
        $this->assertInternalType('array', $values);        
        foreach ($expectedGroupFieldsAndTheirValues[$key] as $userIdZaehler => $userId)
        {
          $this->assertInstanceOf('stdClass', $values[$userIdZaehler]);
          $this->assertObjectHasAttribute('id', $values[$userIdZaehler]);
          $this->assertSame($userId, $values[$userIdZaehler]->id);
        }
      }
      elseif ($key == 'rights' || $key == 'users') 
      {
        $this->assertInternalType('array', $values);
        
        foreach ($expectedGroupFieldsAndTheirValues[$key] 
          as $rightZaehler => $expectedRight
        ){
          $this->assertArrayHasKey($rightZaehler, $values);
          $groupRight = $values[$rightZaehler];
          $this->assertInstanceOf('stdClass', $groupRight);
          foreach ($expectedRight as $expectedRightName => $expectedRightValue)
          {
            $this->assertObjectHasAttribute($expectedRightName, $groupRight);
            $this->assertSame($expectedRightValue, $groupRight->$expectedRightName);
          }
        }
      }
      else
      {
        $this->assertSame($expectedGroupFieldsAndTheirValues[$key], $values);
      }
    }    
  }
  
  /**
   * Standard-User darf keine Gruppe abfragen
   *
   * @test
   * @group integration
   */
  public function getByIdShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-gbi54g03-a3c4-4fdh-ard4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-gbi7e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE';

    $request = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
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
  public function superuserGetByIdShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-gbi54g03-a3c4-4fdh-ard4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-gbi7e89c-r2ag-48cd-a6t9-fc45ds78fe5s-SITE';

    $request = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
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
  public function invalidGroupIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('PAGE-0rap62te-0t4c-42c7-8628-f2cb4236eb45-PAGE'),
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
      array('some_test_value'),
      array('MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-MODUL'),
    );
  }
}