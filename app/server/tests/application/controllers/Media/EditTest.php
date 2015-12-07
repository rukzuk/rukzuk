<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * EditTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function editMediaShouldReturnValidationErrorForMissingWebsiteIdAndMediaId()
  {
    $requestUri = '/media/edit/params/';
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }
    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('id', $errorFields);
    $this->assertContains('websiteid', $errorFields);
    $this->assertNotContains('name', $errorFields);
    $this->assertNotContains('albumid', $errorFields);
  }

  /**
   * @test
   * @group integration
   * @param string $invalidName
   * @dataProvider invalidNamesProvider
   */
  public function editMediaShouldReturnValidationErrorForInvalidNames($invalidName)
  {
    $mediaId = 'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB';
    $websiteId = 'SITE-co6e702g-10ac-4e1e-951f-307e4b8760ed-SITE';
    $requestUri = sprintf(
      '/media/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $mediaId,
      $invalidName
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }

    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('name', $errorFields);
  }

  /**
   * @test
   * @group integration
   * @param string $invalidAlbumId
   * @dataProvider invalidAlbumIdsProvider
   */
  public function editMediaShouldReturnValidationErrorForInvalidAlbumId($invalidAlbumId)
  {
    $mediaId = 'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB';
    $websiteId = 'SITE-co6e702g-10ac-4e1e-951f-307e4b8760ed-SITE';
    $name = 'media_edit_test_name';
    $requestUri = sprintf(
      '/media/edit/params/{"websiteId":"%s","id":"%s","name":"%s","albumid":"%s"}',
      $websiteId,
      $mediaId,
      $name,
      $invalidAlbumId
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $errorFields = array();
    foreach ($errorData as $error)
    {
      $errorFields[] = $error->param->field;
    }

    $this->assertNotEmpty($errorData);
    $this->assertFalse($response->getSuccess());
    $this->assertContains('albumid', $errorFields);
  }

  /**
   * @test
   * @group integration
   */
  public function editMediaShouldAlterNameAsExpected()
  {
    $mediaId = 'MDB-co91d0ec-cb0f-4961-92bd-765d4aa581a3-MDB';
    $websiteId = 'SITE-ra10e89c-22ed-46cd-a651-fc42dc78fe50-SITE';
    $albumId = 'ALBUM-om1ecf03-acc4-4fdb-add4-72ebb0878008-ALBUM';
    $alteredMediaName = 'media-item_edit_0_altered';

    $requestUri = sprintf(
      '/media/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $mediaId,
      $alteredMediaName
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertEmpty($response->getError());
    $this->assertTrue($response->getSuccess());

    $getAllRequestUri = sprintf(
      '/media/get/params/{"websiteId":"%s","albumid":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($getAllRequestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertTrue(count($responseData->media) === 1);

    $allMediaForWebsiteAndAlbumId = $responseData->media;
    foreach ($allMediaForWebsiteAndAlbumId as $media) 
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId);
      $this->assertObjectHasAttribute('id', $media);
      $this->assertSame($mediaId, $media->id);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($albumId, $media->albumId);
      $this->assertObjectHasAttribute('name', $media);
      $this->assertSame($alteredMediaName, $media->name);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function editMediaShouldAlterAlbumIdAsExpected()
  {
    $mediaId = 'MDB-co91d0ec-cb0f-4961-92bd-765d4aa581a4-MDB';
    $websiteId = 'SITE-ra10e89c-22ed-46cd-a651-fc42dc78fe51-SITE';
    $alteredAlbumId = 'ALBUM-ce1ecf03-acc4-4adb-ed34-72ebb0878alt-ALBUM';

    $requestUri = sprintf(
      '/media/edit/params/{"websiteId":"%s","id":"%s","albumId":"%s"}',
      $websiteId,
      $mediaId,
      $alteredAlbumId
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertEmpty($response->getError());
    $this->assertTrue($response->getSuccess());

    $getAllRequestUri = sprintf(
      '/media/get/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($this->getResponseBody());
    $this->dispatch($getAllRequestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertTrue(count($responseData->media) === 1);

    $allMediaForWebsiteAndAlbumId = $responseData->media;
    foreach ($allMediaForWebsiteAndAlbumId as $media) 
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId);
      $this->assertObjectHasAttribute('id', $media);
      $this->assertSame($mediaId, $media->id);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($alteredAlbumId, $media->albumId);
    }
  }

  /**
   * @return array
   */
  public function invalidNamesProvider()
  {
    $tooLongString = str_repeat('toolongname', 24);
    return array(
      array('a'),
      array(null),
      array($tooLongString)
    );
  }
  /**
   * @return array
   */
  public function invalidAlbumIdsProvider()
  {
    return array(
      array(null),
      array(154),
      array('some_invalid_value'),
      array('SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE'),
    );
  }
}