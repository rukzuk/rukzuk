<?php
namespace Application\Controller\Template;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateController GetByIdTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetByIdTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function getByIdShouldReturnValidationErrorForInvalidIds($id)
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/template/getbyid/params/{"id":"%s","websiteid":"%s"}',
      $id, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $error = $response->getError();
    $expectedErrorCode = 3;
    $actualErrorCode  = $error[0]->code;

    $assertionMessage = sprintf(
      "Expected error code '%d' doesn't match actual code '%d'",
      $expectedErrorCode, $actualErrorCode
    );
    $this->assertSame($expectedErrorCode, $actualErrorCode, $assertionMessage);
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldReturnErrorForNonExistingId()
  {
    $nonExistingId = 'TPL-rap492no-c6ex-4b33-b52c-git749e54rap-TPL';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/template/getbyid/params/{"id":"%s","websiteid":"%s"}',
      $nonExistingId, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $error = $response->getError();
    $expectedErrorCode = 302;
    $actualErrorCode  = $error[0]->code;

    $assertionMessage = sprintf(
      "Expected error code '%d' doesn't match actual code '%d'",
      $expectedErrorCode, $actualErrorCode
    );
    $this->assertSame($expectedErrorCode, $actualErrorCode, $assertionMessage);
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldReturnExpectedTemplate()
  {
    $existingId = 'TPL-in6c753f-2rap-474f-a4et-4a97223fbdea-TPL';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/template/getbyid/params/{"id":"%s","websiteid":"%s"}',
      $existingId, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualTemplate = $response->getData();

    $this->assertInstanceOf('stdClass', $actualTemplate);
    $this->assertObjectHasAttribute('id', $actualTemplate);
    $this->assertSame($existingId, $actualTemplate->id);
    $this->assertObjectHasAttribute('websiteId', $actualTemplate);
    $this->assertSame($websiteId, $actualTemplate->websiteId);
    $this->assertObjectHasAttribute('name', $actualTemplate);
    $this->assertSame('Template_Name_Int_GetById', $actualTemplate->name);
    $this->assertObjectHasAttribute('content', $actualTemplate);
    $this->assertSame('[{"some":"value"}]', \Zend_Json::encode($actualTemplate->content));
  }

  /**
   * User darf ohne Website-Zugehoerigkeit keine Alben auslesen
   *
   * @test
   * @group integration
   */
  public function getByIdTemplatesShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateId = 'TPL-ff3a8203-e884-4e19-8c6c-f588c9dea01f-TPL';
    $request = sprintf(
      '/template/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateId
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
   * User darf mit Website-Zugehoerigkeit Templates der Website auslesen
   *
   * @test
   * @group integration
   */
  public function getByIdTemplatesShouldReturnExpectedTemplates()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateId = 'TPL-ff3a8203-e884-4e19-8c6c-f588c9dea01f-TPL';
    $request = sprintf(
      '/template/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateId
    );

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * Super-User darf alle Templates jeder Website auslesen
   *
   * @test
   * @group integration
   */
  public function superuserGetByIdTemplateShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateId = 'TPL-ff3a8203-e884-4e19-8c6c-f588c9dea01f-TPL';
    $request = sprintf(
      '/template/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateId
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
  public function invalidIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
    );
  }
}