<?php


namespace Cms\Service\Import;


use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Service\Import as ImportService;
use Cms\Service\Website as WebsiteService;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;

class ImportWebsiteTest extends ServiceTestCase
{
  protected $filesToDeleteAtTearDown = array();
  protected $directoriesToDeleteAtTearDown = array();

  protected function setUp()
  {
    parent::setUp();

    // reset delete lists
    $this->filesToDeleteAtTearDown = array();
    $this->directoriesToDeleteAtTearDown = array();
  }

  public function tearDown()
  {
    // remove test files
    foreach ($this->filesToDeleteAtTearDown as $filePath) {
      DirectoryHelper::removeFile($filePath);
    }
    // remove test directories
    foreach ($this->directoriesToDeleteAtTearDown as $directory) {
      DirectoryHelper::removeRecursiv($directory);
    }

    parent::tearDown();
  }

  public function test_importActualWebsiteExport_success()
  {
    // ARRANGE
    $config = Registry::getConfig();
    $importDirectory = FS::joinPath($config->test->files->directory,
      'test_imports', 'actual_website_export_test');
    $importService = new ImportService();

    // ACT
    $importData = $importService->importFromDirectory(null, $importDirectory, null, null);

    // ASSERT
    $this->assertInternalType('array', $importData);
    $this->assertArrayHasKey('websiteId', $importData);
    $actualWebsite = $this->getWebsiteService()->getById($importData['websiteId']);
    $this->assertSame('the_global_set_id', $actualWebsite->getUsedSetId());
  }

  public function test_importVersion_1_7_0_success()
  {
    // ARRANGE
    $config = Registry::getConfig();
    $importDirectory = FS::joinPath($config->test->files->directory,
      'test_imports', 'website_export_v_1_7_0_test');
    $importService = new ImportService();

    // ACT
    $importData = $importService->importFromDirectory(null, $importDirectory, null, null);

    // ASSERT
    $this->assertInternalType('array', $importData);
    $this->assertArrayHasKey('websiteId', $importData);
    $actualWebsite = $this->getWebsiteService()->getById($importData['websiteId']);
    $this->assertSame('the_repo_id', $actualWebsite->getUsedSetId());
  }

  /**
   * @test
   * @group library
   */
  public function test_importWebsiteShouldCreateNewWebsiteId()
  {
    // ARRANGE
    $websiteId = 'SITE-import00-test-use0-id00-000000000002-SITE';
    $allowedImportMode = 'WEBSITE';
    $importFilePath = $this->createImportFile(
      'test_service_import_website_take_given_websiteid.zip');
    $importService = new ImportService();

    // ACT
    $importData = $importService->import($websiteId, $importFilePath, $allowedImportMode);

    // ASSERT
    $this->assertNotEquals($websiteId, $importData['websiteId']);
    $this->assertCount(1, $importData['website']);
    $this->assertInternalType('array', $importData['website'][0]);
    $this->assertArrayHasKey('id', $importData['website'][0]);
    $this->assertNotEquals($websiteId, $importData['website'][0]['id']);
    $this->assertCount(0, $importData['modules']);
    $this->assertCount(0, $importData['templatesnippets']);
    $this->assertCount(0, $importData['templates']);
    $this->assertCount(0, $importData['pages']);
    $this->assertCount(0, $importData['media']);
    $this->assertCount(0, $importData['albums']);
    $this->assertCount(0, $importData['usergroups']);

    $actualWebsite = $this->getWebsiteService()->getById($importData['websiteId']);
    $this->assertEmpty($actualWebsite->getUsedSetId());
  }

  /**
   * @param $importFilename
   *
   * @return string
   */
  protected function createImportFile($importFilename)
  {
    $config = Registry::getConfig();
    $importFilePath = FS::joinPath($config->test->files->directory,
      'test_exports', $importFilename);
    $fakedImportFile = FS::joinPath($config->import->directory,
      $importFilename);
    $unzipDirectory = str_replace('.zip', '', $fakedImportFile);

    $assertionMessage = sprintf("Test import file '%s' not exists",
      $importFilePath);
    $this->assertFileExists($importFilePath, $assertionMessage);

    // Add fake upload file and unzip directory to delete lists
    $this->filesToDeleteAtTearDown[] = $fakedImportFile;
    $this->directoriesToDeleteAtTearDown[] = $unzipDirectory;

    FS::copyFile($importFilePath, $fakedImportFile);
    FS::createDirIfNotExists($unzipDirectory, true);

    return $importFilePath;
  }

  /**
   * @return WebsiteService
   */
  protected function getWebsiteService()
  {
    return new WebsiteService('Website');
  }
}
 