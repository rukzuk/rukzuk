<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetAllTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnInvalidWebsiteId()
  {
    $invalidWebsiteId = 'PAGE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-PAGE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s"}',
      $invalidWebsiteId,
      $albumId
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
  public function getAllShouldReturnResponseErrorOnInvalidMaxIconWidth()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","maxIconHeight":100,"maxIconWidth":"abc"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);

    $this->assertSame('maxiconwidth', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnInvalidMaxIconHeight()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","maxIconHeight":"abc","maxIconWidth":100}',
      $websiteId,
      $albumId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);

    $this->assertSame('maxiconheight', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnMissingMaxIconHeight()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","maxIconWidth":100}',
      $websiteId,
      $albumId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);

    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);

    $this->assertSame('maxiconheight', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnMissingMaxIconWidth()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","maxIconHeight":100}',
      $websiteId,
      $albumId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);

    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);

    $this->assertSame('maxiconwidth', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidSearchStringsProvider
   */
  public function getAllShouldReturnResponseErrorOnTooLongOrTooShortSearchString($search)
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","search":"%s"}',
      $websiteId,
      $albumId,
      $search
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);

    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);

    $this->assertSame('search', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnInvalidAlbumId()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $invalidAlbumId = 'PAGE-ce1e6f03-gbc4-4fdb-add4-72ebb0878006-PAGE';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s"}',
      $websiteId,
      $invalidAlbumId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);
    $this->assertSame('albumid', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnResponseErrorOnInvalidTypeValue()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb0878008-ALBUM';
    $invalidType = 'INVALID_MEDIA_TYPE';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","type":"%s"}',
      $websiteId,
      $albumId,
      $invalidType
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $responseError = $response->getError();
    $this->assertNotEmpty($responseError[0]->param);
    $this->assertSame('type', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnExpectedAlbumMediaNotFilteredByType()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe5l-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb0878008-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($requestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertObjectHasAttribute('total', $responseData);
    $this->assertTrue(count(array_keys(get_object_vars($responseData))) === 2);
    $medias = $responseData->media;
    $expectedMediaCount = 3;
    $assertionMessage = sprintf(
      "Didn't get the expected media count of %d",
      $expectedMediaCount
    );
    $this->assertTrue(count($medias) === $expectedMediaCount, $assertionMessage);

    $assertionMessage = sprintf(
      "Didn't get the expected total media count of %d",
      $expectedMediaCount
    );
    $this->assertSame($expectedMediaCount, $responseData->total, $assertionMessage);

    $assertionMessageAlbumIds = "The albumIds aren't the same";
    $assertionMessageWebsiteIds = "The websiteIds aren't the same";

    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($albumId, $media->albumId, $assertionMessageAlbumIds);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId, $assertionMessageWebsiteIds);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldHaveAResizedIconUrlInResponseWhenMaxHeightAndWidthAreSet()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe52-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb0878009-ALBUM';
    $maxHeight = $maxWidth = 100;

    $requestUri = '/media/getall/params/{"websiteid":"%s","albumId":"%s",'
      . '"maxIconWidth":"%s","maxIconHeight":"%s"}';

    $requestUri = sprintf($requestUri,
      $websiteId,
      $albumId,
      $maxWidth,
      $maxHeight
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('icon', $media);
      $this->assertNotEmpty($media->icon);
      $this->assertRegExp('/"chain":"max100_100"/', urldecode($media->icon));
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldHaveAnIconUrlAndUrlInResponse()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe52-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb0878009-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s"}',
      $websiteId,
      $albumId
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('url', $media);
      $this->assertNotEmpty($media->url);
      $this->assertObjectHasAttribute('icon', $media);
      $this->assertNotEmpty($media->icon);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnOnlySearchMatchingMediasOfAlbum()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fesr-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb08780sr-ALBUM';
    $search = 'get_all_search';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","search":"%s"}',
      $websiteId,
      $albumId,
      $search
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('total', $responseData);

    $this->assertTrue(count(array_keys(get_object_vars($responseData))) === 2);

    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    $expectedMediaCount = 2;
    $assertionMessage = sprintf(
      "Didn't get the expected media count of %d",
      $expectedMediaCount
    );
    $this->assertTrue(count($medias) === $expectedMediaCount, $assertionMessage);
    $assertionMessage = sprintf(
      "Didn't get the expected total media count of %d",
      $expectedMediaCount
    );
    $this->assertSame($expectedMediaCount, $responseData->total, $assertionMessage);

    $assertionMessageAlbumIds = "The albumIds aren't the same";
    $assertionMessageWebsiteIds = "The websiteIds aren't the same";

    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($albumId, $media->albumId, $assertionMessageAlbumIds);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId, $assertionMessageWebsiteIds);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnAllSearchMatchingMedias()
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fesr-SITE';
    $search = 'get_all_search';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","search":"%s"}',
      $websiteId,
      $search
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('total', $responseData);

    $this->assertTrue(count(array_keys(get_object_vars($responseData))) === 2);

    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    $expectedMediaCount = 3;
    $assertionMessage = sprintf(
      "Didn't get the expected media count of %d",
      $expectedMediaCount
    );
    $this->assertTrue(count($medias) === $expectedMediaCount, $assertionMessage);
    $assertionMessage = sprintf(
      "Didn't get the expected total media count of %d",
      $expectedMediaCount
    );
    $this->assertSame($expectedMediaCount, $responseData->total, $assertionMessage);

    $assertionMessageWebsiteIds = "The websiteIds aren't the same";

    $actualAlbumIds = array();
    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $actualAlbumIds[] = $media->albumId;
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId, $assertionMessageWebsiteIds);
    }

    $this->assertTrue(count($actualAlbumIds) > 1);
  }

  /**
   * @test
   * @group integration
   * @dataProvider filterTypesProvider
   */
  public function getAllShouldReturnExpectedAlbumMediaFilteredByType($type)
  {
    $websiteId = 'SITE-ge10e89c-r2af-46cd-a651-fc42dc78fe52-SITE';
    $albumId = 'ALBUM-g02ecf03-moe9-4adb-ed3f-72ebb0878009-ALBUM';

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","type":"%s"}',
      $websiteId,
      $albumId,
      $type
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('total', $responseData);

    $this->assertTrue(count(array_keys(get_object_vars($responseData))) === 2);

    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    $expectedMediaCount = 2;
    $assertionMessage = sprintf(
      "Didn't get the expected media count of %d",
      $expectedMediaCount
    );
    $this->assertTrue(count($medias) === $expectedMediaCount, $assertionMessage);
    $assertionMessage = sprintf(
      "Didn't get the expected total media count of %d",
      $expectedMediaCount
    );
    $this->assertSame($expectedMediaCount, $responseData->total, $assertionMessage);

    $assertionMessageAlbumIds = "The albumIds aren't the same";
    $assertionMessageWebsiteIds = "The websiteIds aren't the same";
    $assertionMessageTypes = "The media types aren't the same";

    foreach ($medias as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($albumId, $media->albumId, $assertionMessageAlbumIds);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertSame($websiteId, $media->websiteId, $assertionMessageWebsiteIds);
      $this->assertObjectHasAttribute('type', $media);
      $this->assertSame($type, $media->type, $assertionMessageTypes);
    }
  }

  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-457
   */
  public function getAllShouldReturnLimitedAlbumMediaAndTotalMediaCount()
  {
    $websiteId = 'SITE-ga10e89c-r2af-46cd-a651-fc42dc78fe5c-SITE';
    $albumId = 'ALBUM-ga2ecf03-moe9-4adb-ed3f-72ebb087800c-ALBUM';
    $limit = $expectedMediaCount = 5;
    $expectedTotalCount = 8;

    $requestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumId":"%s","limit":%d}',
      $websiteId,
      $albumId,
      $limit
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('total', $responseData);
    $this->assertTrue(count(array_keys(get_object_vars($responseData))) === 2);

    $medias = $responseData->media;
    $this->assertInternalType('array', $medias);

    $assertionMessage = sprintf(
      "Didn't get the expected media count of %d",
      $expectedMediaCount
    );
    $this->assertTrue(count($medias) === $expectedMediaCount, $assertionMessage);
    $assertionMessage = sprintf(
      "Didn't get the expected total media count of %d",
      $expectedTotalCount
    );
    $this->assertSame($expectedTotalCount, $responseData->total, $assertionMessage);
  }

  /**
   * @return arrray
   */
  public function filterTypesProvider()
  {
    return array(
      array('misc'),
      array('download'),
      array('image'),
      array('multimedia')
    );
  }
  /**
   * @return array
   */
  public function invalidSearchStringsProvider()
  {
    return array(
      array(''),
      array(str_repeat('toolongname', 25))
    );
  }
}