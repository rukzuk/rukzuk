<?php
namespace Application\Controller\Media;

use Cms\Validator\UniqueId as UniqueIdValidator,
    Orm\Data\Media as DataMedia,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Seitenbau\Registry as Registry;

/**
 * DeleteTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class DeleteTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function deleteMediaShouldFailOnMissingWebsiteIdAndMediaIds()
  {
    $requestUri = '/media/delete/params/';
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertNotEmpty($response->getError());
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidIdsProvider
   * @param mixed $websiteId
   * @param mixed $mediaIds
   */
  public function deleteMediaShouldReturnValidationErrorForInvalidWebsiteIdAndMediaIds(
    $websiteId, $mediaIds)
  {
    $requestUri = sprintf(
      '/media/delete/params/{"websiteid":"%s","ids":%s}',
      $websiteId,
      $mediaIds
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertNotEmpty($response->getError());
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function deleteMediaShouldReturnUndeletedRelatedMediaItems()
  {
    $websiteId = 'SITE-ra15e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $mediaId = 'MDB-sf4mo234-1a5f-b9fh-92bd-af5d4c4df4a6-MDB';
    $requestUri = sprintf(
      '/media/delete/params/{"websiteid":"%s","ids":["%s"]}',
      $websiteId,
      $mediaId
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);
    
    $this->assertNotEmpty($responseObject->error, 'Im Error-Part des Response werden die nicht zu löschenden Medien erwartet');
    $this->assertFalse($responseObject->success, 'Response-Status ist false, sobald Medien nicht gelöscht werden können');

    $this->assertSame(1, count($responseObject->error));
    $error = $responseObject->error[0];
    $this->assertSame(236, $error->code);

    // response allgemein pruefen
    $this->assertObjectHasAttribute('id', $error->param);
    $this->assertObjectHasAttribute('name', $error->param);
    $this->assertObjectHasAttribute('infos', $error->param);
    $this->assertInstanceOf('stdClass', $error->param->infos);
    $this->assertInternalType('array', $error->param->infos->templates);
    $this->assertInternalType('array', $error->param->infos->pages);
    
    // medien und page-angaben pruefen
    $this->assertSame(1, count($error->param->infos->pages));
    $page = $error->param->infos->pages[0];
    $this->assertObjectHasAttribute('id', $page);
    $this->assertObjectHasAttribute('name', $page);
    $this->assertSame($mediaId, $error->param->id);
    $this->assertSame('locked_media_modul', $error->param->name);
    $this->assertSame('Controller_Test_Media_Ref_Page', $page->name);
    
    // medien und template-angaben pruefen
    $this->assertSame(1, count($error->param->infos->templates));
    $template = $error->param->infos->templates[0];
    $this->assertObjectHasAttribute('id', $template);
    $this->assertObjectHasAttribute('name', $template);
    $this->assertSame($mediaId, $error->param->id);
    $this->assertSame('locked_media_modul', $error->param->name);
    $this->assertSame('Controller_Test_Media_Ref_Template', $template->name);
  }

  /**
   * @test
   * @group integration
   */
  public function mediaDeleteShouldDeleteAsExpected()
  {
    $websiteId = 'SITE-medel89c-22a5-46cd-a651-fc42dc78fe5f-SITE';
    $mediaIds = '["MDB-del0d0ec-cb0f-4961-92bd-765d4ag581a3-MDB",'
      . '"MDB-del1d0ec-cb0f-4961-92bd-765d4af581a3-MDB"]';

    $mediaFiles = array(
      'media-delete-0.js',
      'media-delete-0.json',
    );

    $this->assertTrue(
      $this->createTestMedias($websiteId, $mediaFiles)
    );

    $requestUri = sprintf(
      '/media/delete/params/{"websiteid":"%s","ids":%s}',
      $websiteId,
      $mediaIds
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertEmpty($response->getError());
    $this->assertTrue($response->getSuccess());
  }

  /**
   * @return arrray
   */
  public function invalidIdsProvider()
  {
    return array(
      array('', ''),
      array('murks', '["MDB-del0d0ec-cb0f-4961-92bd-765d4ag581a3-MDB", "MDB-del1d0ec-cb0f-4961-92bd-765d4af581a3-MDB"]'),
      array('SITE-medel89c-22a5-46cd-a651-fc42dc78fe5f-SITE', 'quark'),
      array('murks', 'quark')
    );
  }
}