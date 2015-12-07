<?php
namespace Application\Controller\Import;

use Cms\Business\Import\Latch as LatchBusiness,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ControllerTestCase;

/**
 * ImportController Cancel Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class CancelTest extends ControllerTestCase
{
  protected function tearDown() 
  {
    DirectoryHelper::clearLatchDirectory();
    parent::tearDown();
  }
  /**
   * @test
   * @group integration
   */
  public function cancelShouldReturnValidationErrorForMissingImportId()
  {
    $request = '/import/cancel/params/{}';
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
  public function cancelShouldReturnValidationErrorForInvalidImportIds($importId)
  {
    $request = sprintf(
      '/import/cancel/params/{"importid":"%s"}',
      $importId
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
  public function cancelShouldReturnErrorForNonExistingImportId()
  {
    DirectoryHelper::clearLatchDirectory();
    
    $fakeImportId = str_repeat('a', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId1 = str_repeat('b', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId2 = str_repeat('c', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    
    $fakeLatches[$fakeImportId] = 'fake_import_1.zip';
    $fakeLatches[$fakeImportId1] = 'fake_import_2.zip';
    $fakeLatches[$fakeImportId2] = 'fake_import_3.zip';
    
    $this->buildFakeLatchStorage($fakeLatches);
    
    $nonExistingImportId = str_repeat('d', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    
    $request = sprintf(
      '/import/cancel/params/{"importid":"%s"}',
      $nonExistingImportId
    );
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertFalse($response->getSuccess());
    
    $errorData = $response->getError();
    $expectedErrorText = sprintf(
      'Import Id %s existiert nicht', 
      $nonExistingImportId
    );
    
    $expectedErrorText = \Cms\Error::getMessageByCode(15, array(
      'value' => $nonExistingImportId,
    ));
    $this->assertEquals($expectedErrorText, $errorData[0]->text);
  }
  /**
   * @test
   * @group integration
   */
  public function cancelShouldRemoveImportFileFormFilesystemAndLatchStorageAsExpected()
  {
    DirectoryHelper::clearLatchDirectory();
    
    $fakeImportId = str_repeat('e', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId1 = str_repeat('f', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    $fakeImportId2 = str_repeat('g', LatchBusiness::LATCH_IMPORT_ID_LENGTH);
    
    $fakeLatches[$fakeImportId] = 'fake_import_4.zip';
    $fakeLatches[$fakeImportId1] = 'fake_import_5.zip';
    $fakeLatches[$fakeImportId2] = 'fake_import_6.zip';
    
    $this->buildFakeLatchStorage($fakeLatches);
    
    $request = sprintf(
      '/import/cancel/params/{"importid":"%s"}',
      $fakeImportId1
    );
    $this->dispatch($request);
    
    $response = new Response($this->getResponseBody());
    
    $this->assertInternalType('string', $this->getResponseBody());
    $this->assertNotNull($this->getResponseBody());
    
    $this->assertTrue($response->getSuccess());
    
    $config = Registry::getConfig();
    $importLatchDirectory = $config->import->latch->directory;
    
    $this->assertFileExists($importLatchDirectory);
    
    $fakeImportFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . $fakeLatches[$fakeImportId1];
    $fakeImportLatchStorageFile = $importLatchDirectory 
      . DIRECTORY_SEPARATOR . LatchBusiness::IMPORT_LATCH_STORAGE_FILE;
    
    $this->assertFileNotExists($fakeImportFile);
    $this->assertFileExists($fakeImportLatchStorageFile);
    
    $fakeImportLatchStorageFileContent = json_decode(
      file_get_contents($fakeImportLatchStorageFile),
      true
    );
    $this->assertNotContains(
      $fakeImportId1, array_keys($fakeImportLatchStorageFileContent)
    );
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