<?php
namespace Application\Controller\Group;

use Orm\Data\Group as DataGroup,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class CreateTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function createGroupShouldReturnValidationErrorForInvalidIds($id)
  {
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $id,
      $groupName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodyError($responseObject);

    $this->assertSame('websiteid', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidNamesProvider
   */
  public function createGroupShouldReturnValidationErrorForInvalidNames($name)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $name
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodyError($responseObject);

    $this->assertSame('name', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider nonArrayRightsProvider
   */
  public function createGroupShouldReturnValidationErrorForNonArrayRights($rights)
  {
    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s","rights":"%s"}',
      $websiteId,
      $groupName,
      $rights
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodyError($responseObject);

    $this->assertSame('rights', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidRightsProvider
   */
  public function createGroupShouldReturnValidationErrorForInvalidRights($rights)
  {
    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';

    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s","rights":%s}',
      $websiteId,
      $groupName,
      json_encode($rights)
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodyError($responseObject);

    $this->assertSame('rights', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-425
   */
  public function createGroupWithEmptyRightsInRequestShouldReturnNoValiadtionError()
  {
    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s","rights":"[]"}',
      $websiteId,
      $groupName
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodySuccess($responseObject);

    $this->assertObjectHasAttribute('id', $responseObject->data);
    $groupId = $responseObject->data->id;

    $this->assertTrue($this->validateUniqueId(new DataGroup, $groupId));
  }

  /**
   * @test
   * @group integration
   */
  public function createGroupShouldCreateGroupAsExpected()
  {
    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $groupName
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodySuccess($responseObject);

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $this->assertObjectHasAttribute('id', $responseObject->data);
    $groupId = $responseObject->data->id;

    $this->assertTrue($this->validateUniqueId(new DataGroup, $groupId));
  }

  /**
   * @test
   * @group integration
   */
  public function createGroupShouldSetDefaultRightsAndUsersAsExpected()
  {
    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765de-SITE';
    $groupName = 'controller_test_group_default_rights_users';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $groupName
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodySuccess($responseObject);

    $this->assertObjectHasAttribute('id', $responseObject->data);
    $createdGroupId = $responseObject->data->id;

    $this->assertTrue($this->validateUniqueId(new DataGroup, $createdGroupId));

    $getByIdRequest = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $createdGroupId,
      $websiteId
    );
    $this->dispatch($getByIdRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $this->assertResponseBodySuccess($responseObject);

    $createdGroup = $responseObject->data;

    $this->assertObjectHasAttribute('users', $createdGroup);
    $this->assertInternalType('array', $createdGroup->users);
    $this->assertObjectHasAttribute('rights', $createdGroup);
    $this->assertInternalType('array', $createdGroup->rights);

    $this->assertSame(array(), $createdGroup->users);
    $this->assertSame(array(), $createdGroup->rights);
  }

  /**
   * Standard-User darf keine neuen Gruppe anlegen
   *
   * @test
   * @group integration
   */
  public function createShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $groupName
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
   * Super-User darf neue Gruppe anlegen
   *
   * @test
   * @group integration
   */
  public function superuserCreateShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-gc6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $groupName = 'controller_test_group_0';
    $request = sprintf(
      '/group/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $groupName
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
      array(array('areas' => 15, 'privilege' => 'abs', 'unit' => '-')),
      array(array('area' => 15, 'privileges' => 'abs', 'uni' => '+')),
      array(array('area' => 'templates', 'privileges' => 'subAll', 'units' => null))
    );
  }
  /**
   * @return array
   */
  public function nonArrayRightsProvider()
  {
    return array(
      array(null),
      array(15),
      array('{}'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
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