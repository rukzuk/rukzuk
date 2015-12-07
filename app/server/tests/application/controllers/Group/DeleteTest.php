<?php
namespace Application\Controller\Group;

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
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function deleteGroupShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $groupId = 'GROUP-ca3ecf03-acc4-4fdb-add4-72ede08780al-GROUP';
    $request = sprintf(
      '/group/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $groupId
    );
    $this->dispatch($request);
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
   * @dataProvider invalidGroupIdsProvider
   */
  public function deleteGroupShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-ce6e702d-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/group/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $groupId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $this->assertSame('id', $responseObject->error[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function deleteGroupShouldDeleteAsExpected()
  {
    $groupId = 'GROUP-del54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-dg10e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $expectedGroupCountBeforeDeletion = 1;

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
    $this->assertSame($expectedGroupCountBeforeDeletion, count($responseData->groups));

    $deleteRequest = sprintf(
      '/group/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $groupId
    );
    $this->dispatch($deleteRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $this->dispatch($getAllRequest);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    $data = $responseObject->data;
    $this->assertObjectHasAttribute('groups', $data);
    $this->assertInternalType('array', $data->groups);
    $this->assertSame(0, count($data->groups));
  }

  /**
   * Standard-User darf keine Gruppe loeschen
   *
   * @test
   * @group integration
   */
  public function deleteShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-del54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-dg10e89c-22af-46cd-a651-fc42dc78fe50-SITE';

    $request = sprintf(
      '/group/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $groupId
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
   * Super-User darf Gruppe loeschen
   *
   * @test
   * @group integration
   */
  public function superuserDeleteShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $groupId = 'GROUP-del54f03-acc4-4fdb-add4-72ebb0878rf7-GROUP';
    $websiteId = 'SITE-dg10e89c-22af-46cd-a651-fc42dc78fe50-SITE';

    $request = sprintf(
      '/group/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $groupId
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
      array('ALBUM-0rap62te-0t4c-42c7-8628-f2cb4236eb45-ALBUM'),
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
      array('SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE'),
    );
  }
}