<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * BatchMoveTest
 *
 * @package      $package
 * @subpackage   $subpackage
 */
class BatchMoveTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   * @param mixed  $websiteId
   */
  public function batchmoveMediaShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $albumId = 'ALBUM-ce1ecf03-acc4-4adb-add4-72ebb08780bm-ALBUM';
    $mediaIds = array(
      'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB',
      'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB'
    );

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":%s,"websiteId":"%s","albumid":"%s"}',
      json_encode($mediaIds),
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $expectedErrorCount = 1;
    $expectedErrorParamField = 'websiteid';

    $this->assertSame($expectedErrorCount, count($errorData));
    $this->assertSame($expectedErrorParamField, $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider nonArrayMediaIdsProvider
   * @param mixed  $mediaIds
   */
  public function batchmoveMediaShouldReturnValidationErrorForNonArrayMediaIds(
    $mediaIds)
  {
    $albumId = 'ALBUM-ce1ecf03-acc4-4adb-add4-72ebb08780bm-ALBUM';
    $websiteId = 'SITE-ce1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":"[%s]","websiteId":"%s","albumid":"%s"}',
      $mediaIds,
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();

    $expectedErrorCount = 1;
    $expectedErrorParamField = 'ids';

    $this->assertSame($expectedErrorCount, count($errorData));
    $this->assertSame($expectedErrorParamField, $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidMediaIdsProvider
   * @param mixed  $mediaIds
   */
  public function batchmoveMediaShouldReturnValidationErrorForNonInvalidMediaIds(
    $mediaIds)
  {
    $albumId = 'ALBUM-ce1ecf03-acc4-4adb-add4-72ebb08780bm-ALBUM';
    $websiteId = 'SITE-ce1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":[%s],"websiteId":"%s","albumid":"%s"}',
      implode(',', $mediaIds),
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();

    $expectedErrorCount = 2;
    $expectedErrorParamField = 'id';

    $this->assertSame($expectedErrorCount, count($errorData));
    $this->assertSame($expectedErrorParamField, $errorData[0]->param->field);
    $this->assertSame($expectedErrorParamField, $errorData[1]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidAlbumIdsProvider
   * @param mixed  $albumId
   */
  public function batchmoveMediaShouldReturnValidationErrorForInvalidAlbumIds($albumId)
  {
    $websiteId = 'SITE-ce1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';
    $mediaIds = array(
      'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB',
      'MDB-co74a7be-4c29-405b-86eb-46bef914ba9e-MDB'
    );

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":%s,"websiteId":"%s","albumid":"%s"}',
      json_encode($mediaIds),
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $errorData = $response->getError();

    $expectedErrorCount = 1;
    $expectedErrorParamField = 'albumid';

    $this->assertSame($expectedErrorCount, count($errorData));
    $this->assertSame($expectedErrorParamField, $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function batchmoveMediaShouldReturnValidationForNonSetMediaIds()
  {
    $albumId = 'ALBUM-ce1ecf03-acc4-4adb-add4-72ebb08780bm-ALBUM';
    $websiteId = 'SITE-ce1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"websiteId":"%s","albumid":"%s"}',
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();

    $expectedErrorCount = 1;
    $expectedErrorParamField = 'ids';

    $this->assertSame($expectedErrorCount, count($errorData));
    $this->assertSame($expectedErrorParamField, $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function batchmoveMediaOnNonExistingMediaShouldReturnResponseError()
  {
    $albumId = 'ALBUM-no1ecf03-adc4-4aeb-ahd4-72err08780bm-ALBUM';
    $websiteId = 'SITE-te1ecf03-acc4-4adb-add4-72err08780bm-SITE';
    $mediaIds = array(
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581b0-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581b1-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581b2-MDB',
    );
    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":%s,"websiteId":"%s","albumid":"%s"}',
      json_encode($mediaIds),
      $websiteId,
      $albumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function batchmoveMediaToNonExistingAlbumShouldReturnResponseError()
  {
    $nonExistingAlbumId = 'ALBUM-no1ecf03-adc4-4aeb-ahd4-72ebb08780bm-ALBUM';
    $websiteId = 'SITE-te1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';
    $mediaIds = array(
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a0-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a1-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a2-MDB',
    );

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":%s,"websiteId":"%s","albumid":"%s"}',
      json_encode($mediaIds),
      $websiteId,
      $nonExistingAlbumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function batchmoveMediaShouldMoveMediasAsExpected()
  {
    $expectedSourceMediaCountBeforeMove = 4;
    $expectedSourceMediaCountAfterMove = 1;
    $expectedDestinationMediaCountAfterMove = 3;

    $sourceAlbumId = 'ALBUM-du0ecf0d-acc4-4fdb-dem4-72ebb08780bm-ALBUM';
    $destinationAlbumId = 'ALBUM-te1ecf03-acc4-4adb-add4-72ebb08780bm-ALBUM';
    $websiteId = 'SITE-te1ecf03-acc4-4adb-add4-72ebb08780bm-SITE';
    $mediaIds = array(
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a0-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a1-MDB',
      'MDB-bm5fg6ec-ai0f-4961-92bd-765d4aa581a2-MDB',
    );

    $getAllRequestSource = sprintf(
      '/media/getall/params/{"websiteId":"%s","albumid":"%s"}',
      $websiteId,
      $sourceAlbumId
    );

    $this->dispatch($getAllRequestSource);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $actualSourceMedias = $responseData->media;
    $this->assertInternalType('array', $actualSourceMedias);

    $this->assertSame($expectedSourceMediaCountBeforeMove, count($actualSourceMedias));

    $batchMoveRequestUri = sprintf(
      '/media/batchmove/params/{"ids":%s,"websiteId":"%s","albumid":"%s"}',
      json_encode($mediaIds),
      $websiteId,
      $destinationAlbumId
    );

    $this->dispatch($batchMoveRequestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $getAllRequestDestination = sprintf(
      '/media/getall/params/{"websiteId":"%s","albumid":"%s"}',
      $websiteId,
      $destinationAlbumId
    );

    $this->dispatch($getAllRequestDestination);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $actualDestinationMedias = $responseData->media;
    $this->assertInternalType('array', $actualDestinationMedias);

    $this->assertSame($expectedDestinationMediaCountAfterMove, count($actualDestinationMedias));

    foreach ($actualDestinationMedias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($destinationAlbumId, $media->albumId);
      $this->assertObjectHasAttribute('id', $media);
      $this->assertContains($media->id, $mediaIds);
    }

    $getAllRequestSource = sprintf(
      '/media/getall/params/{"websiteId":"%s","albumid":"%s"}',
      $websiteId,
      $sourceAlbumId
    );

    $this->dispatch($getAllRequestSource);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $actualSourceMediasAfterMove = $responseData->media;
    $this->assertInternalType('array', $actualSourceMediasAfterMove);

    $this->assertSame($expectedSourceMediaCountAfterMove, count($actualSourceMediasAfterMove));
  }

  /**
   * @return array
   */
  public function invalidMediaIdsProvider()
  {
    return array(
      array(array('"ab"','"cd"')),
      array(array('"1"','"2"')),
      array(array('"MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL", "MODUL-0bin62pr-0t5f-28c9-eg28-f2cb4136ef45-MODUL"'))
    );
  }
  /**
   * @return array
   */
  public function nonArrayMediaIdsProvider()
  {
    return array(
      array('[]'),
      array('quark'),
      array(null),
      array(15)
    );
  }
  /**
   * @return array
   */
  public function invalidAlbumIdsProvider()
  {
    return array(
      array(null),
      array(16),
      array('some_invalid_album_id'),
      array('SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE'),
    );
  }
  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(154),
      array('some_invalid_value'),
      array('GROUP-ce6e702f-10ac-4e1e-951f-307e4b8765al-GROUP'),
    );
  }
}