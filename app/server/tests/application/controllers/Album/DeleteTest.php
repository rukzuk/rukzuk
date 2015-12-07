<?php
namespace Application\Controller\Album;

use Orm\Data\Album as DataAlbum,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ControllerTestCase;

/**
 * DeleteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class DeleteTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function deleteAlbumShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $albumId = 'ALBUM-ca3ecf03-acc4-4fdb-add4-72ede08780al-ALBUM';
    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('websiteid', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidAlbumIdsProvider
   */
  public function deleteAlbumShouldReturnValidationErrorForInvalidAlbumIds($albumId)
  {
    $websiteId = 'SITE-ce6e702d-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('id', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function deleteAlbumShouldDeleteAsExpected()
  {
    $albumId = 'ALBUM-ce1ecf0d-acc4-4fdb-add4-72ebb0878008-ALBUM';
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307d4b8760al-SITE';

    $getAllRequest = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertTrue(count($responseData->albums) === 1);

    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertSame(0, count($responseData->albums));
  }

  /**
   * @test
   * @group integration
   */
  public function deleteAlbumShouldAlsoDeleteIncludedMedia()
  {
    $albumId = 'ALBUM-ce1ecf0d-acc4-4fdb-dem4-72ebb0878008-ALBUM';
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-dem7-307d4b8760al-SITE';

    $expectedAlbumCountBeforeDelete = 2;
    $expectedAlbumCountAfterDelete = 1;

    $mediaFiles = array(
      'samy-v1.js',
      'backbone.js',
      'samy-v2.js',
      'samy-v3.js',
      'samy-v4.js',
    );

    $this->assertTrue(
      $this->createTestMedias($websiteId, $mediaFiles)
    );

    $getAllRequest = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertSame($expectedAlbumCountBeforeDelete, count($responseData->albums));

    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertSame($expectedAlbumCountAfterDelete, count($responseData->albums));

    $config = Registry::getConfig();
    $testFilesDirectory = $config->test->files->directory;
    $testFilesMediaDirectory = $config->media->files->directory;
    $testMediaDirectory = $testFilesDirectory
      . DIRECTORY_SEPARATOR . $websiteId;

    $expectedAfterDeleteMediasTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'trees'
      . DIRECTORY_SEPARATOR . 'album_delete.tree';
    $expectedAfterDeleteMediasTree = file_get_contents($expectedAfterDeleteMediasTreeFile);

    $testMediaDirectory = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . $websiteId;

    $assertionMessage = "Tree mismatch between medias directory tree and "
      . "expected medias directory tree after album deletion";

    $this->assertSame(
      $expectedAfterDeleteMediasTree,
      \Test\Seitenbau\Directory\Helper::getRecursiveAsJson($testMediaDirectory, true),
      $assertionMessage
    );

    DirectoryHelper::removeRecursiv($testMediaDirectory, $testFilesMediaDirectory);
  }

  /**
   * @test
   * @group annotation
   * @dataProvider nonDeletableAlbumIdsProvider
   * @param string $albumId
   * @param string $relatedToTypeResponseKey Response type key the Media item
   * is related to 'pages|templates'
   */
  public function deleteAlbumShouldBeRejectedWhenItContainsRelatedMediaItems(
    $albumId, $relatedToTypeResponseKey)
  {
    $expectedAlbumCountBeforeDelete = $expectedAlbumCountAfterDelete = 3;
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-dem7-307d4b8760rm-SITE';
    $getAllRequest = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertSame($expectedAlbumCountBeforeDelete, count($responseData->albums));

    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    $this->assertSame(1, count($responseObject->error));
    $error = $responseObject->error[0];

    $this->assertObjectHasAttribute($relatedToTypeResponseKey, $error->param->infos);
    $this->assertInternalType('array', $error->param->infos->$relatedToTypeResponseKey);
    $this->assertSame(1, count($error->param->infos->$relatedToTypeResponseKey));

    $this->dispatch($getAllRequest);
    $response = new Response($this->getResponseBody());
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $this->assertSame($expectedAlbumCountBeforeDelete, count($responseData->albums));
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) nicht alle Alben
   * auslesen
   *
   * @test
   * @group integration
   */
  public function deleteAlbumShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumId = 'ALBUM-45ee0958-f040-447f-9be0-4ebacc2ba770-ALBUM';
    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
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
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) alle Alben
   * auslesen
   *
   * @test
   * @group integration
   */
  public function deleteAlbumShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumId = 'ALBUM-45ee0958-f040-447f-9be0-4ebacc2ba770-ALBUM';
    $request = sprintf(
      '/album/delete/params/{"websiteId":"%s","id":"%s"}',
      $websiteId,
      $albumId
    );

    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * @return array
   */
  public function nonDeletableAlbumIdsProvider()
  {
    return array(
      array('ALBUM-ce1ecf0d-acc4-4fdb-dem4-72ebb08780rm-ALBUM', 'pages'),
      array('ALBUM-ce2ecf0d-acc4-4fdb-dem4-72ebb08780rm-ALBUM', 'templates'),
    );
  }

  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }

  /**
   * @return array
   */
  public function invalidAlbumIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE'),
    );
  }
}