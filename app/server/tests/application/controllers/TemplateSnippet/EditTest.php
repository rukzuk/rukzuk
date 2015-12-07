<?php
namespace Application\Controller\TemplateSnippet;

use Test\Seitenbau\EditTemplateSnippetControllerTestCase as EditTemplateSnippetControllerTestCase;

/**
 * TemplateSnippetController EditTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends EditTemplateSnippetControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'TemplateSnippetController.json');

  protected $websiteId = 'SITE-template-snip-pet0-test-000000000002-SITE';
  protected $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
  protected $serviceUrl = '/templatesnippet/edit/params/%s';



  /**
   * User darf ohne Website-Zugehoerigkeit und Template-Rechte kein TemplateSnippet editieren
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function editTemplateSnippetShouldReturnAccessDenied($username, $password)
  {
    $this->activateGroupCheck();
    
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $newName = 'name_Edit';
    $request = sprintf(
      '/templatesnippet/edit/params/{"runid":"%s","websiteId":"%s","id":"%s","name":"%s"}',
      $runId, $websiteId, $templateSnippetId, $newName
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
   * User darf mit Website-Zugehoerigkeit und Template-Rechten TemplateSnippets der Website editieren
   *
   * @test
   * @group integration
   */
  public function editTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $newName = 'name_Edit';
    $request = sprintf(
      '/templatesnippet/edit/params/{"runid":"%s","websiteId":"%s","id":"%s","name":"%s"}',
      $runId, $websiteId, $templateSnippetId, $newName
    );

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * Super-User darf alle TemplateSnippets jeder Website editieren
   *
   * @test
   * @group integration
   */
  public function superuserEditTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $newName = 'name_Edit';
    $request = sprintf(
      '/templatesnippet/edit/params/{"runid":"%s","websiteId":"%s","id":"%s","name":"%s"}',
      $runId, $websiteId, $templateSnippetId, $newName
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
  
  
  /**
   * @return array
   */
  public function invalidIdsProvider()
  {
    return array(
      array('15'),
      array('some_test_value'),
      array('MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-MODUL'),
      array('TPL-4mrap53m-2al2-4g1f-a49b-4a93in3f70pd-TPL'),
    );
  }

  /**
   * @return array
   */
  public function requiredParamsProvider()
  {
    return array(
      array( array('runid', 'id', 'websiteid') )
    );
  }
  
  /**
   * @return array
   */
  public function editDataProvider()
  {
    return array(
      array(
        'params' => array(
          'id' => 'TPLS-template-snip-pet0-test-000000000011-TPLS',
          'name' => 'name_Edit',
          'description' => 'description_Edit',
          'category' => 'category_Edit',
          'content' => array(
            (object) array(
              'id' => 'MUNIT-00000000-0000-0000-0000-000000000000-MUNIT',
              'name' => 'Test-Basismodul',
              'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000000-MODUL',
            )
          )
        ),
        'expectedData' => array(
          'name' => 'name_Edit',
          'description' => 'description_Edit',
          'category' => 'category_Edit',
          'content' => array(
            (object) array(
              'id' => 'MUNIT-00000000-0000-0000-0000-000000000000-MUNIT',
              'name' => 'Test-Basismodul',
              'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000000-MODUL',
            )
          )
        )
      )
    );
  }
}