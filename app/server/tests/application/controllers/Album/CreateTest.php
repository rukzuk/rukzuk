<?php
namespace Application\Controller\Album;

use Orm\Data\Album as DataAlbum,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class CreateTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   */
  public function createAlbumShouldReturnValidationErrorForInvalidIds($id)
  {
    $albumName = 'controller_test_album_0';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $id,
      $albumName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('websiteid', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidNamesProvider
   */
  public function createAlbumShouldReturnValidationErrorForInvalidNames($name)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $name
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('name', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function createAlbumShouldCreateAlbumAsExpected()
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $albumName = 'controller_test_album_1';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $albumName
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $albumId = $responseData->id;

    $this->assertNotNull($albumId, 'Album ID muss im Response geliefert werden');
    $this->assertTrue($this->validateUniqueId(new DataAlbum, $albumId));
  }
  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) kein neues Album
   * anlegen
   *
   * @test
   * @group integration
   */
  public function createAlbumShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumName = 'create album lock test';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
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
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) neues Album
   * anlegen
   *
   * @test
   * @group integration
   */
  public function createAlbumShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumName = 'create album lock test';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
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
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) neues Album
   * anlegen
   *
   * @test
   * @group integration
   */
  public function superuserCreateAlbumShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-42fdf881-0299-415b-b5dc-66b1d6e3228d-SITE';
    $albumName = 'create album lock test';
    $request = sprintf(
      '/album/create/params/{"websiteId":"%s","name":"%s"}',
      $websiteId,
      $albumName
    );

    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);
    
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
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
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
  /**
   * @return array
   */
  public function invalidNamesProvider()
  {
    return array(
      array('a'),
      array(null),
      array(str_repeat('toolongname', 24))
    );
  }
}