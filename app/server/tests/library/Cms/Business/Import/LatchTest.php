<?php
namespace Cms\Business\Import;

use Test\Seitenbau\Cms\Business\Import\LatchMock as LatchBusiness;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;
use Seitenbau\Registry as Registry;
use Seitenbau\FileSystem as FS;

/**
 * Tests fuer Latch Funktionalitaet des Imports
 *
 * @package      Cms
 * @subpackage   Business\Import
 */
class LatchTest extends ServiceTestCase
{
  /**
   * @var \Cms\Business\Import\Latch
   */
  protected $business; 
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new LatchBusiness('Latch');
    DirectoryHelper::clearLatchDirectory();
  }
  protected function tearDown() 
  {
    DirectoryHelper::clearLatchDirectory();
    LatchBusiness::clearTestLatchDateAndTime();
    parent::tearDown();
  }
  
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function latchOfNonExistingImportfileShouldThrowExpectedException()
  {
    $websiteId = 'SITE-latch000-test-0000-0000-business0001-SITE';
    $this->business->latchImportFile($websiteId, 'no-way.txt');
  }  
  /**
   * @test
   * @group library
   */
  public function firstLatchShouldCreateDirectoryAndExpectedStorageJson()
  {
    $websiteId = 'SITE-latch000-test-0000-0000-business0001-SITE';

    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    $this->assertFileNotExists($importLatchDirectory);
    
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_latch.zip';
    $testImportTime = 133416663;

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->assertFileExists($testImportFile);
    
    LatchBusiness::setTestLatchDateAndTime($testImportTime);
    
    $latchImportId = $this->business->latchImportFile($websiteId, $testImportFile);
    $this->assertFileExists($importLatchDirectory);
    $expectedImportLatchFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $latchImportId.'.zip';
    $this->assertFileExists($expectedImportLatchFile);
    $expectedStorageJsonFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    $this->assertFileExists($expectedStorageJsonFile);
    
    $expectedStorageJsonFileContent = json_encode(
      array($latchImportId => array(
        'websiteId' => $websiteId,
        'file'      => $expectedImportLatchFile,
        'date'      => $testImportTime,
        'name'      => $testImportFilename,
      ))
    );
    $this->assertSame(
      $expectedStorageJsonFileContent, 
      file_get_contents($expectedStorageJsonFile)
    );
  }
  
  /**
   * @test
   * @group library
   */
  public function mutipleLatchesShouldBeAddedToStorageJson()
  {
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    $this->assertFileNotExists($importLatchDirectory);
    
    $testFilesDirectory = $config->test->files->directory;
    $websiteId = 'SITE-latch000-test-0000-0000-business0001-SITE';
    $websiteId1 = 'SITE-latch000-test-0000-0000-business0002-SITE';
    $testImportFilename = 'test_export_0_latch.zip';
    $testImportFilename1 = 'test_export_1_latch.zip';
    $testImportTime = time()-30;
    $testImportTime1 = time();

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $testImportFile1 = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename1;
    
    $this->assertFileExists($testImportFile);
    
    LatchBusiness::setTestLatchDateAndTime($testImportTime);
    
    $latchImportId = $this->business->latchImportFile($websiteId, $testImportFile);
    $this->assertFileExists($importLatchDirectory);
    $expectedImportLatchFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $latchImportId.'.zip';
    $this->assertFileExists($expectedImportLatchFile);
    $expectedStorageJsonFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    $this->assertFileExists($expectedStorageJsonFile);
    
    $expectedStorageJsonFileContent = json_encode(
      array($latchImportId => array(
        'websiteId' => $websiteId,
        'file'      => $expectedImportLatchFile,
        'date'      => $testImportTime,
        'name'      => $testImportFilename,
      ))
    );
    $this->assertSame(
      $expectedStorageJsonFileContent, 
      file_get_contents($expectedStorageJsonFile)
    );
    
    $this->assertFileExists($testImportFile1);
    
    LatchBusiness::setTestLatchDateAndTime($testImportTime1);
    
    $latchImportId1 = $this->business->latchImportFile($websiteId1, $testImportFile1);
    $this->assertFileExists($importLatchDirectory);
    $expectedImportLatchFile1 = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $latchImportId1.'.zip';
    $this->assertFileExists($expectedImportLatchFile1);
    $actualStorageJsonFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    $this->assertFileExists($actualStorageJsonFile);
    
    $expectedStorage = array(
      $latchImportId => array(
        'websiteId' => $websiteId,
        'file'      => $expectedImportLatchFile,
        'date'      => $testImportTime,
        'name'      => $testImportFilename,
      ),
      $latchImportId1 => array(
        'websiteId' => $websiteId1,
        'file'      => $expectedImportLatchFile1,
        'date'      => $testImportTime1,
        'name'      => $testImportFilename1,
      ),
    );
    
    $actualStorageJsonFileContent = file_get_contents($actualStorageJsonFile);
    $actualStorage = \json_decode($actualStorageJsonFileContent, true);
    $this->assertSame(
      $expectedStorage, 
      $actualStorage
    );
  }
  /**
   * @test
   * @group library
   */
  public function oldlatchImportShouldBeDeleted()
  {
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    $this->assertFileNotExists($importLatchDirectory);
    
    $testFilesDirectory = $config->test->files->directory;
    $websiteId = 'SITE-latch000-test-0000-0000-business0001-SITE';
    $websiteId1 = 'SITE-latch000-test-0000-0000-business0002-SITE';
    $testImportFilename = 'test_export_0_latch.zip';
    $testImportFilename1 = 'test_export_1_latch.zip';
    $testImportTime = 1325379661;  // 01.01.2012 01:01:01
    $testImportTime1 = time();  // Aktueller Timestamp

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->assertFileExists($testImportFile);
    
    LatchBusiness::setTestLatchDateAndTime($testImportTime);
    
    $latchImportId = $this->business->latchImportFile($websiteId, $testImportFile);
    $this->assertFileExists($importLatchDirectory);
    $expectedImportLatchFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $latchImportId.'.zip';
    $this->assertFileExists($expectedImportLatchFile);
    
    $testImportFile1 = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename1;
    
    $this->assertFileExists($testImportFile1);
    
    LatchBusiness::setTestLatchDateAndTime($testImportTime1);
    
    $latchImportId1 = $this->business->latchImportFile($websiteId1, $testImportFile1);
    $this->assertFileExists($importLatchDirectory);
    $expectedImportLatchFile1 = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $latchImportId1.'.zip';
    $this->assertFileExists($expectedImportLatchFile1);

    $actualStorageJsonFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;

    $expectedStorage = array(
      $latchImportId1 => array(
        'websiteId' => $websiteId1,
        'file'      => $expectedImportLatchFile1,
        'date'      => $testImportTime1,
        'name'      => $testImportFilename1,
      ),
    );
    
    $actualStorageJsonFileContent = file_get_contents($actualStorageJsonFile);
    $actualStorage = \json_decode($actualStorageJsonFileContent, true);
    $this->assertSame(
      $expectedStorage, 
      $actualStorage
    );
    
    $this->assertFileNotExists($expectedImportLatchFile);
  }

  /**
   * @test
   * @group library
   */
  public function test_latchImportFile_zipDirectoryIfZipFileNotGiven()
  {
    // ARRANGE
    $config = Registry::getConfig();

    $importLatchDirectory = $config->import->latch->directory;
    $this->assertFileNotExists($importLatchDirectory);
    $storageFile = FS::joinPath($importLatchDirectory, LatchBusiness::IMPORT_LATCH_STORAGE_FILE);

    $testImportTime = 1325379661;  // 01.01.2012 01:01:01
    LatchBusiness::setTestLatchDateAndTime($testImportTime);

    $websiteId = 'SITE-latch000-test-0000-0000-business0003-SITE';
    $localId = 'local_test_import_001';
    $importDirectory = FS::joinPath($config->test->files->directory, 'test_imports', $localId);

    // ACT
    $latchImportId = $this->business->latchImportFile($websiteId, $importDirectory);

    // ASSERT
    $this->assertFileExists($importLatchDirectory);

    $expectedImportLatchFile = FS::joinPath($importLatchDirectory, $latchImportId.'.zip');
    $this->assertFileExists($expectedImportLatchFile);

    $expectedStorage = array(
      $latchImportId => array(
        'websiteId' => $websiteId,
        'file'      => $expectedImportLatchFile,
        'date'      => $testImportTime,
        'name'      => $localId,
      ),
    );
    $actualStorage = \json_decode(file_get_contents($storageFile), true);
    $this->assertSame($expectedStorage, $actualStorage);
  }
}