<?php
namespace Application\Controller\TemplateSnippet;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateSnippetController GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetAllTest extends ControllerTestCase
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
   */
  public function getAllShouldReturnAtLeastOneSnippet()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000002-SITE';
    $this->dispatch(sprintf(
      '/templatesnippet/getAll/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $this->assertGreaterThan(0, count($responseData->templatesnippets));
  }

  /**
   * @test
   * @group integration
   */
  public function getAllMustHaveParamWebsiteId()
  {
    // Pflichtparameter
    $params = array('websiteid' => '');

    $this->dispatch('/templatesnippet/getAll');
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    foreach ($responseObject->error as $error)
    {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }

  /**
   * User darf ohne Website-Zugehoerigkeit und Template-Rechte keine TemplateSnippets auslesen
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function getAllTemplateSnippetsShouldReturnAccessDenied($username, $password)
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/templatesnippet/getall/params/{"websiteId":"%s"}',
      $websiteId
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
   * User darf mit Website-Zugehoerigkeit TemplateSnippets der Website auslesen
   *
   * @test
   * @group integration
   */
  public function getAllTemplateSnippetsShouldReturnExpectedTemplatesSnippets()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/templatesnippet/getall/params/{"websiteId":"%s"}',
      $websiteId
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
  public function superuserGetAllTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/templatesnippet/getall/params/{"websiteId":"%s"}',
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
  public function accessDeniedUser()
  {
    return array(
      array('access_rights_1@sbcms.de', 'seitenbau'),
      array('access_rights_3@sbcms.de', 'seitenbau'),
    );
  }
}