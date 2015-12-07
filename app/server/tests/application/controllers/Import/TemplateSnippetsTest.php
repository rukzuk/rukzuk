<?php
namespace Application\Controller\Import;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ImportControllerTestCase,
    Cms\Business\Export as ExportBusiness;
/**
 * ImportController TemplateSnippets Import Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class TemplateSnippetsTest extends ImportControllerTestCase
{
  protected function tearDown() 
  {
    $this->clearFakeUpload();
    DirectoryHelper::clearLatchDirectory();

    parent::tearDown();
  }
  
  /**
   * @test
   * @group  integration
   */
  public function importTemplateSnippetShouldThrowValidationErrorOnNotAllowedType()
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_module_export_not_allowed_type.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpi5teim';
    $fileInputname = 'import';

    $websiteId = 'SITE-im13up2c-mul0-3ex8-a4f7-4ee73e8e62te-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s","allowedType":"%s"}',
      $websiteId,
      $fileInputname,
      ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET  // Nur TemplateSnippets duerfen importiert werden
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    
    $response = $this->getResponseBody();
    
    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $this->assertNull($response->getData());
    
    $reponseError = $response->getError();
    $this->assertEquals(34, $reponseError[0]->code);
    
    $assertionMessage = "Import files weren't removed as expected";
    $this->assertFileNotExists($expectedImportFile, $assertionMessage);
    $this->assertFileNotExists($importUnzipDirectory, $assertionMessage);
  }

  /**
   * @test
   * @integration
   */
  public function templateSnippetImportShouldRespondWithConflictingTemplateSnippet()
  {
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';
    
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_templatesnippets_conflict_only_templatesnippet.zip';
    
    $testImportFile = $testFilesDirectory 
      . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpe7f3im';
    $fileInputname = 'import';
    
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);
    
    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $response = new Response($this->getResponseBody());
    $this->assertFalse($response->getSuccess());

    $reponseError = $response->getError();
    $this->assertEquals(11, $reponseError[0]->code);
    
    $responseData = $response->getData();
    
    $this->assertObjectHasAttribute('importId', $responseData);
    $this->assertObjectHasAttribute('conflict', $responseData);
    
    $conflictData = $responseData->conflict;
    
    $this->assertObjectHasAttribute('templatesnippets', $conflictData);
    $this->assertObjectHasAttribute('templates', $conflictData);
    $this->assertObjectHasAttribute('modules', $conflictData);
    $this->assertObjectHasAttribute('media', $conflictData);
    
    $expectedConflictingTemplateSnippets = array();
    
    $conflictingTemplateSnippet = new \StdClass;
    $conflictingTemplateSnippet->id = 'TPLS-template-snip-pet0-test-000000000023-TPLS';
    $conflictingTemplateSnippet->oldName = 'TEMPLATE_SNIPPET_NAME_23';
    $conflictingTemplateSnippet->newName = 'Export_Test_TemplateSnippet_0';
    
    $expectedConflictingTemplateSnippets[] = $conflictingTemplateSnippet;
    
    $this->assertTrue(count($conflictData->templatesnippets) === count($expectedConflictingTemplateSnippets));
    $this->assertEquals($expectedConflictingTemplateSnippets, $conflictData->templatesnippets);
    
    $errorData = $response->getError();
    
    $this->assertEquals(11, $errorData[0]->code);
    $errorMessage = \Cms\Error::getMessageByCode(11);
    $this->assertEquals($errorMessage, $errorData[0]->text);
    
    if (file_exists($testImportTmpFile)) {
      unlink($testImportTmpFile);
    }
    
    if (strstr($importUnzipDirectory, $testImportDirectory)) 
    {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory)) 
      {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile)) 
      {
        unlink($expectedImportFile);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function templateSnippetImportShouldImportAsExpectedForOnlyNewTemplateSnippetIds()
  {
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_templatesnippets_export_non_existing_templatesnippets.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3im';
    $fileInputname = 'import';

    $alreadyExistingWebsiteId = 'SITE-im13up2c-20da-4ea8-a477-4ee79e8e62mo-SITE';

    $this->assertHasNoMedias($alreadyExistingWebsiteId);

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $alreadyExistingWebsiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);

    $this->getValidatedSuccessResponse();

    $this->assertHeaderContains('Content-Type', 'text/plain');

    $expectedNewTemplateSnippets = array(
      'TPLS-template-snip-pet0-test-import000001-TPLS' => (object) array(
        'id'          => 'TPLS-template-snip-pet0-test-import000001-TPLS',
        'websiteId'   => $alreadyExistingWebsiteId,
        'name'        => 'Imported_Test_TemplateSnippet_1',
        'description' => '',
        'category'    => '',
        'content'     => json_decode('[{"id":"MUNIT-00000000-0000-0000-0000-000000000001-MUNIT","moduleId":"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL","name":"SnippetUnit_01","description":"","formValues":{},"children":[],"deletable":false,"ghostContainer":false,"visibleFormGroups":[]}]'),
        'readonly'    => false,
        'sourceType'  => 'local',
        'overwritten' => false,
        'baseLayout'  => false,
        'pageTypes'   => array(),
        'previewImageUrl' => null,
      ),
      'TPLS-template-snip-pet0-test-import000002-TPLS' => (object) array(
        'id'          => 'TPLS-template-snip-pet0-test-import000002-TPLS',
        'websiteId'   => $alreadyExistingWebsiteId,
        'name'        => 'Imported_Test_TemplateSnippet_2',
        'description' => '',
        'category'    => '',
        'content'     => json_decode('[{"id":"MUNIT-00000000-0000-0000-0000-000000000002-MUNIT","moduleId":"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL","name":"SnippetUnit_02","description":"","formValues":{},"children":[],"deletable":false,"ghostContainer":false,"visibleFormGroups":[]}]'),
        'readonly'    => false,
        'sourceType'  => 'local',
        'overwritten' => false,
        'baseLayout'  => false,
        'pageTypes'   => array(),
        'previewImageUrl' => null,
      ),
      'TPLS-template-snip-pet0-test-import000003-TPLS' => (object) array(
        'id'          => 'TPLS-template-snip-pet0-test-import000003-TPLS',
        'websiteId'   => $alreadyExistingWebsiteId,
        'name'        => 'Imported_Test_TemplateSnippet_3',
        'description' => '',
        'category'    => '',
        'content'     => json_decode('[]'),
        'readonly'    => false,
        'sourceType'  => 'local',
        'overwritten' => false,
        'baseLayout'  => false,
        'pageTypes'   => array(),
        'previewImageUrl' => null,
      ),
    );
    
    $requestUri = sprintf(
      '/templatesnippet/getall/params/{"websiteId":"%s"}',
      $alreadyExistingWebsiteId
    );
    $this->dispatch($requestUri);
    
    $response = $this->getValidatedSuccessResponse();

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('templatesnippets', $responseData);
    $this->assertInternalType('array', $responseData->templatesnippets);
    $templateSnippets = $responseData->templatesnippets;
    $actualNewTemplateSnippets = array();
    foreach ($templateSnippets as $templateSnippet)
    {
      $this->assertInstanceOf('stdClass', $templateSnippet);
      $this->assertObjectHasAttribute('id', $templateSnippet);
      
      $assertionMessage = sprintf(
        'Actual TemplateSnippet and expected TemplateSnippet not equal (%s).',
        $templateSnippet->id
      );
      $this->assertEquals($expectedNewTemplateSnippets[$templateSnippet->id], $templateSnippet, $assertionMessage);
    }

    $this->assertEquals(count($expectedNewTemplateSnippets), count($templateSnippets));

    $this->assertHasNoMedias($alreadyExistingWebsiteId);

    if (strstr($importUnzipDirectory, $testImportDirectory))
    {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory))
      {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile))
      {
        unlink($expectedImportFile);
      }
    }
  }

}