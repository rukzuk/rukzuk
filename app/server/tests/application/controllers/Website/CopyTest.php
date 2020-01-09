<?php
namespace Application\Controller\Website;

use Cms\Dao\Base\AbstractSourceItem;
use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Orm\Data\Site as DataWebsite,
    Seitenbau\Registry,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;

/**
 * WebsiteController Copy Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class CopyTest extends ControllerTestCase
{

  public $sqlFixtures = array('WebsiteController.json');

  protected function setUp()
  {
    parent::setUp();

    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Modul', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });
  }

  public function tearDown()
  {
    ModuleWriteableMock::tearDown();
    
    parent::tearDown();
  }
  
  /**
   * @test
   * @group integration
   */
  public function copyCopiesWithNoAssociatedAlbums()
  {
    $sourceWebsiteId = 'SITE-1964e89c-0002-cows-a651-fc42dc78fe50-SITE';

    $copyRequest = sprintf(
      'website/copy/params/{"id":"%s","name":"%s"}',
      $sourceWebsiteId,
      'copied_website_with_copied_albums'
    );

    $this->dispatch($copyRequest);

    $responseBody = $this->getResponseBody();

    $response = new Response($responseBody);

    $this->assertTrue($response->getSuccess(), $responseBody);

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $copiedWebsiteId = $responseData->id;

    $uniqueidValidator = new UniqueIdValidator(
      DataWebsite::ID_PREFIX,
      DataWebsite::ID_SUFFIX
    );

    $this->assertTrue($uniqueidValidator->isValid($copiedWebsiteId));
  }

  /**
   * @test
   * @group integration
   */
  public function copyCopiesAssociatedAlbums()
  {
    $sourceWebsiteId = 'SITE-1964e89c-0001-cows-a651-fc42dc78fe50-SITE';
    $sourceAlbumIds = array(
      'ALBUM-wc0ecf03-moe9-4adb-ed3f-72ebb087800c-ALBUM',
      'ALBUM-wc1ecf03-moe9-4adb-ed3f-72ebb087800c-ALBUM',
      'ALBUM-wc2ecf03-moe9-4adb-ed3f-72ebb087800c-ALBUM',
    );
    $sourceMediaNames = array(
      'Website_Copy_Album_0_0',
      'Website_Copy_Album_0_1',
      'Website_Copy_Album_0_2',
      'Website_Copy_Album_1_0',
      'Website_Copy_Album_1_1',
      'Website_Copy_Album_1_2',
      'Website_Copy_Album_2_0',
      'Website_Copy_Album_2_1',
      'Website_Copy_Album_2_2',
    );

    $testMediaFiles = array(
      'report_2009.pdf',
      'backbone.js',
      'footer.jpg',
      'header_0.png',
      'report_2010.pdf',
      'pdf.js',
      'report_2011.pdf',
      'grumble.js',
      'header_0.gif',
    );

    $this->createTestMedias($sourceWebsiteId, $testMediaFiles);

    $copyRequest = sprintf(
      'website/copy/params/{"id":"%s","name":"%s"}',
      $sourceWebsiteId,
      'copied_website_with_copied_albums'
    );

    $this->dispatch($copyRequest);

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('id', $responseData);
    $copiedWebsiteId = $responseData->id;

    $uniqueidValidator = new UniqueIdValidator(
      DataWebsite::ID_PREFIX,
      DataWebsite::ID_SUFFIX
    );

    $this->assertTrue($uniqueidValidator->isValid($copiedWebsiteId));

    $config = Registry::getConfig();
    $mediaBaseDirectory = realpath($config->media->files->directory);

    $sourceMediaDirectory = $mediaBaseDirectory
      . DIRECTORY_SEPARATOR . $sourceWebsiteId;

    $copiedMediaDirectory = $mediaBaseDirectory
      . DIRECTORY_SEPARATOR . $copiedWebsiteId;

    $this->assertTrue(is_dir($copiedMediaDirectory));

    $testFilesDirectory = $config->test->files->directory;
    $expectedCopyTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'trees'
      . DIRECTORY_SEPARATOR . 'website_copy.tree';
    $expectedCopyTree = file_get_contents($expectedCopyTreeFile);

    $this->assertSame(
      $expectedCopyTree,
      DirectoryHelper::getRecursiveAsJson($copiedMediaDirectory, true),
      "Tree mismatch between copied directory tree and expected directory tree"
    );

    $getAllMediaRequest = sprintf(
      'media/getall/params/{"websiteid":"%s"}',
      $copiedWebsiteId
    );

    $this->dispatch($getAllMediaRequest);

    $response = $this->getResponseBody();
    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertObjectHasAttribute('total', $responseData);

    $expectedCopiedMediaTotal = 9;
    $this->assertSame($expectedCopiedMediaTotal, $responseData->total);

    $actualCopiedAlbumIds = array();
    $actualMediaNames = array();

    foreach ($responseData->media as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertObjectHasAttribute('name', $media);

      $this->assertSame($copiedWebsiteId, $media->websiteId);
      $this->assertContains($media->albumId, $sourceAlbumIds);
      $actualMediaNames[] = $media->name;
      if (!in_array($media->albumId, $actualCopiedAlbumIds))
      {
        $actualCopiedAlbumIds[] = $media->albumId;
      }
    }

    sort($sourceMediaNames);
    sort($actualMediaNames);
    $this->assertSame($sourceMediaNames, $actualMediaNames);

    $expectedAlbumCount = 3;
    $this->assertSame($expectedAlbumCount, count($actualCopiedAlbumIds));

    $getAllAlbumRequest = sprintf(
      'album/getall/params/{"websiteid":"%s"}',
      $copiedWebsiteId
    );

    $this->dispatch($getAllAlbumRequest);

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);

    $copiedAlbums = $responseData->albums;
    $this->assertSame($expectedAlbumCount, count($copiedAlbums));

    $copiedAlbumIds = array();
    foreach ($copiedAlbums as $album)
    {
      $this->assertInstanceOf('stdClass', $album);
      $this->assertObjectHasAttribute('id', $album);
      $copiedAlbumIds[] = $album->id;
    }

    sort($actualCopiedAlbumIds);
    sort($copiedAlbumIds);
    $this->assertSame($actualCopiedAlbumIds, $copiedAlbumIds);

    $getAllMediaRequest = sprintf(
      'media/getall/params/{"websiteid":"%s"}',
      $sourceWebsiteId
    );

    $this->dispatch($getAllMediaRequest);

    $response = $this->getResponseBody();
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    $this->assertObjectHasAttribute('total', $responseData);

    $expectedSourceMediaTotal = 9;
    $this->assertSame($expectedSourceMediaTotal, $responseData->total);

    foreach ($responseData->media as $media)
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('websiteId', $media);
      $this->assertObjectHasAttribute('albumId', $media);

      $this->assertSame($sourceWebsiteId, $media->websiteId);
      $this->assertContains($media->albumId, $actualCopiedAlbumIds);
    }

    DirectoryHelper::removeRecursiv($sourceMediaDirectory, $mediaBaseDirectory);
    DirectoryHelper::removeRecursiv($copiedMediaDirectory, $mediaBaseDirectory);
  }

  /**
   * Website kopieren
   *
   * Der Test holt sich erst alle vorhanden Websites, der erste Eintrag
   * wird genutzt um die Pruefung von update durchzufuehren
   *
   * @test
   * @group integration
   */
  public function success()
  {
    // ARRANGE
    $expectedLocalPackageIds = array(
      'rz_local_test_1',
      'rz_local_test_2',
    );

    $copyWebsite = $this->getOneWebsite();

    // website kopieren
    $params = array(
      'id' => $copyWebsite->id,
      'name' => md5(time())
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/copy/params/' . $paramsAsJson);

    // Response holen
    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // neue Website-ID muss zurueckgegeben werden
    $this->assertNotNull($responseObject->data);
    $this->assertNotNull($responseObject->data->id);
    $this->assertNotSame($params['id'], $responseObject->data->id);
    $newWebsiteId = $responseObject->data->id;

    // Pruefung, ob Description und Navigation kopiert wurden
    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $websites = $responseObject->data->websites;

    foreach ($websites as $website)
    {
      if ($website->name == $params['name'])
      {
        // Website gefunden -> Website Daten holen
        $params = array('id' => $website->id);
        $paramsAsJson = json_encode($params);
        $this->dispatch('website/getbyid/params/' . $paramsAsJson);
        $response = $this->getResponseBody();
        $responseObject = json_decode($response);
        $this->assertResponseBodySuccess($responseObject);
        $newWebsite = $responseObject->data;

        // Daten pruefen
        $this->assertSame(
          $copyWebsite->description,
          $newWebsite->description
        );
        // Navigation (Pages) pruefen
        $this->assertSame(
          \Seitenbau\Json::encode($copyWebsite->navigation),
          \Seitenbau\Json::encode($newWebsite->navigation)
        );

        break;
      }
    }
    
    // Pruefung, ob TemplateSnippets kopiert wurden
    $this->dispatch('templatesnippet/getall/params/{"websiteid":"' . $newWebsiteId . '"}');
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
    $this->assertInternalType('array', $responseObject->data->templatesnippets);
    $this->assertSame(1, count($responseObject->data->templatesnippets));

    // check local packages
    $actualLocalPackageIds = array();
    $packageService = new \Cms\Service\Package('Package');
    $actualPackages = $packageService->getAll($newWebsiteId);
    foreach ($actualPackages as $package) {
      if ($package->getSourceType() == AbstractSourceItem::SOURCE_LOCAL) {
        $actualLocalPackageIds[] = $package->getId();
      }
    }
    sort($actualLocalPackageIds);
    $this->assertEquals($expectedLocalPackageIds, $actualLocalPackageIds);
  }

  /**
   * @test
   * @group integration
   */
  public function noParams()
  {
    $this->dispatch('website/copy/');

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('id', $invalidKeys);
    $this->assertArrayHasKey('name', $invalidKeys);
  }

  /**
   * @test
   * @group integration
   */
  public function invalidParams()
  {
    $params = array(
      'id' => 'INVALID_ID',
      'name' => 'ab'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/copy/data/' . $paramsAsJson);

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('id', $invalidKeys);
    $this->assertArrayHasKey('name', $invalidKeys);
  }

  /**
   * Gibt eine vorhandene Website zurueck
   */
  protected function getOneWebsite()
  {
    $this->dispatch('/website/getbyid/params/{"id":"SITE-25sbas76-12as-87d7-dujd-2312sd78de50-SITE"}');
    $response = $this->getResponseBody();

    $responseObject = json_decode($response);
    $website = $responseObject->data;

    return $website;
  }
}