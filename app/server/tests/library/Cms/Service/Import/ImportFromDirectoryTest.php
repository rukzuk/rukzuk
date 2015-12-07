<?php


namespace Cms\Service\Import;


use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Service\Import as ImportService;
use Cms\Service\Website as WebsiteService;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;

class ImportFromDirectoryTest extends ServiceTestCase
{
  /**
   * @var array
   */
  protected $sqlFixtures = array('library_Cms_Service_Import_ImportFromDirectoryTest.json');

  protected function setUp()
  {
    parent::setUp();
  }

  public function tearDown()
  {
    DirectoryHelper::clearLatchDirectory();
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function test_importFromDirectory_success()
  {
    // ARRANGE
    $config = Registry::getConfig();
    $importDirectory = FS::joinPath($config->test->files->directory,
      'test_imports', 'local_test_import_001');

    $importServiceMock = $this->getMockBuilder('\Cms\Service\Import')
      ->setMethods(array('removeUnzipDirectory'))->getMock();
    $importServiceMock->expects($this->never())
      ->method('removeUnzipDirectory');

    $expectedImportData = array(
      'website' => 1,
      'modules' => array(),
      'templatesnippets' => array(),
      'templates' => array(),
      'pages' => array(),
      'media' => array(),
      'albums' => array(),
      'usergroups' => array(),
      'packages' => array(
        array(
          'id' => 'rz_package_import_test_1',
          'name' => array(
            'de' => 'Test-Package-1',
            'en' => 'Test package 1',
          ),
        ),
        array(
          'id' => 'rz_package_import_test_2',
          'name' => array(
            'de' => 'Test-Package-2',
            'en' => 'Test package 2',
          ),
        ),
      ),
    );

    // ACT
    $importData = $importServiceMock->importFromDirectory(null, $importDirectory, null, null);

    // ASSERT
    $this->assertImportData($importData, $expectedImportData);
  }


  /**
   * @test
   * @group library
   */
  public function test_importFromDirectory_renameWebsiteIfNameGiven()
  {
    // ARRANGE
    $config = Registry::getConfig();
    $importDirectory = FS::joinPath($config->test->files->directory,
      'test_imports', 'local_test_import_001');

    $importServiceMock = $this->getMockBuilder('\Cms\Service\Import')
      ->setMethods(array('removeUnzipDirectory'))->getMock();
    $importServiceMock->expects($this->never())
      ->method('removeUnzipDirectory');

    $expectedWebsiteName = 'This_Is_The_New_website_Name';

    // ACT
    $importData = $importServiceMock->importFromDirectory(null, $importDirectory, null,
      $expectedWebsiteName);

    // ASSERT
    $this->assertArrayHasKey('websiteId', $importData);
    $this->assertNotEmpty($importData['website']);
    $actualWebsiteId = $importData['websiteId'];

    $this->assertArrayHasKey('website', $importData);
    $this->assertCount(1, $importData['website']);
    $this->assertInternalType('array', $importData['website'][0]);
    $this->assertArrayHasKey('name', $importData['website'][0]);
    $this->assertSame($expectedWebsiteName, $importData['website'][0]['name']);

    $actualWebsite = $this->getWebsiteService()->getById($actualWebsiteId);
    $this->assertSame($expectedWebsiteName, $actualWebsite->getName());
  }

  /**
   * @test
   * @group library
   */
  public function test_importFromDirectory_importDirectoryShouldNotBeDeletedIfConflictOccurred()
  {
    // ARRANGE
    $actualException = null;
    $websiteId = 'SITE-import00-from-dir0-conf-lict00000001-SITE';
    $config = Registry::getConfig();
    $importDirectory = FS::joinPath($config->test->files->directory,
      'test_imports', 'local_test_import_conflict');

    $importServiceMock = $this->getMockBuilder('\Cms\Service\Import')
      ->setMethods(array('removeUnzipDirectory'))->getMock();
    $importServiceMock->expects($this->never())
      ->method('removeUnzipDirectory');

    // ACT
    try {
      $importData = $importServiceMock->importFromDirectory($websiteId, $importDirectory, null, null);
    } catch (\Exception $actualException) {}

    // ASSERT
    $this->assertInstanceOf('\Cms\Service\Import\ConflictException', $actualException);
    $this->assertFileExists($importDirectory);

    $actualExceptionData = $actualException->getData();
    $this->assertArrayHasKey('importId', $actualExceptionData);
    $expectedImportLatchFile = FS::joinPath($config->import->latch->directory,
      $actualExceptionData['importId'].'.zip');
    $this->assertFileExists($expectedImportLatchFile);
  }

  /**
   * @param array $importData
   * @param array $expectedImportData
   */
  protected function assertImportData($importData, $expectedImportData)
  {
    $this->assertInternalType('array', $importData);

    $this->assertArrayHasKey('websiteId', $importData);
    $this->assertInternalType('string', $importData['websiteId']);
    $this->assertNotEmpty($importData['websiteId']);

    foreach ($expectedImportData as $dataKey => $expectedValue) {
      $this->assertArrayHasKey($dataKey, $importData);
      if (is_array($expectedValue)) {
        $this->assertEquals($expectedValue, $importData[$dataKey], '', 0, 10, true);
      } else {
        $this->assertInternalType('array', $importData[$dataKey]);
        $this->assertCount($expectedValue, $importData[$dataKey]);
        foreach ($importData[$dataKey] as $actualValue) {
          $this->assertInternalType('array', $actualValue);
          $this->assertArrayHasKey('id', $actualValue);
          $this->assertNotEmpty($actualValue['id']);
          $this->assertArrayHasKey('name', $actualValue);
          $this->assertNotEmpty($actualValue['name']);
        }
      }
    }
  }

  /**
   * @return WebsiteService
   */
  protected function getWebsiteService()
  {
    return new WebsiteService('Website');
  }
}
 