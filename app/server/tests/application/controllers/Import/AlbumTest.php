<?php
namespace Application\Controller\Import;

use Cms\Business\Import\Latch as LatchBusiness,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ImportControllerTestCase,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;

/**
 * ImportController Album Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class AlbumTest extends ImportControllerTestCase
{
  public $sqlFixtures = array('AlbumTest.json');

  protected function setUp()
  {
    parent::setUp();

    // TODO: remove the WriteableModuleMock
    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Modul', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });
  }

  protected function tearDown() 
  {
    ModuleWriteableMock::tearDown();
    
    DirectoryHelper::clearLatchDirectory();

    parent::tearDown();
  }
  /**
   * @test
   * @group integration
   */
  public function importAgainstExistingAlbumImportsIntoExistingOne()
  {
    DirectoryHelper::clearLatchDirectory();
    $websiteId = 'SITE-im12maec-imal-4ea8-a477-t4e79e8e62m0-SITE';
    $existingAlbumId = 'ALBUM-ex0wcf0d-acc4-4fdb-imal-72ebb08780ig-ALBUM';
    
    $this->dispatch(sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertTrue(count($responseData->albums) === 1);
    
    $albumBeforeOverwrite = $responseData->albums[0];
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
      $websiteId,
      $existingAlbumId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertEmpty($responseData->media);
    $this->assertEquals(count($responseData->media), $responseData->total);
    
    $media = array(
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581a0-MDB',
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581a1-MDB',
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581a2-MDB',
    );
    $templates = $templateSnippet = $modules = array();
    
    $config = Registry::getConfig();
    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_templatesnippet_conflict_media_existing_album.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates,
      'templatesnippets' => $templateSnippet,
      'modules' => $modules, 
      'media' => $media,
      'importId' => $importId,
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    $response = $this->getValidatedSuccessResponse();
    
    $this->dispatch(sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertTrue(count($responseData->albums) === 1);
    
    $albumAfterOverwrite = $responseData->albums[0];
    $this->assertEquals($albumBeforeOverwrite, $albumAfterOverwrite);
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
      $websiteId,
      $existingAlbumId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertNotEmpty($responseData->media);
    $this->assertEquals(count($media), $responseData->total);
    
    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }
  /**
   * @test
   * @group integration
   */
  public function importAgainstNonExistingAlbumsCreatesThemImportsIntoThem()
  {
    DirectoryHelper::clearLatchDirectory();
    $websiteId = 'SITE-im12maec-imal-4ea8-a477-t4e79e8e62m1-SITE';
    
    $this->dispatch(sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertTrue(count($responseData->albums) === 0);
    
    $media = array(
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581n0-MDB',
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581n1-MDB',
      'MDB-exp0d0ec-cb0f-4961-imal-765d4aa581n2-MDB',
    );
    $templates = $templateSnippet = $modules = array();
    
    $config = Registry::getConfig();
    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_templatesnippet_conflict_media_non_existing_album.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates,
      'templatesnippets' => $templateSnippet,
      'modules' => $modules, 
      'media' => $media,
      'importId' => $importId,
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = $this->getValidatedSuccessResponse();
    
    $expectedAlbumIdsAndMediaCount = array(
      'ALBUM-ex0wcf0d-acc4-4fdb-imal-72ebb08780ne-ALBUM' => 2,
      'ALBUM-ex0wcf0d-acc4-4fdb-imal-72ebb08781ne-ALBUM' => 1,
    );
    $expectedAlbumIds = array_keys($expectedAlbumIdsAndMediaCount);
    
    $this->dispatch(sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertTrue(count($responseData->albums) === count($expectedAlbumIds));
    
    $createdAlbumIds = array();
    array_filter($responseData->albums, function($album) use(&$createdAlbumIds) {
      return $createdAlbumIds[] = $album->id;
    }); 
    
    sort($expectedAlbumIds);
    sort($createdAlbumIds);
    
    $this->assertEquals($expectedAlbumIds, $createdAlbumIds);
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
      $websiteId,
      $createdAlbumIds[0])
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertNotEmpty($responseData->media);
    $this->assertEquals(
      $expectedAlbumIdsAndMediaCount[$createdAlbumIds[0]], 
      $responseData->total
    );
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
      $websiteId,
      $createdAlbumIds[1])
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertNotEmpty($responseData->media);
    $this->assertEquals(
      $expectedAlbumIdsAndMediaCount[$createdAlbumIds[1]], 
      $responseData->total
    );
    
    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }  
}