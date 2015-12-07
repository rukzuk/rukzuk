<?php
namespace Cms\Business\Import;

use Cms\Business\Import as ImportBusiness,
    Cms\Business\Import\Latch as LatchBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Seitenbau\Registry as Registry;

/**
 * Tests fuer Import Cancel Funktionalitaet des Imports
 *
 * @package      Cms
 * @subpackage   Business\Import
 */
class CancelTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Import 
   */
  protected $business; 
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new ImportBusiness('Import');
  }
  protected function tearDown() 
  {
    DirectoryHelper::clearLatchDirectory();
    parent::tearDown();
  }
  /**
   * @test
   * @group library
   */
  public function cancelAgainstNonExistingLatchDirectoryShouldThrowExpectedException()
  {
    DirectoryHelper::clearLatchDirectory();
    $business = $this->business;

    $fakeImportId = str_repeat('m', LatchBusiness::LATCH_IMPORT_ID_LENGTH);

    $this->assertException(
      function() use (&$business, $fakeImportId) {
        $business->cancelImport($fakeImportId);
      },
      array(),
      'Cms\Exception',
      function($actualException, &$message) {
        $expected = 14;
        if ($actualException->getCode() != $expected) {
          $message = 'Failed asserting that exception code contains '.$actualException->getCode().'. Expected code '.$expected.'.';
          return false;
        }
        $actualMessage = $actualException->getMessage();
        $exceptedMessage = \Cms\Error::getMessageByCode($expected, array(
            'detail' => 'import cache directory doesn\'t exists'
        ));
        if ($exceptedMessage != $actualMessage) {
          $message = 'Failed asserting that exception message "'.$actualMessage.'" contains "'.$exceptedMessage.'".';
          return false;
        }
        return true;
      }
    );
  }
  /**
   * @test
   * @group library
   */
  public function cancelAgainstNonExistingLatchStorageFileShouldThrowExpectedException()
  {
    DirectoryHelper::clearLatchDirectory();
    $business = $this->business;

    $importLatchDirectory = Registry::getConfig()->import->latch->directory;
    mkdir($importLatchDirectory);
    $this->assertFileExists($importLatchDirectory);

    $fakeImportId = str_repeat('m', LatchBusiness::LATCH_IMPORT_ID_LENGTH);

    $this->assertException(
      function() use (&$business, $fakeImportId) {
        $business->cancelImport($fakeImportId);
      },
      array(),
      'Cms\Exception',
      function($actualException, &$message) {
        $expected = 14;
        if ($actualException->getCode() != $expected) {
          $message = 'Failed asserting that exception code contains '.$actualException->getCode().'. Expected code '.$expected.'.';
          return false;
        }
        $actualMessage = $actualException->getMessage();
        $exceptedMessage = \Cms\Error::getMessageByCode($expected, array(
            'detail' => 'import cache file doesn\'t exists'
        ));
        if ($exceptedMessage != $actualMessage) {
          $message = 'Failed asserting that exception message "'.$actualMessage.'" contains "'.$exceptedMessage.'".';
          return false;
        }
        return true;
      }
    );
  }
  /**
   * @test
   * @group library
   */
  public function cancelAgainstExistingButEmptyLatchStorageFileShouldThrowExpectedException()
  {
    DirectoryHelper::clearLatchDirectory();
    $business = $this->business;

    $fakeLatchDirectory = Registry::getConfig()->import->latch->directory;
    mkdir($fakeLatchDirectory);
    $this->assertFileExists($fakeLatchDirectory);
    
    $fakeImportLatchStorageFile = $fakeLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    file_put_contents($fakeImportLatchStorageFile, '', LOCK_EX);
    
    $fakeImportId = str_repeat('m', LatchBusiness::LATCH_IMPORT_ID_LENGTH);

    $this->assertException(
      function() use (&$business, $fakeImportId) {
        $business->cancelImport($fakeImportId);
      },
      array(),
      'Cms\Exception',
      function($actualException, &$message) {
        $expected = 14;
        if ($actualException->getCode() != $expected) {
          $message = 'Failed asserting that exception code contains '.$actualException->getCode().'. Expected code '.$expected.'.';
          return false;
        }
        $actualMessage = $actualException->getMessage();
        $exceptedMessage = \Cms\Error::getMessageByCode($expected, array(
            'detail' => 'import cache file is empty'
        ));
        if ($exceptedMessage != $actualMessage) {
          $message = 'Failed asserting that exception message "'.$actualMessage.'" contains "'.$exceptedMessage.'".';
          return false;
        }
        return true;
      }
    );
  }
  /**
   * @test
   * @group library
   */
  public function cancelAgainstNonExistingImportIdShouldThrowExpectedException()
  {
    DirectoryHelper::clearLatchDirectory();
    $business = $this->business;
    
    $fakeImportId = str_repeat('a', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId1 = str_repeat('b', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId2 = str_repeat('c', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    
    $fakeLatches[$fakeImportId] = 'fake_import_1.zip';
    $fakeLatches[$fakeImportId1] = 'fake_import_2.zip';
    $fakeLatches[$fakeImportId2] = 'fake_import_3.zip';
    
    $this->buildFakeLatchStorage($fakeLatches);
    
    $nonExistingImportId = str_repeat('m', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    

    $this->assertException(
      function() use (&$business, $nonExistingImportId) {
        $business->cancelImport($nonExistingImportId);
      },
      array(),
      'Cms\Exception',
      function($actualException, &$message) use ($nonExistingImportId) {
        $expected = 15;
        if ($actualException->getCode() != $expected) {
          $message = 'Failed asserting that exception code contains '.$actualException->getCode().'. Expected code '.$expected.'.';
          return false;
        }
        $actualMessage = $actualException->getMessage();
        $exceptedMessage = \Cms\Error::getMessageByCode($expected, array('value' => $nonExistingImportId));
        if ($exceptedMessage != $actualMessage) {
          $message = 'Failed asserting that exception message "'.$actualMessage.'" contains "'.$exceptedMessage.'".';
          return false;
        }
        return true;
      }
    );
  }
  /**
   * @param array $latches
   */
  private function buildFakeLatchStorage(array $latches)
  {
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    mkdir($importLatchDirectory);
    
    $this->assertFileExists($importLatchDirectory);
    
    $fakeImportLatchStorageFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    
    $dateTime = new \DateTime;
    $dateTimeFormatted = $dateTime->format('d.m.Y H:i:s');
    
    $fakeImportLatchStorageContent = array();
    foreach ($latches as $id => $filename) {
      $fakeImportFile = $importLatchDirectory 
        . DIRECTORY_SEPARATOR . $filename; 
      file_put_contents($fakeImportFile, '');
      $fakeImportLatchStorageContent[$id] = array(
        'file' => $fakeImportFile,
        'date' => $dateTimeFormatted,
      );
    }
    
    file_put_contents(
      $fakeImportLatchStorageFile, json_encode($fakeImportLatchStorageContent)
    );
  }
}