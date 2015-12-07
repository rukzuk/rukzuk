<?php


namespace Cms\Service\Import;


use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;
use Cms\Service\Import as ImportService;

class ImportPagesTest extends ServiceTestCase
{
  /**
   * @var string
   */
  private $fakedImportFileToDelete;

  /**
   * @var string
   */
  private $importUnzipDirectoryToDelete;


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
   * @ticket SBCMS-891
   * @ticket SBCMS-2393
   */
  public function test_import_importingPagesShouldThrowExceptionAndRemoveImportFiles()
  {
    // ARRANGE
    $importService = new ImportService('Import');
    $websiteId = 'SITE-rs13up2c-exm0-4ea8-a477-4ee79e8e62pa-SITE';
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_pages_export_non_existing_pages_templates_and_modules.zip';
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
}
 