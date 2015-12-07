<?php
namespace Application\Controller\TemplateSnippet;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * TemplateSnippetController DeleteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class DeleteTest extends ControllerTestCase
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
  public function deleteShouldReturnValidationErrorForInvalidIds($id)
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-template-snip-pet0-test-000000000002-SITE';
    $request = sprintf(
      '/templatesnippet/delete/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'ids'       => array($id),
        'websiteid' => $websiteId
    )));
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
  public function deleteShouldDeleteExpectedTemplateSnippet()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-template-snip-pet0-test-000000000002-SITE';
    $request = sprintf(
      '/templatesnippet/getall/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $this->assertGreaterThan(0, count($responseData->templatesnippets));

    $templateSnippetCountBeforeDelete = count($responseData->templatesnippets);

    $templateSnippetIdForDelete = 'TPLS-template-snip-pet0-test-deleteaction-TPLS';
    $request = sprintf(
      '/templatesnippet/delete/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'ids'       => array($templateSnippetIdForDelete),
        'websiteid' => $websiteId
    )));
    $this->dispatch($request);

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $request = sprintf(
      '/templatesnippet/getall/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $this->assertGreaterThan(0, count($responseData->templatesnippets));

    $this->assertLessThan(
      $templateSnippetCountBeforeDelete,
      count($responseData->templatesnippets)
    );

    foreach ($responseData->templatesnippets as $templateSnippet)
    {
      $this->assertInstanceOf('stdClass', $templateSnippet);
      $this->assertObjectHasAttribute('id', $templateSnippet);
      $this->assertNotSame($templateSnippetIdForDelete, $templateSnippet->id);
    }
  }

  /**
   * User darf ohne Website-Zugehoerigkeit und Template-Rechte kein TemplateSnippet loeschen
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function deleteTemplateSnippetShouldReturnAccessDenied($username, $password)
  {
    $this->activateGroupCheck();
    
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $request = sprintf(
      '/templatesnippet/delete/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'ids'       => array($templateSnippetId),
        'websiteid' => $websiteId
    )));

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
   * User darf mit Website-Zugehoerigkeit und Template-Rechten TemplateSnippets der Website loeschen
   *
   * @test
   * @group integration
   */
  public function deleteTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $request = sprintf(
      '/templatesnippet/delete/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'ids'       => array($templateSnippetId),
        'websiteid' => $websiteId
    )));

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * Super-User darf alle TemplateSnippets jeder Website loeschen
   *
   * @test
   * @group integration
   */
  public function superuserDeleteTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $templateSnippetId = 'TPLS-template-snip-pet0-test-000000000022-TPLS';
    $request = sprintf(
      '/templatesnippet/delete/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'ids'       => array($templateSnippetId),
        'websiteid' => $websiteId
    )));

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
      array('MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-MODUL'),
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