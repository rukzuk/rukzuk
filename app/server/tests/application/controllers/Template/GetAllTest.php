<?php
namespace Application\Controller\Template;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateController GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetAllTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnAtLeastOneTemplate()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $this->dispatch(sprintf(
      '/template/getAll/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertInternalType('array', $responseData->templates);
    $this->assertGreaterThan(0, count($responseData->templates));
  }

  /**
   * @test
   * @group integration
   */
  public function getAllMustHaveParamWebsiteId()
  {
    // Pflichtparameter
    $params = array('websiteid' => '');

    $this->dispatch('/template/getAll');
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
   * User darf ohne Website-Zugehoerigkeit keine Alben auslesen
   *
   * @test
   * @group integration
   */
  public function getAllTemplatesShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/template/getall/params/{"websiteId":"%s"}',
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
   * User darf mit Website-Zugehoerigkeit Templates der Website auslesen
   *
   * @test
   * @group integration
   */
  public function getAllTemplatesShouldReturnExpectedTemplates()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/template/getall/params/{"websiteId":"%s"}',
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
   * Super-User darf alle Templates jeder Website auslesen
   *
   * @test
   * @group integration
   */
  public function superuserGetAllTemplateShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/template/getall/params/{"websiteId":"%s"}',
      $websiteId
    );

    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }
}