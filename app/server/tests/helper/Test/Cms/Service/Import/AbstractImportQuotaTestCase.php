<?php


namespace Test\Cms\Service\Import;


use Cms\Dao\Website as WebsiteDao;
use Cms\Service\Website as WebsiteService;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Service\Import as ImportService;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;
use Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;


class ImportQuotaTestService extends ImportService
{
  private $phpunitWebsiteService = null;

  public function phpunitSetWebsiteService($websiteService)
  {
    $this->phpunitWebsiteService = $websiteService;
  }

  public function getWebsiteService()
  {
    if (!is_null($this->phpunitWebsiteService)) {
      return $this->phpunitWebsiteService;
    }
    return parent::getWebsiteService();
  }
}


abstract class AbstractImportQuotaTestCase extends ServiceTestCase
{
  protected $filesToDeleteAtTearDown = array();
  protected $directoriesToDeleteAtTearDown = array();

  protected function setUp()
  {
    parent::setUp();

    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Modul', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });

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

    ModuleWriteableMock::tearDown();

    parent::tearDown();
  }

  /**
   * @param $maxCount
   */
  protected function setQuotaWebsiteMaxCount($maxCount)
  {
    $config = Registry::getConfig();
    $newConfig = new \Zend_Config($config->toArray(), true);
    $newConfig->quota->website->maxCount = $maxCount;
    $newConfig->setReadOnly();
    Registry::setConfig($newConfig);
  }

  /**
   * @param boolean $enableDev
   */
  protected function setQuotaModuleEnableDev($enableDev)
  {
    $config = Registry::getConfig();
    $newConfig = new \Zend_Config($config->toArray(), true);
    $newConfig->quota->module->enableDev = $enableDev;
    $newConfig->setReadOnly();
    Registry::setConfig($newConfig);
  }

  /**
   * @param WebsiteDao|null $websiteDao
   *
   * @return \Cms\Service\Import
   */
  protected function createImportService($websiteDao = null)
  {
    $websiteService = new WebsiteService('Website');
    if ($websiteDao instanceof WebsiteDao) {
      $websiteService->setDao($websiteDao);
    }
    $importService = new ImportQuotaTestService();
    $importService->phpunitSetWebsiteService($websiteService);
    return $importService;
  }

  /**
   * @param $importFilename
   *
   * @return string
   */
  protected function createImportFile($importFilename, $subDirectory = 'test_exports')
  {
    $config = Registry::getConfig();
    $importFilePath = FS::joinPath($config->test->files->directory,
      $subDirectory, $importFilename);
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
   * create website dao which throw exception if any modification
   * method is called and return expected website count at getCount
   *
   * @param $websiteCount
   * @param $websiteExists
   *
   * @internal param $expectedWebsiteCount
   *
   * @return WebsiteDao
   */
  protected function getWebsiteDaoMock($websiteCount, $websiteExists)
  {
    $websiteDao = $this->getMock('\Cms\Dao\Website');
    $websiteDao->expects($this->never())->method(
      $this->logicalNot($this->logicalOr(
        $this->equalTo('existsWebsite'),
        $this->equalTo('convertToCmsDataObject'),
        $this->equalTo('getCount')
      )));
    $websiteDao->expects($this->any())->method('existsWebsite')
      ->will($this->returnValue($websiteExists));
    $websiteDao->expects($this->any())->method('convertToCmsDataObject')
      ->will($this->returnArgument(0));
    $websiteDao->expects($this->any())->method('getCount')
      ->will($this->returnValue($websiteCount));
    return $websiteDao;
  }
}