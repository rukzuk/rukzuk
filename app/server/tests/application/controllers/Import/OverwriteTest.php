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
 * ImportController Overwrite Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class OverwriteTest extends ImportControllerTestCase
{
  protected function setUp()
  {
    parent::setUp();

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
  public function overwriteShouldReturnValidationErrorForInvalidParamTemplates()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $templates = 'INVALID_PARAM_TEMPLATES';
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules, 
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('templates', $errorData[0]->param->field);
    $this->assertStringEndsWith(
      sprintf("'%s' ist kein Array", $templates),
      $errorData[0]->text
    );
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidTemplateId()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-2b56-b52c-aeaa49e541c9-TPL',
      'MODUL-5rap62rs-ovwr-aa03-8de8-f2cb4236eb45-MODUL',
    );
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('templates', $errorData[0]->param->field);
    $this->assertStringEndsWith('Template Id ist ungueltig', $errorData[0]->text);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidParamModules()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $modules = 'INVALID_PARAM_MODULES';
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-2b56-b52c-aeaa49e541c9-TPL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('modules', $errorData[0]->param->field);
    $this->assertStringEndsWith(
      sprintf("'%s' ist kein Array", $modules),
      $errorData[0]->text
    );
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidModuleId()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
      'TPL-3b249276-ovwr-2b56-b52c-aeaa49e541c9-TPL'
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-5rap62rs-ovwr-aa03-8de8-f2cb4236eb45-TPL',
    );
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('modules', $errorData[0]->param->field);
    $this->assertStringEndsWith('Modul Id ist ungueltig', $errorData[0]->text);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidParamTemplatesnippets()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $templateSnippets = 'INVALID_PARAM_TEMPALTESNIPPETS';
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-2b56-b52c-aeaa49e541c9-TPL',
    );
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'templatesnippets' => $templateSnippets,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('templatesnippets', $errorData[0]->param->field);
    $this->assertStringEndsWith(
      sprintf("'%s' ist kein Array", $templateSnippets),
      $errorData[0]->text
    );
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidTemplateSnippetId()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $templateSnippets = array(
      'TPLS-0000000-0000-0000-0000-000000000001-TPLS',
      'MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB',
    );
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-5rap62rs-ovwr-aa03-8de8-f2cb4236eb45-TPL',
    );
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'templatesnippets' => $templateSnippets,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('templatesnippets', $errorData[0]->param->field);
    $this->assertStringEndsWith('TemplateSnippet Id ist ungueltig', $errorData[0]->text);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidMediaParam()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $media = 'INVALID_PARAM_MEDIA';
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-2b56-b52c-aeaa49e541c9-TPL',
    );
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('media', $errorData[0]->param->field);
    $this->assertStringEndsWith(
      sprintf("'%s' ist kein Array", $media),
      $errorData[0]->text
    );
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForInvalidMediaId()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array(
      'MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB',
      'TPL-me4mo49-ovwr-b9fh-92bd-af5d4c4df4a6-TPL',
    );
    $templates = array(
      'TPL-3b249276-ovwr-0b56-b52c-aeaa49e541c9-TPL',
      'TPL-3b249276-ovwr-1b56-b52c-aeaa49e541c9-TPL',
      'TPL-5rap62rs-ovwr-aa03-8de8-f2cb4236eb45-TPL',
    );
    
    $data = array(
      'templates' => $templates,
      'modules' => $modules,
      'media' => $media,
      'importId' => str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH),
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('media', $errorData[0]->param->field);
    $this->assertStringEndsWith('Media Id ist ungueltig', $errorData[0]->text);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldReturnValidationErrorForMissingImportId()
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $templates = array('TPL-3b249276-ovwr-4b56-b52c-aeaa49e541c9-TPL');
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49f-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates, 
      'modules' => $modules, 
      'media' => $media,
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    
    $this->dispatch($request);
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('importid', $errorData[0]->param->field);
  }  
  /**
   * @test
   * @group integration
   * @dataProvider invalidImportIdsProvider
   * @param string $importId
   */
  public function overwriteShouldReturnValidationErrorForInvalidImportIds($importId)
  {
    //$websiteId = 'SITE-02a7358d-ovwr-4b9a-b3c8-75c8ec37d324-SITE';
    $templates = array('TPL-3b249276-ovwr-4b56-b52c-aeaa49e541c9-TPL');
    $modules = array(
      'MODUL-5rap62rs-ovwr-aa01-8de8-f2cb4236eb45-MODUL',
      'MODUL-5rap62rs-ovwr-aa02-8de8-f2cb4236eb45-MODUL',
    );
    $media = array('MDB-me4mo49e-ovwr-b9fh-92bd-af5d4c4df4a6-MDB');
    
    $data = array(
      'templates' => $templates, 
      'modules' => $modules, 
      'media' => $media,
      'importId' => $importId,
    );
    
    $request = sprintf(
      '/import/overwrite/params/%s',
      json_encode($data)
    );
    $this->dispatch($request);
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    
    $this->assertEquals('importid', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldNotOverwriteOnEmptyOverwrites()
  {
    DirectoryHelper::clearLatchDirectory();
    $websiteId = 'SITE-im12maec-ovwr-4ea8-a477-t4e79e8e62m6-SITE';
    $expectedPreMediaCount = 2; 
    $expectedPreModulesCount = 1;
    $expectedPreTemplateCount = 1;
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->media) === $expectedPreMediaCount);
    
    $this->dispatch(sprintf(
      '/modul/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->modules) === $expectedPreModulesCount);
    
    $this->dispatch(sprintf(
      '/template/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->templates) === $expectedPreTemplateCount);
    
    $modules = $templates = $media = array();    
    $config = Registry::getConfig();
    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_templatesnippet_no_overwriting.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates, 
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

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('pages', $responseData);
    $this->assertObjectHasAttribute('website', $responseData);
    
    $this->assertEmpty($responseData->website);
    $this->assertEmpty($responseData->pages);
    $this->assertEmpty($responseData->templates);
    $this->assertEmpty($responseData->modules);
    $this->assertEmpty($responseData->media);
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->media) === $expectedPreMediaCount);
    
    $this->dispatch(sprintf(
      '/modul/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->modules) === $expectedPreModulesCount);
    
    $this->dispatch(sprintf(
      '/template/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->templates) === $expectedPreTemplateCount);
    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldOverwriteStatedMediasAndTemplateSnippet()
  {
    DirectoryHelper::clearLatchDirectory();
    $websiteId = 'SITE-im12maec-ovwr-4ea8-a477-t4e79e8e62m5-SITE';
    $expectedPreMediaCount = 3; 
    $expectedPreModulesCount = 2;
    $expectedPreTemplateCount = 1;
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->media) === $expectedPreMediaCount);
    
    $this->dispatch(sprintf(
      '/modul/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->modules) === $expectedPreModulesCount);
    
    $this->dispatch(sprintf(
      '/template/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->templates) === $expectedPreTemplateCount);

    $templates = array();
    $modules = array();
    $media = array(
      'MDB-exp0d0ec-cb0f-4961-95ow-765d4aa581a0-MDB',
      'MDB-exp0d0ec-cb0f-4961-95ow-765d4aa581a1-MDB',
      'MDB-exp0d0ec-cb0f-4961-94ow-765d4aa581a2-MDB',
    );
    $templateSnippets = array(
      'TPLS-template-snip-pet0-test-000000000021-TPLS',
    );

    $config = Registry::getConfig();
    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_templatesnippet_conflict_media_and_templatesnippet.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates, 
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
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('pages', $responseData);
    $this->assertObjectHasAttribute('website', $responseData);
    
    $this->assertEmpty($responseData->website);
    $this->assertEmpty($responseData->pages);
    $this->assertEmpty($responseData->templates);
    $this->assertEmpty($responseData->modules);

    $this->assertNotEmpty($responseData->templatesnippets);
    $this->assertNotEmpty($responseData->media);
    
    $importedMedia = $responseData->media;
    $this->assertTrue(count($importedMedia) === 3);
    $importedTemplateSnippets = $responseData->templatesnippets;
    $this->assertTrue(count($importedTemplateSnippets) === 1);

    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldOverwriteStatedMedia()
  {
    $config = Registry::getConfig();
    DirectoryHelper::clearLatchDirectory();
    
    $websiteId = 'SITE-im12maec-ovwr-4ea8-a477-t4e79e8e62m4-SITE';
    
    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->media) === 4);

    $templates = array();
    $templateSnippets = array();
    $modules = array();
    $media = array(
      'MDB-exp0d0ec-cb0f-4961-96ow-765d4aa581a0-MDB',
      'MDB-exp0d0ec-cb0f-4961-96ow-765d4aa581a1-MDB',
      'MDB-exp0d0ec-cb0f-4961-96ow-765d4aa581a2-MDB',
    );

    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_templatesnippet_conflict_media.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates,
      'templateSnippets' => $templateSnippets,
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
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('pages', $responseData);
    $this->assertObjectHasAttribute('website', $responseData);
    
    $this->assertEmpty($responseData->website);
    $this->assertEmpty($responseData->pages);
    $this->assertEmpty($responseData->templates);
    $this->assertEmpty($responseData->modules);
    
    // TemplateSnippet werden importiert, da noch nicht vorhanden
    $this->assertNotEmpty($responseData->templatesnippets);
    $this->assertTrue(count($responseData->templatesnippets) === 1);
    
    $this->assertNotEmpty($responseData->media);
    $importedMedia = $responseData->media;
    $this->assertTrue(count($importedMedia) === 3);

    $this->dispatch(sprintf(
      '/media/getall/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();

    $this->assertTrue(count($responseData->media) === 4);

    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }
  
  /**
   * @test
   * @group integration
   */
  public function overwriteShouldOverwriteStatedModules()
  {
    $config = Registry::getConfig();
    
    DirectoryHelper::clearLatchDirectory();
    
    $websiteId = 'SITE-im12maec-ovwr-4ea8-a477-t4e79e8e62m3-SITE';
    
    $this->dispatch(sprintf(
      '/modul/getAll/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    
    $this->assertTrue(count($responseData->modules) === 4);
    
    $modules = array(
      'MODUL-0rap5eb8-0df3-47e9-exti-71ae9d96d3m0-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-exti-71ae9d96d3m1-MODUL'
    );
    $templates = $media = array();
    
    $importLatchFilesDirectory = $config->import->latch->files->directory;
    $importFileToLatch = $importLatchFilesDirectory
      . DIRECTORY_SEPARATOR . 'import_modules_conflict_modules.zip';
    
    $this->assertFileExists($importFileToLatch);
    
    $latchBusiness = new LatchBusiness('Latch');
    $importId = $latchBusiness->latchImportFile($websiteId, $importFileToLatch);

    $data = array(
      'templates' => $templates, 
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
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertObjectHasAttribute('templates', $responseData);
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('pages', $responseData);
    $this->assertObjectHasAttribute('website', $responseData);
    
    $this->assertEmpty($responseData->website);
    $this->assertEmpty($responseData->pages);
    $this->assertEmpty($responseData->templates);
    $this->assertEmpty($responseData->media);

    $this->assertNotEmpty($responseData->modules);
    $importedModules = $responseData->modules;
    $this->assertTrue(count($importedModules) === 3);
    
    $this->dispatch(sprintf(
      '/modul/getAll/params/{"websiteid":"%s"}',
      $websiteId)
    );
    $response = new Response($this->getResponseBody());
    $responseData = $response->getData();
    $this->assertTrue(count($responseData->modules) === 5);
    
    $this->removeImportMediaDirectoryAndFiles($websiteId);
  }

  /**
   * @return array
   */
  public function invalidImportIdsProvider()
  {
    return array(
      array(null),
      array(str_repeat('a', LatchBusiness::LATCH_IMPORT_ID_LENGTH - 1)),
      array(str_repeat('b', LatchBusiness::LATCH_IMPORT_ID_LENGTH + 1)),
    );
  }

}