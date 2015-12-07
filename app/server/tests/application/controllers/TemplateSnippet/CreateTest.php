<?php
namespace Application\Controller\TemplateSnippet;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * TemplateSnippetController CreateTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class CreateTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'TemplateSnippetController.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function createShouldReturnValidationErrorForInvalidNames($websiteId,
    $name, $description, $category, $content, $expectedErrorParams)
  {
    $request = sprintf(
      '/templatesnippet/create/params/%s',
      \Zend_Json::encode(array(
        'name'        => $name,
        'description' => $description,
        'category'    => $category,
        'content'     => $content,
      ))
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();
    $expectedErrorCode = 3;
    $actualErrorCode  = $errors[0]->code;

    $assertionMessage = sprintf(
      "Expected error code '%d' doesn't match actual code '%d'",
      $expectedErrorCode, $actualErrorCode
    );
    $this->assertSame($expectedErrorCode, $actualErrorCode, $assertionMessage);
    
    foreach ($errors as $error)
    {
      $this->assertObjectHasAttribute('param', $error);
      $this->assertInstanceOf('stdClass', $error->param);
      $this->assertObjectHasAttribute('field', $error->param);
      $this->assertInternalType('string', $error->param->field);
      $this->assertContains($error->param->field, $expectedErrorParams, 'Parameter "' . $error->param->field . '" wird invalid zurückgegeben, obwohl er korrekt ist');
      $key = array_search($error->param->field, $expectedErrorParams);
      unset($expectedErrorParams[$key]);
    }
    $this->assertSame(0, count($expectedErrorParams), 'Parameter "' . implode(', ', $expectedErrorParams) . '" wurde nicht als Fehler zurückgegeben');
  }

  /**
   * @test
   * @group integration
   */
  public function checkRequiredParams()
  {
    $this->dispatch('/templatesnippet/create/');
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();

    $invalidParams = array();
    foreach ($errors as $error)
    {
      $this->assertSame(3, $error->code);
      $this->assertObjectHasAttribute('field', $error->param);
      $invalidParams[] = $error->param->field;
    }
    $this->assertContains('websiteid', $invalidParams);
    $this->assertContains('name', $invalidParams);
    $this->assertSame(2, count($invalidParams));
  }

  /**
   * @test
   * @group integration
   */
  public function createShouldCreateExpectedTemplateSnippet()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $this->dispatch(sprintf(
      '/templatesnippet/getall/params/{"websiteid":"%s"}',
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

    $templateSnippetCountBeforeCreate = count($responseData->templatesnippets);

    $name = 'TemplateSnippet_Create_Via_Integration_Test';

    $request = sprintf(
      '/templatesnippet/create/params/{"name":"%s","websiteid":"%s", "content":[]}',
      $name, $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());


    $newTemplateSnippet = $response->getData();
    $this->assertNotNull($newTemplateSnippet);
    $this->assertObjectHasAttribute('id', $newTemplateSnippet);
    $this->assertNotNull($newTemplateSnippet->id);

    $this->dispatch(sprintf(
      '/templatesnippet/getall/params/{"websiteid":"%s"}',
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

    $this->assertGreaterThan(
      $templateSnippetCountBeforeCreate,
      count($responseData->templatesnippets)
    );

    $newTemplateSnippetData = null;
    foreach ($responseData->templatesnippets as $templateSnippet)
    {
      $this->assertInstanceOf('stdClass', $templateSnippet);
      $this->assertObjectHasAttribute('id', $templateSnippet);
      $this->assertObjectHasAttribute('name', $templateSnippet);
      $this->assertObjectHasAttribute('content', $templateSnippet);

      if ($templateSnippet->id == $newTemplateSnippet->id)
      {
        $newTemplateSnippetData = $templateSnippet;
      }
    }
    $this->assertNotNull($newTemplateSnippetData);
    $this->assertSame($name, $newTemplateSnippetData->name);
    $this->assertSame(array(), $newTemplateSnippetData->content);
    $this->assertEquals('local', $newTemplateSnippetData->sourceType);
    $this->assertFalse($newTemplateSnippetData->readonly);
  }


  /**
   * User darf ohne Website-Zugehoerigkeit und Template-Rechte kein TemplateSnippet anlegen
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function createTemplateSnippetShouldReturnAccessDenied($username, $password)
  {
    $this->activateGroupCheck();
    
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $name = 'createTemplateSnippetAccessTest_'.__METHOD__;
    $request = sprintf(
      '/templatesnippet/create/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'websiteid' => $websiteId,
        'name'      => $name,
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
   * User darf mit Website-Zugehoerigkeit und Template-Rechten TemplateSnippets in der Website anlegen
   *
   * @test
   * @group integration
   */
  public function createTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $name = 'createTemplateSnippetAccessTest_'.__METHOD__;
    $request = sprintf(
      '/templatesnippet/create/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'websiteid' => $websiteId,
        'name'      => $name,
    )));

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    
    $responseData = $responseObject->data;
    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('id', $responseData);
  }

  /**
   * Super-User darf in allen Websites TemplateSnippetsanlegen
   *
   * @test
   * @group integration
   */
  public function superuserDeleteTemplateSnippetShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $name = 'createTemplateSnippetAccessTest_'.__METHOD__;
    $request = sprintf(
      '/templatesnippet/create/params/%s',
      \Zend_Json::encode(array(
        'runid'     => $runId,
        'websiteid' => $websiteId,
        'name'      => $name,
    )));

    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    
    $responseData = $responseObject->data;
    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('id', $responseData);
  }  
  
  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    $textLongerThat255Chars = 'sometextlongerthan255characterssometextlonger'
      . 'than255characterssometextlongerthan255characterssometextlongerthan255'
      . 'characterssometextlongerthan255characterssometextlongerthan255charact'
      . 'erssometextlongerthan255characterssometextlongerthan255characterssome'
      . 'textlongerthan255characters';

    $gueltigeUnit = array(
      'id' => '123456789',
      'name' => 'name der unit',
      'description' => 'beschreibung der unit',
      'moduleId' => '1233455',
      'formValues' => '',
      'deletable' => false,
      'readonly' => true,
      'ghostContainer' => true,
      'visibleFormGroups' => array('abc', 'def'),
      'expanded' => true,
      'children' => array()
    );
    $gueltigeUnits = array($gueltigeUnit);

    return array(
      array('123456879', null, null, null, '"abc"', array('websiteid', 'name', 'content')),
      array(null, '', null, null, null, array('websiteid', 'name')),
      array(null, $textLongerThat255Chars, $textLongerThat255Chars, $textLongerThat255Chars, '[{"id":"123456","name":"unit_name","ungueltigerKey":"abc"}]', array('websiteid', 'name','content')),
      array(null, null, 'description', 'category', $gueltigeUnits, array('websiteid', 'name')),
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