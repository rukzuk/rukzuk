<?php
namespace Application\Controller\Template;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * TemplateController DeleteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class DeleteTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function deleteShouldReturnValidationErrorForInvalidIds($id)
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/template/delete/params/{"runId":"%s","id":"%s","websiteid":"%s"}',
      $runId, $id, $websiteId
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
  public function deleteShouldReturnErrorWhenTemplateIsReferencedByPage()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $byPageReferencedTemplateId = 'TPL-ejrap53m-2bf8-2g4h-a47b-4a97in3fbdel-TPL';
    $request = sprintf(
      '/template/delete/params/{"runId":"%s","id":"%s","websiteid":"%s"}',
      $runId, $byPageReferencedTemplateId, $websiteId
    );
    $this->dispatch($request);

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $error = $response->getError();
    $expectedErrorCode = 308;
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
  public function deleteShouldDeleteExpectedTemplate()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $request = sprintf(
      '/template/getall/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertInternalType('array', $responseData->templates);
    $this->assertGreaterThan(0, count($responseData->templates));

    $templateCountBeforeDelete = count($responseData->templates);

    $templateIdForDelete = 'TPL-4mrap53m-2bf9-4g1h-a49b-4a93in3fbdel-TPL';
    $request = sprintf(
      '/template/delete/params/{"runid":"%s","id":"%s","websiteid":"%s"}',
      $runId, $templateIdForDelete, $websiteId
    );
    $this->dispatch($request);

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $request = sprintf(
      '/template/getall/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertInternalType('array', $responseData->templates);
    $this->assertGreaterThan(0, count($responseData->templates));

    $this->assertLessThan(
      $templateCountBeforeDelete,
      count($responseData->templates)
    );

    foreach ($responseData->templates as $template)
    {
      $this->assertInstanceOf('stdClass', $template);
      $this->assertObjectHasAttribute('id', $template);
      $this->assertNotSame($templateIdForDelete, $template->id);
    }
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
    );
  }
}