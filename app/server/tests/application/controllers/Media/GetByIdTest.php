<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * MediaController GetByIdTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetByIdTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function getByIdShouldFailOnMissingWebsiteId()
  {
    $mediaId = 'MDB-1frc4a9f-6793-471e-96bf-ge9039a0d703-MDB';
    $requestUri = sprintf(
      '/media/getbyid/params/{"id":"%s"}',
      $mediaId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);
    $this->assertSame('websiteid', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldFailOnMissingMediaId()
  {
    $websiteId = 'SITE-me10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $requestUri = sprintf(
      '/media/getbyid/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);
    $this->assertSame('id', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getByIdShouldReturnExpectedMedia()
  {
    $websiteId = 'SITE-me10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $mediaId = 'MDB-1frc4a9f-6793-471e-96bf-ge9039a0d703-MDB';
    $expectedAlbumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getbyid/params/{"websiteid":"%s","id":"%s"}',
      $websiteId,
      $mediaId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);

    $responseData = $response->getData();
    $this->assertTrue($response->getSuccess());
    $this->assertNotEmpty($responseData);

    // Response Felder pruefen
    $expectedResponseProperties = array(
      'id', 'websiteId', 'name', 'url',
      'icon', 'type', 'extension', 'filesize', 'dateUploaded',
      'albumId',
    );
    $actualResponseProperties = array_keys(get_object_vars($responseData));

    foreach ($expectedResponseProperties as $expectedResponseProperty)
    {
      $this->assertContains($expectedResponseProperty, $actualResponseProperties);
    }

    // Response Inhalte pruefen
    $this->assertObjectHasAttribute('id', $responseData);
    $this->assertSame($mediaId, $responseData->id);
    $this->assertObjectHasAttribute('websiteId', $responseData);
    $this->assertSame($websiteId, $responseData->websiteId);
    $this->assertObjectHasAttribute('albumId', $responseData);
    $this->assertSame($expectedAlbumId, $responseData->albumId);
    $this->assertObjectHasAttribute('url', $responseData);
    $this->assertNotEmpty($responseData->url);
    $this->assertObjectHasAttribute('icon', $responseData);
    $this->assertNotEmpty($responseData->icon);
  }
}