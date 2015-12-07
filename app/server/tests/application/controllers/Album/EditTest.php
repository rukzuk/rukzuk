<?php
namespace Application\Controller\Album;

use Orm\Data\Album as DataAlbum,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * EditTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function editAlbumShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $albumId = 'ALBUM-ca3ecf03-acc4-4fdb-add4-72ebb08780al-ALBUM';
    $albumName = 'controller_test_album_2';
    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $albumName
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
  public function editAlbumShouldReturnValidationErrorForInvalidAlbumIds($albumId)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $albumName = 'controller_test_album_3';
    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $albumName
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
   * @dataProvider invalidNamesProvider
   */
  public function editAlbumShouldReturnValidationErrorForInvalidNames($albumName)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $albumId = 'ALBUM-ca3ecf03-acc4-4fdb-add4-72ebb08780al-ALBUM';
    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $albumName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errorData = $response->getError();
    $this->assertSame('name', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function editAlbumShouldAlterAlbumAsExpected()
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $albumId = 'ALBUM-ce1ecf03-acc4-4fdb-add4-72ebb0878007-ALBUM';
    $alteredAlbumName = 'controller_test_album_4_altered';

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
    $this->assertSame(1, count($responseData->albums));

    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $alteredAlbumName
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
    $this->assertSame(1, count($responseData->albums));

    $allAlbumsForWebsiteId = $responseData->albums;
    $this->assertInternalType('array', $allAlbumsForWebsiteId);
    foreach ($allAlbumsForWebsiteId as $albumsOfWebsiteId)
    {
      $this->assertInstanceOf('stdClass', $albumsOfWebsiteId);
      $this->assertObjectHasAttribute('websiteId', $albumsOfWebsiteId);
      $this->assertSame($websiteId, $albumsOfWebsiteId->websiteId);
      $this->assertTrue(
        $this->validateUniqueId(new DataAlbum, $albumsOfWebsiteId->id)
      );
      $this->assertObjectHasAttribute('name', $albumsOfWebsiteId);
      $this->assertSame($alteredAlbumName, $albumsOfWebsiteId->name);
    }
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) kein Album
   * editieren
   *
   * @test
   * @group integration
   */
  public function editAlbumShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumId = 'ALBUM-45ee0958-f040-447f-9be0-4ebacc2ba770-ALBUM';
    $albumName = 'other album name';
    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $albumName
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
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) Album editieren
   *
   * @test
   * @group integration
   */
  public function editAlbumShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumId = 'ALBUM-45ee0958-f040-447f-9be0-4ebacc2ba770-ALBUM';
    $albumName = 'other album name';
    $request = sprintf(
      '/album/edit/params/{"websiteId":"%s","id":"%s","name":"%s"}',
      $websiteId,
      $albumId,
      $albumName
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
}