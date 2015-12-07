<?php
namespace Application\Controller\Group;

use Orm\Data\Group as DataGroup,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * CopyTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class CopyTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function copyGroupShouldReturnValidationErrorForInvalidIds($id)
  {
    $groupName = 'controller_test_group_0';
    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $id,
      $websiteId,
      $groupName
    );
    $this->dispatch($request);
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
  public function copyGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $groupName = 'controller_test_group_0';
    $id = 'GROUP-cg6e702f-10ac-4e1e-951f-307e4b8765al-GROUP';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $id,
      $websiteId,
      $groupName
    );
    $this->dispatch($request);
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
   * @dataProvider invalidNamesProvider
   */
  public function copyGroupShouldReturnValidationErrorForInvalidNames($name)
  {
    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $id = 'GROUP-cg6e702f-10ac-4e1e-951f-307e4b8765al-GROUP';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $id,
      $websiteId,
      $name
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $responseError = $responseObject->error[0];
    $this->assertSame('name', $responseError->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function copyGroupShouldReturnErrorForNonExistingGroup()
  {
    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $id = 'GROUP-cg6e702f-10no-4e1e-951f-307e4b8765al-GROUP';
    $name = 'new_name_of_group';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $id,
      $websiteId,
      $name
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $responseError = $responseObject->error[0];
    $this->objectHasAttribute('id', $responseError->param);
    $this->assertSame($id, $responseError->param->id);
  }

  /**
   * @test
   * @group integration
   */
  public function copyGroupShouldReturnErrorWhenGroupNameOfCopyAlreadyExists()
  {
    $websiteId = 'SITE-ne5e702f-10ac-4e1e-951f-307e4b8765a0-SITE';
    $sourceGroupId = 'GROUP-ne5e702f-10a0-4e1e-951f-307e4b8765a0-GROUP';
    $nameOfCopiedGroup = 'already_existing_name';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $sourceGroupId,
      $websiteId,
      $nameOfCopiedGroup
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $responseError = $responseObject->error[0];
    $this->assertSame(573, $responseError->code);
  }

  /**
   * @test
   * @group integration
   */
  public function copyGroupShouldReturnErrorWhenNewNameOfSourceAndCopyAreEqual()
  {
    $websiteId = 'SITE-ne6e702f-10ac-4e1e-951f-307e4b8765a0-SITE';
    $sourceGroupId = 'GROUP-ne6e702f-10a0-4e1e-951f-307e4b8765al-GROUP';
    $nameOfCopiedGroup = 'same_name_as_source';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $sourceGroupId,
      $websiteId,
      $nameOfCopiedGroup
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $responseError = $responseObject->error[0];
    $this->assertSame(572, $responseError->code);
  }

  /**
   * @test
   * @group integration
   */
  public function copyGroupShouldCopyGroupAsExpected()
  {
    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765a0-SITE';
    $sourceGroupId = 'GROUP-cg6e702f-10a0-4e1e-951f-307e4b8765al-GROUP';
    $nameOfCopiedGroup = 'new_name_of_group';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $sourceGroupId,
      $websiteId,
      $nameOfCopiedGroup
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $responseData = $responseObject->data;
    $this->assertObjectHasAttribute('id', $responseData);
    $idOfCopiedGroup = $responseData->id;
    $this->assertNotEmpty($idOfCopiedGroup);
    $this->assertTrue($this->validateUniqueId(new DataGroup, $idOfCopiedGroup));

    $expectedGroupAmountAfterCopy = 2;
    $getAllRequest = sprintf(
      '/group/getall/{"websiteId":"%s"}',
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
    $groupsOfWebsite = $responseData->groups;

    $this->assertSame($expectedGroupAmountAfterCopy, count($groupsOfWebsite));

    $rightsOfSourceGroup = $usersOfSourceGroup = array();
    $rightsOfCopiedGroup = $usersOfCopiedGroup = array();

    $this->assertInternalType('array', $groupsOfWebsite);
    foreach ($groupsOfWebsite as $group)
    {
      $this->assertObjectHasAttribute('id', $group);
      if ($group->id === $sourceGroupId)
      {
        $this->assertObjectHasAttribute('rights', $group);
        $rightsOfSourceGroup = $group->rights;
        $this->assertObjectHasAttribute('users', $group);
        $usersOfSourceGroup = $group->users;
      }
      elseif ($group->id === $idOfCopiedGroup)
      {
        $this->assertSame($idOfCopiedGroup, $group->id);
        $this->assertObjectHasAttribute('websiteId', $group);
        $this->assertSame($websiteId, $group->websiteId);
        $this->assertObjectHasAttribute('name', $group);
        $this->assertSame($nameOfCopiedGroup, $group->name);
        $this->assertObjectHasAttribute('rights', $group);
        $rightsOfCopiedGroup = $group->rights;
        $this->assertObjectHasAttribute('users', $group);
        $usersOfCopiedGroup = $group->users;
      }
    }

    foreach ($rightsOfSourceGroup as $rightsZaehler => $sourceRight)
    {
      $sourceRightValues = get_object_vars($sourceRight);
      $this->assertArrayHasKey($rightsZaehler, $rightsOfCopiedGroup);
      $copiedRightValues = get_object_vars($rightsOfCopiedGroup[$rightsZaehler]);

      $this->assertSame($sourceRightValues, $copiedRightValues);
    }

    foreach ($usersOfSourceGroup as $userZaehler => $sourceUser)
    {
      $sourceUserGroupValues = get_object_vars($sourceUser->groups);
      $copiedUserGroupValues = get_object_vars($usersOfCopiedGroup[$userZaehler]->groups);
      $this->assertSame($sourceUserGroupValues, $copiedUserGroupValues);

      $sourceUser->groups = array();
      $usersOfCopiedGroup[$userZaehler]->groups = array();

      $sourceUserValues = get_object_vars($sourceUser);
      $this->assertArrayHasKey($userZaehler, $usersOfCopiedGroup);
      $copiedUserValues = get_object_vars($usersOfCopiedGroup[$userZaehler]);

      $this->assertSame($sourceUserValues, $copiedUserValues);
    }
  }

  /**
   * Standard-User darf keine Gruppe kopieren
   *
   * @test
   * @group integration
   */
  public function copyShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765a0-SITE';
    $sourceGroupId = 'GROUP-cg6e702f-10a0-4e1e-951f-307e4b8765al-GROUP';
    $nameOfCopiedGroup = 'new_name_of_group';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $sourceGroupId,
      $websiteId,
      $nameOfCopiedGroup
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
   * Super-User darf Gruppe kopieren
   *
   * @test
   * @group integration
   */
  public function superuserCopyShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-cg6e702f-10ac-4e1e-951f-307e4b8765a0-SITE';
    $sourceGroupId = 'GROUP-cg6e702f-10a0-4e1e-951f-307e4b8765al-GROUP';
    $nameOfCopiedGroup = 'new_name_of_group';
    $request = sprintf(
      '/group/copy/params/{"id":"%s","websiteId":"%s","name":"%s"}',
      $sourceGroupId,
      $websiteId,
      $nameOfCopiedGroup
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
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
  }
  /**
   * @return array
   */
  public function invalidIdsProvider()
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
  public function invalidNamesProvider()
  {
    return array(
      array('a'),
      array(null),
      array(str_repeat('toolongname', 24))
    );
  }
}