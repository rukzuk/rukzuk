<?php


namespace Cms\Service\Import;


use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;
use Cms\Service\Import as ImportService;
use Cms\Service\Template as TemplateService;

class ImportTemplateTest extends ServiceTestCase
{
  protected $sqlFixtures = array('library_Cms_Service_Import_ImportTemplateTest.json');

  /**
   * @var string
   */
  private $fakedImportFileToDelete;

  /**
   * @var string
   */
  private $importUnzipDirectoryToDelete;

  /**
   * @var string
   */
  protected $testFilesDirectory = null;

  /**
   * @var \Cms\Service\Template
   */
  protected $templateService;


  protected function setUp()
  {
    parent::setUp();

    $config = Registry::getConfig();
    $this->testFilesDirectory = $config->test->files->directory;
  }

  protected function tearDown()
  {
    if ($this->fakedImportFileToDelete !== null) {
      DirectoryHelper::removeFile($this->fakedImportFileToDelete);
    }
    if ($this->importUnzipDirectoryToDelete !== null){
      $config = Registry::getConfig();
      $testImportDirectory = $config->import->directory;

      DirectoryHelper::removeRecursiv($this->importUnzipDirectoryToDelete, $testImportDirectory);
    }

    parent::tearDown();
  }

  /**
   * @test
   * @group library
   * @ticket SBCMS-2393
   */
  public function test_import_importingExportTemplateShouldThrowExceptionAndRemoveImportFiles()
  {
    // ARRANGE
    $importService = new ImportService('Import');
    $websiteId = 'SITE-rs13up2c-exm0-4ea8-a477-4ee79e8e62pa-SITE';
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_template.zip';
    $testImportFile = FS::joinPath($testFilesDirectory,
      'test_exports', $testImportFilename);
    $this->fakedImportFileToDelete = FS::joinPath($testImportDirectory,
      $testImportFilename);
    $this->importUnzipDirectoryToDelete = str_replace('.zip', '',
      $this->fakedImportFileToDelete);

    $this->assertFileExists($testImportFile, sprintf(
      "Failed asserting import file '%s' exists", $testImportFile));
    copy($testImportFile, $this->fakedImportFileToDelete);
    mkdir($this->importUnzipDirectoryToDelete);

    // ACT
    try {
      $importService->import($websiteId, $this->fakedImportFileToDelete, null);
      $occurredException = null;
    } catch (\Exception $e) {
      $occurredException = $e;
    }

    // ASSERT
    $this->assertInstanceOf('\Cms\Exception', $occurredException);
    $this->assertEquals(25, $occurredException->getCode());
    $this->assertFileNotExists($this->fakedImportFileToDelete, sprintf(
      "Faked import file '%s' wasn't deleted", $this->fakedImportFileToDelete));
    $this->assertFileNotExists($this->importUnzipDirectoryToDelete, sprintf(
      "Import unzip directory '%s' wasn't deleted", $this->importUnzipDirectoryToDelete));
  }

  /**
   * @test
   * @group library
   *
   * @dataProvider provider_test_updateOrCreateTemplateFromTemplateJson_success
   */
  public function test_updateOrCreateTemplateFromTemplateJson_success($websiteId,
                                                                      $templateId,
                                                                      $expectedTemplate)
  {
    // ARRANGE
    $templateJsonFile = FS::joinPath($this->testFilesDirectory, 'import', 'import_test_002',
      'templates', $templateId, 'template.json');
    $templateService = new TemplateService('Template');
    $importServiceMock = $this->getMockBuilder('\Cms\Service\Import')
      ->setConstructorArgs(array('Import'))
      ->setMethods(array('cleaningColorIds'))
      ->getMock();

    // ACT
    $actualImportedData = $this->callMethod($importServiceMock,
      'updateOrCreateTemplateFromTemplateJson', array($templateId, $websiteId, $templateJsonFile));

    // ASSERT
    $this->assertEquals($templateId, $actualImportedData['id']);
    $actualTemplate = $templateService->getById($templateId, $websiteId);
    $this->assertInstanceOf('\Cms\Data\Template', $actualTemplate);
    $actualTemplateAsArray = $actualTemplate->toArray();
    foreach($expectedTemplate as $attributeName => $expectedValue) {
      $this->assertArrayHasKey($attributeName, $actualTemplateAsArray);
      $this->assertEquals($expectedValue, $actualTemplateAsArray[$attributeName]);
    }
  }

  /**
   * @return array
   */
  public function provider_test_updateOrCreateTemplateFromTemplateJson_success()
  {
    $websiteId = 'SITE-template-impo-rt0t-est0-000000000001-SITE';
    return array(
      array(
        $websiteId,
        'TPL-template-impo-rt0t-est0-000000000001-TPL',
        array(
          'websiteid' => $websiteId,
          'id' => 'TPL-template-impo-rt0t-est0-000000000001-TPL',
          'name' => 'template import 1',
          'content' => json_encode(array((object)array('abc' => 'def'))),
          'pageType' => 'the_page_type_id',
        )
      ),
      // use default pageType 'page'
      array(
        $websiteId,
        'TPL-template-impo-rt0t-est0-000000000002-TPL',
        array(
          'websiteid' => $websiteId,
          'id' => 'TPL-template-impo-rt0t-est0-000000000002-TPL',
          'name' => 'template import 2',
          'content' => json_encode(array()),
          'pageType' => 'page',
        )
      ),
    );
  }
}
