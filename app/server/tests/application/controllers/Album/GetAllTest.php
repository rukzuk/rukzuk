<?php
namespace Application\Controller\Album;

use Orm\Data\Album as DataAlbum,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetAllTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function editAlbumShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
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
   */
  public function getAllAlbumsShouldReturnExpectedAlbums()
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8760al-SITE';
    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $allAlbumsForWebsiteId = $responseData->albums;
    $this->assertInternalType('array', $allAlbumsForWebsiteId);
    $this->assertSame(3, count($allAlbumsForWebsiteId));
    foreach ($allAlbumsForWebsiteId as $albumsOfWebsiteId)
    {
      $this->assertInstanceOf('stdClass', $albumsOfWebsiteId);
      $this->assertObjectHasAttribute('websiteId', $albumsOfWebsiteId);
      $this->assertSame($websiteId, $albumsOfWebsiteId->websiteId);
      $this->assertObjectHasAttribute('id', $albumsOfWebsiteId);
      $this->assertTrue(
        $this->validateUniqueId(new DataAlbum, $albumsOfWebsiteId->id)
      );
      $this->assertObjectHasAttribute('name', $albumsOfWebsiteId);
      $this->assertNotEmpty($albumsOfWebsiteId->name);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllAlbumsShouldReturnExpectedAlbumsSortedByName()
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8760as-SITE';
    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $allAlbumsForWebsiteId = $responseData->albums;
    $this->assertInternalType('array', $allAlbumsForWebsiteId);

    $expectedAlbumNameOrder = array(
      'a_controller_test_album',
      'b_controller_test_album',
      'z_controller_test_album'
    );

    $this->assertSame(3, count($allAlbumsForWebsiteId));
    $actualAlbumNameOrder = array();
    foreach ($allAlbumsForWebsiteId as $albumsOfWebsiteId)
    {
      $this->assertInstanceOf('stdClass', $albumsOfWebsiteId);
      $this->objectHasAttribute('websiteId', $albumsOfWebsiteId);
      $this->assertSame($websiteId, $albumsOfWebsiteId->websiteId);
      $this->objectHasAttribute('id', $albumsOfWebsiteId);
      $this->assertTrue(
        $this->validateUniqueId(new DataAlbum, $albumsOfWebsiteId->id)
      );
      $this->objectHasAttribute('name', $albumsOfWebsiteId);
      $this->assertNotEmpty($albumsOfWebsiteId->name);
      $actualAlbumNameOrder[] = $albumsOfWebsiteId->name;
    }
    $assertionMessage = sprintf(
      "Actual album name order [%s] doesn't match expected album name order [%s]",
      implode(",", $actualAlbumNameOrder),
      implode(",", $expectedAlbumNameOrder)
    );
    $this->assertSame(
      $expectedAlbumNameOrder,
      $actualAlbumNameOrder,
      $assertionMessage
    );
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) nicht alle Alben
   * auslesen
   *
   * @test
   * @group integration
   */
  public function getAllAlbumsShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
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
  public function getAllAlbumsShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
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
}