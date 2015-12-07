<?php
namespace Test\Seitenbau;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * EditTemplateSnippetControllerTestCase
 *
 * @package      Test
 * @subpackage   Controller
 */
abstract class EditTemplateSnippetControllerTestCase extends ControllerTestCase
{
  protected $websiteId = '';
  protected $runId = '';
  protected $serviceUrl = '';

  protected function setUp()
  {
    parent::setUp();

    $this->activateGroupCheck();
  }

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
  public function updateShouldReturnValidationErrorForInvalidIds($id)
  {
    $params = array(
      'id' => $id,
      'name' => 'is_egal',
    );

    $userName = 'controller.templatesnippet.update@sbcms.de';
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $request = sprintf($this->serviceUrl, json_encode($params));
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
   * @dataProvider requiredParamsProvider
   */
  public function checkRequiredParams($requiredParams)
  {
    $userName = 'controller.templatesnippet.update@sbcms.de';
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $request = sprintf($this->serviceUrl, '');
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();
    foreach ($errors as $error)
    {
      $this->assertSame(3, $error->code);
      $this->assertObjectHasAttribute('field', $error->param);
      $this->assertContains($error->param->field, $requiredParams, 
        'Parameter "' . $error->param->field . '" wird invalid zurueckgegeben, obwohl er korrekt ist');
      $key = array_search($error->param->field, $requiredParams);
      unset($requiredParams[$key]);
    }
    $this->assertSame(0, count($requiredParams), 'Parameter "' . implode(', ', $requiredParams) . '" wurde nicht als Fehler zurueckgegeben');
  }

  /**
   * @test
   * @group integration
   * @dataProvider editDataProvider
   */
  public function editShouldAlterTemplateSnippetAsExpected($editParams, $expectedData)
  {
    $editParams['runId'] = $this->runId;
    $editParams['websiteId'] = $this->websiteId;

    $userName = 'controller.templatesnippet.update@sbcms.de';
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $getAllUrl = sprintf('/templatesnippet/getall/params/{"websiteid":"%s"}', $editParams['websiteId']);
    
    $this->dispatch($getAllUrl);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $this->assertGreaterThan(0, count($responseData->templatesnippets));
    $templateSnippetCountBeforeUpdate = count($responseData->templatesnippets);

    $dataBeforeUpdate = null;
    foreach ($responseData->templatesnippets as $templateSnippet)
    {
      $this->assertInstanceOf('stdClass', $templateSnippet);
      $this->assertObjectHasAttribute('id', $templateSnippet);
      if ($templateSnippet->id === $editParams['id'])
      {
        $dataBeforeUpdate = get_object_vars($templateSnippet);
      }
    }

    $request = sprintf($this->serviceUrl, json_encode($editParams));
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $this->dispatch($getAllUrl);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $this->assertSame($templateSnippetCountBeforeUpdate, count($responseData->templatesnippets));

    $updatedTemplateSnippet = null;
    foreach ($responseData->templatesnippets as $templateSnippet)
    {
      $this->assertInstanceOf('stdClass', $templateSnippet);
      $this->assertObjectHasAttribute('id', $templateSnippet);
      if ($templateSnippet->id === $editParams['id'])
      {
        $updatedTemplateSnippet = get_object_vars($templateSnippet);
      }
    }

    foreach($updatedTemplateSnippet as $key => $value)
    {
      if (isset($expectedData[$key]))
      {
        $this->assertEquals($expectedData[$key], $value);
      }
      else
      {
        $this->assertEquals($dataBeforeUpdate[$key], $value);
      }
    }
  }

  /**
   * @return array
   */
  public function invalidIdsProvider()
  {
    return array();
  }

  /**
   * @return array
   */
  public function requiredParamsProvider()
  {
    return array();
  }

  /**
   * @return array
   */
  public function editDataProvider()
  {
    return array();
  }
}