<?php
namespace Application\Controller\TemplateSnippet;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateSnippetController GetByIdTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetByIdTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'TemplateSnippetController.json');

  protected function tearDown()
  {
    $this->deactivateGroupCheck();

    parent::tearDown();
  }
  
  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function getByIdShouldReturnValidationErrorForInvalidIds($id)
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"id":"%s","websiteid":"%s"}',
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
    $nonExistingId = 'TPLS-template-snip-pet0-not0-existing0001-TPLS';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"id":"%s","websiteid":"%s"}',
      $nonExistingId, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $error = $response->getError();
    $expectedErrorCode = 1602;
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
  public function getByIdShouldReturnExpectedTemplateSnippet()
  {
    $existingId = 'TPLS-template-snip-pet0-test-000000000001-TPLS';
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"id":"%s","websiteid":"%s"}',
      $existingId, $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $actualTemplateSnippet = $response->getData();

    $this->assertInstanceOf('stdClass', $actualTemplateSnippet);
    $this->assertObjectHasAttribute('id', $actualTemplateSnippet);
    $this->assertSame($existingId, $actualTemplateSnippet->id);
    $this->assertObjectHasAttribute('websiteId', $actualTemplateSnippet);
    $this->assertSame($websiteId, $actualTemplateSnippet->websiteId);
    $this->assertObjectHasAttribute('name', $actualTemplateSnippet);
    $this->assertSame('TEMPLATE_SNIPPET_NAME_1', $actualTemplateSnippet->name);
    $this->assertObjectHasAttribute('description', $actualTemplateSnippet);
    $this->assertSame('TEMPLATE_SNIPPET_DESCRIPTION_1', $actualTemplateSnippet->description);
    $this->assertObjectHasAttribute('category', $actualTemplateSnippet);
    $this->assertSame('TEMPLATE_SNIPPET_CATEGORY_1', $actualTemplateSnippet->category);
    $this->assertObjectHasAttribute('baseLayout', $actualTemplateSnippet);
    $this->assertFalse($actualTemplateSnippet->baseLayout);
    $this->assertObjectHasAttribute('pageTypes', $actualTemplateSnippet);
    $this->assertInternalType('array', $actualTemplateSnippet->pageTypes);
    $this->assertCount(0, $actualTemplateSnippet->pageTypes);
    $this->assertObjectHasAttribute('content', $actualTemplateSnippet);
    $this->assertSame('[]', \Seitenbau\Json::encode($actualTemplateSnippet->content));
    $this->assertEquals('local', $actualTemplateSnippet->sourceType);
    $this->assertFalse($actualTemplateSnippet->readonly);

  }

  /**
   * User darf ohne Website-Zugehoerigkeit und etmplate-Rechte kein TemplateSnippet auslesen
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function getByIdTemplateSnippetShouldReturnAccessDenied($username, $password)
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000021-TPLS';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateSnippetId
    );

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin($username, $password);

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf mit Website-Zugehoerigkeit und Template-Rechten TemplateSnippets der Website auslesen
   *
   * @test
   * @group integration
   */
  public function getByIdTemplateSnippetShouldReturnExpectedTemplateSnippet()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000021-TPLS';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateSnippetId
    );

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * Super-User darf alle TemplateSnippets jeder Website auslesen
   *
   * @test
   * @group integration
   */
  public function superuserGetByIdTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000021-TPLS';
    $request = sprintf(
      '/templatesnippet/getbyid/params/{"websiteId":"%s","id":"%s"}',
      $websiteId, $templateSnippetId
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
      array('TPL-00000000-0000-0000-0000-000000000001-TPL'),
    );
  }
  
  /**
   * @return array
   */
  public function accessDeniedUser()
  {
    return array(
      array('access_rights_1@sbcms.de', 'seitenbau'),
      array('access_rights_3@sbcms.de', 'seitenbau'),
    );
  }
}