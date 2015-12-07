<?php
namespace Application\Controller\Group;

use Orm\Data\Group as DataGroup,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * EditTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function editGroupShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/edit/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $groupId,
      $websiteId,
      $groupName
    );
    $this->dispatch($request);
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
   * @dataProvider invalidGroupIdsProvider
   */
  public function editGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $groupId = 'GROUP-ce6e702f-10ac-4e1e-951f-307e4b8765al-GROUP';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/edit/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $groupId,
      $websiteId,
      $groupName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('websiteid', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidNamesProvider
   */
  public function editGroupShouldReturnValidationErrorForInvalidNames($groupName)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupId = 'GROUP-ce6e702f-10ac-4e1e-951f-307e4b8765al-GROUP';
    $request = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $groupId,
      $groupName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('name', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidRightsProvider
   */
  public function editGroupShouldReturnValidationErrorForInvalidRights($rights)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b876ved-SITE';
    $groupId = 'GROUP-ce6e702f-10ac-4e1e-951f-307e4b876ved-GROUP';
    $groupName = 'test_group_name';

    $request = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $groupName,
      json_encode(array($rights))
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('right', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function editGroupShouldReturnUsersNotJustUserIds()
  {
    $expectedGroupUsersCount = 1;
    $expectedGroupUserFields = array(
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

    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5d-SITE';
    $groupId = 'GROUP-edl54f03-acc4-4fdb-add4-72ebb0878ret-GROUP';

    $alteredGroupName = 'controller_edit_group_name_altered';
    $alteredGroupRights[] = array(
      'area' => 'templates',
      'privilege' => 'none',
      'ids' => null
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $alteredGroupName,
      json_encode($alteredGroupRights)
    );

    $this->dispatch($editRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $editedGroup = $response->getData();
    $editedGroupUsers = $editedGroup->users;

    $editedGroupUsersValues = get_object_vars($editedGroupUsers[0]);

    $this->assertSame($expectedGroupUsersCount, count($editedGroupUsers));
    $this->assertSame($expectedGroupUserFields, array_keys($editedGroupUsersValues));
  }

  /**
   * @test
   * @group integration
   */
  public function editGroupShouldReturnGroupJson()
  {
    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5d-SITE';
    $groupId = 'GROUP-edl54f03-acc4-4fdb-add4-72ebb0878ret-GROUP';

    $alteredGroupName = 'controller_edit_group_name_altered';
    $templateRights = new \stdClass();
    $templateRights->area = 'templates';
    $templateRights->privilege = 'none';
    $templateRights->ids = null;
    $alteredGroupRights[] = $templateRights;

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $alteredGroupName,
      json_encode($alteredGroupRights)
    );

    $this->dispatch($editRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $expectedEditedGroup = array(
     'id' => $groupId,
     'websiteId' => $websiteId,
     'name' => $alteredGroupName,
     'rights' => $alteredGroupRights,
     'users' => array('USER-ov04egs7-7fc5-4f4a-bd49-16b3b8jf09n9-USER')
    );

    $editedGroup = $response->getData();
    $this->assertInstanceOf('stdClass', $editedGroup);
    $editedGroupValues = get_object_vars($editedGroup);
    $this->assertTrue((count($editedGroupValues) > 0), 'Keine Daten fuer Vergleich zurueck bekommen');

    foreach ($editedGroupValues as $key => $value)
    {
      if ($key === 'users')
      {
        $actualUserIds = array();
        foreach ($value as $user)
        {
          $actualUserIds[] = $user->id;
        }
        $this->assertSame($expectedEditedGroup[$key], $actualUserIds);
      }
      elseif ($key == 'rights')
      {
        foreach ($expectedEditedGroup['rights'] as $zaehler => $expectedRight)
        {
          $this->assertArrayHasKey($zaehler, $value);

          $expectedValues = get_object_vars($expectedRight);

          foreach ($expectedValues as $expectedKey => $expectedValue)
          {
            $this->assertObjectHasAttribute($expectedKey, $value[$zaehler]);
            $this->assertSame($expectedValue, $value[$zaehler]->$expectedKey);
          }
        }
      }
      else
      {
        $this->assertSame($expectedEditedGroup[$key], $value);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function editGroupShouldAlterGroupAsExpected()
  {
    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5r-SITE';
    $groupId = 'GROUP-edl54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';

    $alteredGroupName = 'controller_edit_group_name_altered';
    $templateRights = new \stdClass();
    $templateRights->area = 'templates';
    $templateRights->privilege = 'none';
    $templateRights->ids = null;
    $alteredGroupRights[] = $templateRights;

    $expectedGroupCountBeforeEdit = 1;
    $expectedGroupCountAfterEdit  = 1;

    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('groups', $responseData);
    $this->assertTrue(
      count($responseData->groups) === $expectedGroupCountBeforeEdit
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $alteredGroupName,
      json_encode($alteredGroupRights)
    );

    $this->dispatch($editRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('groups', $responseData);
    $this->assertTrue(
      count($responseData->groups) === $expectedGroupCountBeforeEdit
    );

    $allGroupsForWebsiteId = $responseData->groups;
    $this->assertInstanceOf('stdClass', $responseData);
    foreach ($allGroupsForWebsiteId as $groupOfWebsiteId)
    {
      $this->assertSame($groupId, $groupOfWebsiteId->id);
      $this->assertSame($websiteId, $groupOfWebsiteId->websiteId);
      $this->assertTrue(
        $this->validateUniqueId(new DataGroup, $groupOfWebsiteId->id)
      );
      $this->assertSame($alteredGroupName, $groupOfWebsiteId->name);

      $this->assertInternalType('array', $groupOfWebsiteId->rights,
        'Rechte muessen als Array uebergeben werden');
      foreach ($alteredGroupRights as $key => $alteredGroupRight)
      {
        $this->assertArrayHasKey($key, $groupOfWebsiteId->rights);
        $rightAttributes = get_object_vars($alteredGroupRight);
        foreach ($rightAttributes as $right => $value)
        {
          $this->assertObjectHasAttribute($right, $groupOfWebsiteId->rights[$key]);
          $this->assertSame($value, $groupOfWebsiteId->rights[$key]->$right);
        }
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function editGroupShouldOverwriteExistingRightsAndKeepExistingOnesWhichAreNotEdited()
  {
    $websiteId = 'SITE-or17e8f7-r2af-46cd-a6t1-fc42dc78fe5s-SITE';
    $groupId = 'GROUP-orl54f03-ri49-4fdb-add4-72ebb0878rf7-GROUP';

    $pagesRights = new \stdClass();
    $pagesRights->area = 'pages';
    $pagesRights->privilege = 'subEdit';
    $pagesRights->ids = array(
      'PAGE-163b62or-b045-40ce-8b4e-c795a87a03ex-PAGE',
      'PAGE-163b62or-b046-40ce-8b4e-c795a87a03ex-PAGE'
    );
    $modulesRights = new \stdClass();
    $modulesRights->area = 'modules';
    $modulesRights->privilege = 'all';
    $modulesRights->ids = null;
    $templatesRights = new \stdClass();
    $templatesRights->area = 'templates';
    $templatesRights->privilege = 'none';
    $templatesRights->ids = null;

    $previousRights = array(
      $modulesRights,
      $pagesRights,
      $templatesRights
    );

    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $actualGroup = $response->getData();

    $expectedPreviousRights = $previousRights;
    sort($expectedPreviousRights);
    sort($actualGroup->rights);

    foreach ($expectedPreviousRights as $key => $expectedPreviousRight)
    {
      $this->assertArrayHasKey($key, $actualGroup->rights);

      $expectedValues = get_object_vars($expectedPreviousRight);

      foreach ($expectedValues as $expectedKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedKey, $actualGroup->rights[$key]);
        $this->assertSame($expectedValue, $actualGroup->rights[$key]->$expectedKey);
      }
    }

    $templateAllRight = new \stdClass();
    $templateAllRight->area = 'templates';
    $templateAllRight->privilege = 'all';
    $templateAllRight->ids = null;
    $websiteNoneRight = new \stdClass();
    $websiteNoneRight->area = 'website';
    $websiteNoneRight->privilege = 'none';
    $websiteNoneRight->ids = null;

    $overwriteRights = array(
      $templateAllRight,
      $websiteNoneRight
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","rights":%s}',
      $websiteId,
      $groupId,
      json_encode($overwriteRights)
    );

    $this->dispatch($editRequest);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $this->dispatch($getByIdRequest);

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $actualGroup = $response->getData();

    $expectedRightsAfterOverwrite = array_merge(
      $overwriteRights, array($previousRights[1]), array($previousRights[0])
    );

    sort($expectedRightsAfterOverwrite);
    sort($actualGroup->rights);


    foreach ($expectedRightsAfterOverwrite as $key => $expectedPreviousRight)
    {
      $this->assertArrayHasKey($key, $actualGroup->rights);

      $expectedValues = get_object_vars($expectedPreviousRight);

      foreach ($expectedValues as $expectedKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedKey, $actualGroup->rights[$key]);
        $this->assertSame($expectedValue, $actualGroup->rights[$key]->$expectedKey);
      }
    }
  }

  /**
   * @test
   * @integration
   */
  public function editGroupWithNoneRightsSetShouldResetGroupRights()
  {
    $websiteId = 'SITE-re17e8f7-r2af-46cd-a6t1-fc42dc78fe5s-SITE';
    $groupId = 'GROUP-rel54f03-ri49-4fdb-add4-72ebb0878rf7-GROUP';
    $resetRights = array(
      0 => array(
        'area' => 'website',
        'privilege' => 'none',
        'ids' => null
      ),
      1 => array(
        'area' => 'templates',
        'privilege' => 'none',
        'ids' => null
      )
    );
    $expectedName = 'group_reset_rights_test_0';
    $expectedUsers = array(
      'USER-rr04egs7-7fc5-4f4a-bd49-16b3b8af09n4-USER'
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","rights":%s}',
      $websiteId,
      $groupId,
      json_encode($resetRights)
    );

    $this->dispatch($editRequest);
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

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $actualGroup = $response->getData();

    $expectedValuesAfterEdit = array(
      'id' => $groupId,
      'websiteId' => $websiteId,
      'name' => $expectedName,
      'rights' => $resetRights,
      'users' => $expectedUsers
    );

    $actualUserIds = array();

    foreach ($actualGroup as $key => $value)
    {
      if ($key === 'users')
      {
         foreach ($value as $user)
         {
           $actualUserIds[] = $user->id;
         }
         $this->assertSame($expectedUsers, $actualUserIds);
      }
      else if ($key == 'rights')
      {
        foreach ($expectedValuesAfterEdit['rights'] as $zaehler => $expectedRight)
        {
          $this->assertArrayHasKey($zaehler, $value);

          foreach ($expectedRight as $expectedKey => $expectedValue)
          {
            $this->assertObjectHasAttribute($expectedKey, $value[$zaehler]);
            $this->assertSame($expectedValue, $value[$zaehler]->$expectedKey);
          }
        }
      }
      else
      {
        $this->assertSame($expectedValuesAfterEdit[$key], $value);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function editGroupShouldreturnErrorOnNonAllowedPagesArea()
  {
    $websiteId = 'SITE-re17e8f7-r2af-46cd-a6t1-fc42dc78fe5s-SITE';
    $groupId = 'GROUP-rel54f03-ri49-4fdb-add4-72ebb0878rf7-GROUP';
    $pagesRights = array(
      0 => array(
        'area' => 'pages',
        'privilege' => 'subEdit',
        'ids' => "['PAGE-163b62or-b045-40ce-8b4e-c795a87a03ex-PAGE']"
      )
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","rights":%s}',
      $websiteId,
      $groupId,
      json_encode($pagesRights)
    );

    $this->dispatch($editRequest);
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
  public function editGroupShouldOnlyAlterName()
  {
    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5s-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-add4-72ebb0878rf7-GROUP';

    $alteredGroupName = 'controller_edit_group_name_altered_only';

    $websiteRight = new \stdClass();
    $websiteRight->area = 'website';
    $websiteRight->privilege = 'publish';
    $websiteRight->ids = null;
    $expectedUnalteredRights[] = $websiteRight;

    $expectedUnalteredUsers = array(
      'USER-gbi7e2cf-11r5-45e9-bc6d-a25c4e8a7hed-USER'
    );

    $editRequest = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $groupId,
      $alteredGroupName
    );
    $this->dispatch($editRequest);
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

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $actualGroup = $response->getData();

    $expectedValuesAfterEdit = array(
      'id' => $groupId,
      'websiteId' => $websiteId,
      'name' => $alteredGroupName,
      'rights' => $expectedUnalteredRights,
      'users' => $expectedUnalteredUsers
    );

    $actualUserIds = array();

    foreach ($actualGroup as $key => $value)
    {
      if ($key === 'users')
      {
         foreach ($value as $user)
         {
           $actualUserIds[] = $user->id;
         }
         $this->assertSame($expectedUnalteredUsers, $actualUserIds);
      }
      else if ($key == 'rights')
      {
        foreach ($expectedValuesAfterEdit['rights'] as $zaehler => $expectedRight)
        {
          $this->assertArrayHasKey($zaehler, $value);

          foreach ($expectedRight as $expectedKey => $expectedValue)
          {
            $this->assertObjectHasAttribute($expectedKey, $value[$zaehler]);
            $this->assertSame($expectedValue, $value[$zaehler]->$expectedKey);
          }
        }
      }
      else
      {
        $this->assertSame($expectedValuesAfterEdit[$key], $value);
      }
    }
  }

  /**
   * Standard-User darf keine Gruppe editieren
   *
   * @test
   * @group integration
   */
  public function editShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5r-SITE';
    $groupId = 'GROUP-edl54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';

    $templateRights = new \stdClass();
    $templateRights->area = 'templates';
    $templateRights->privilege = 'none';
    $templateRights->ids = null;
    $alteredGroupRights[] = $templateRights;
    $alteredGroupName = 'controller_edit_group_name_altered';

    $request = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $alteredGroupName,
      json_encode($alteredGroupRights)
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
   * Super-User darf Gruppe editieren
   *
   * @test
   * @group integration
   */
  public function superuserEditShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5r-SITE';
    $groupId = 'GROUP-edl54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';

    $templateRights = new \stdClass();
    $templateRights->area = 'templates';
    $templateRights->privilege = 'none';
    $templateRights->ids = null;
    $alteredGroupRights[] = $templateRights;
    $alteredGroupName = 'controller_edit_group_name_altered';

    $request = sprintf(
      '/group/edit/params/{"websiteId":"%s","id":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupId,
      $alteredGroupName,
      json_encode($alteredGroupRights)
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
  public function invalidRightsProvider()
  {
    return array(
      array(array('areas' => 'modules', 'privilege' => 'none', 'ids' => null)),
      array(array('area' => 'modules', 'privileges' => 'edit', 'ids' => null)),
      array(array('area' => 'template', 'privilege' => 'all',  'ids' => null)),
      array(array('area' => 'template', 'privilege' => 'none', 'id' => null)),
      array(array('area' => 'none', 'privilege' => 'all', 'ids' => null)),
      array(array('area' => 'pages', 'privilege' => 'test', 'ids' => null)),
      array(array('area' => 'website', 'privilege' => 'push', 'ids' => null)),
      array(array('area' => 'pages', 'privilege' => 'subAll', 'ids' => null)),
      array(array('area' => 'pages', 'privilege' => 'suball', 'ids' => array('1','2'))),
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
      array('some_test_value'),
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
  }
  /**
   * @return array
   */
  public function invalidNamesProvider()
  {
    return array(
      array('a'),
      array(null),
      array(str_repeat('toolongname', 24))
    );
  }
}