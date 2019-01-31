<?php
namespace Application\Controller\Import;

use Cms\Dao\Base\AbstractSourceItem;
use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ImportControllerTestCase,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Cms\Business\Export as ExportBusiness,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
use Seitenbau\FileSystem as FS;

/**
 * ImportController File Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class WebsiteTest extends ImportControllerTestCase
{
  protected function setUp()
  {
    $this->markTestSkipped(
      'TODO: fix test because of new chunk upload'
    );
    parent::setUp();
    MockManager::activateModuleMock(true);
  }

  protected function tearDown()
  {
    $this->clearFakeUpload();
    parent::tearDown();
  }

  /**
   * @test
   * @group  integration
   */
  public function test_import_success()
  {
    // ARRANGE
    $packageService = new \Cms\Service\Package('Package');
    $config = Registry::getConfig();
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_import_website.zip';

    $importFilePath = FS::joinPath($testFilesDirectory, 'test_imports', $testImportFilename);
    $this->assertFileExists($importFilePath);
    $testImportTmpFile = FS::joinPath('', 'tmp', 'phpI2f3im');
    $this->assertFakeUpload($testImportFilename, $importFilePath, $testImportTmpFile);

    $expectedWebsiteNameStartsWith = 'WebsiteImportSuccess';

    $expectedImportedPackages = array(
      'rz_package_import_test_1' => array(
        'id' => 'rz_package_import_test_1',
        'name' => (object)array(
          'de' => 'Test-Package-1',
          'en' => 'Test package 1',
        ),
        'description' => (object)array(
          'de' => '',
          'en' => '',
        ),
        'websiteSettings' => array(),
        'pageTypes' => array(),
        'templateSnippets' => array(),
        'modules' => array(),
      ),
      'rz_package_import_test_2' => array(
        'id' => 'rz_package_import_test_2',
        'name' => (object)array(
          'de' => 'Test-Package-2',
          'en' => 'Test package 2',
        ),
        'description' => (object)array(
          'de' => '',
          'en' => '',
        ),
        'websiteSettings' => array(),
        'pageTypes' => array(),
        'templateSnippets' => array(),
        'modules' => array(),
      ),
    );


    // ACT
    $this->dispatchWithParams('/import/file', array(
      'fileinputname' => $testImportFilename,
    ));


    // ASSERT
    $this->assertHeaderContains('Content-Type', 'text/plain');
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('website', $responseData);
    $this->assertInternalType('array', $responseData->website);
    $this->assertCount(1, $responseData->website);
    $website = array_shift($responseData->website);
    $this->assertObjectHasAttribute('id', $website);
    $websiteId = $website->id;
    $this->assertNotEmpty($websiteId);
    $this->assertObjectHasAttribute('name', $website);
    $this->assertStringStartsWith($expectedWebsiteNameStartsWith, $website->name);

    $this->assertObjectHasAttribute('packages', $responseData);
    $this->assertInternalType('array', $responseData->packages);
    $this->assertCount(count($expectedImportedPackages), $responseData->packages);
    foreach ($responseData->packages as $importedPackage) {
      $this->assertObjectHasAttribute('id', $importedPackage);
      $this->assertArrayHasKey($importedPackage->id, $expectedImportedPackages);
    }
    $this->assertHasPackages($packageService, $websiteId, $expectedImportedPackages);
  }

  /**
   * @param \Cms\Service\Package $packageService
   * @param string               $websiteId
   * @param array                $expectedPackages
   */
  protected function assertHasPackages($packageService, $websiteId, array $expectedPackages)
  {
    $actualAllPackages = $packageService->getAll($websiteId);
    $actualAllLocalPackages = array();
    foreach ($actualAllPackages as $package) {
      if ($package->getSourceType() !== AbstractSourceItem::SOURCE_LOCAL) {
        continue;
      }
      $actualAllLocalPackages[] = $package;
    }

    $this->assertCount(count($expectedPackages), $actualAllLocalPackages);
    foreach ($actualAllLocalPackages as $package) {
      $this->assertArrayHasKey($package->getId(), $expectedPackages);
      $actualPackageAsArray = $package->toArray();
      foreach ($expectedPackages[$package->getId()] as $attributeName => $expectedAttributeValue) {
        $this->assertArrayHasKey($attributeName, $actualPackageAsArray);
        if (is_object($expectedAttributeValue)) {
          $this->assertEquals($actualPackageAsArray[$attributeName], $expectedAttributeValue);
        } else {
          $this->assertSame($actualPackageAsArray[$attributeName], $expectedAttributeValue);
        }
      }
    }
  }

  /**
   * @test
   * @group  integration
   * @dataProvider importAllowedFileTypesProvider
   */
  public function test_importShouldImportAllowedFileTypesAsExpected($importFilePath, $websiteName)
  {
    // ARRANGE
    $requestFileName = 'import';
    $testImportTmpFile = FS::joinPath('', 'tmp', 'phpI2f3im');
    $this->assertFakeUpload($requestFileName, $importFilePath, $testImportTmpFile);

    // ACT
    $this->dispatchWithParams('/import/file', array(
      'fileinputname' => $requestFileName,
    ));

    // ASSERT
    $this->assertHeaderContains('Content-Type', 'text/plain');
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('website', $responseData);
    $this->assertInternalType('array', $responseData->website);
    $this->assertCount(1, $responseData->website);
    $website = array_shift($responseData->website);
    $this->assertObjectHasAttribute('name', $website);
    $this->assertStringStartsWith($websiteName, $website->name);
  }

  /**
   * @return array
   */
  public function importAllowedFileTypesProvider()
  {
    $config = Registry::getConfig();
    $testFilesDirectory = FS::joinPath($config->test->files->directory, 'test_exports');
    return array(array(
      FS::joinPath($testFilesDirectory, 'test_website_import_allowed_type_rukzuk.rukzuk'),
      'import type .rukzuk'
    ), array(
      FS::joinPath($testFilesDirectory, 'test_website_import_allowed_type_zip.zip'),
      'import type .zip'
    ));
  }

  /**
   * @test
   * @group  integration
   * @ticket SBCMS-977
   */
  public function importWebsiteShouldThrowValidationErrorOnNotAllowedType()
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_templatesnippet_export_not_allowed_type.zip';

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

    $websiteId = 'SITE-rs13up2c-exm0-4ea8-a477-4ee79e8e62pa-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s","allowedType":"%s"}',
      $websiteId,
      $fileInputname,
      ExportBusiness::EXPORT_MODE_WEBSITE  // Nur Websites duerfen importiert werden
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedErrorResponse();

    $responseError = $response->getError();
    $this->assertEquals(35, $responseError[0]->code, $response->getRawResponseBody());
    
    $assertionMessage = "Import files weren't removed as expected";
    $this->assertFileNotExists($expectedImportFile, $assertionMessage);
    $this->assertFileNotExists($importUnzipDirectory, $assertionMessage);
  }
  /**
   * @test
   * @group integration
   */
  public function importFileShouldThrowValidationErrorOnInvalidWebsiteId()
  {
    $_FILES = array();
    $invalidWebsiteId = 'invalid_id';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s"}',
      $invalidWebsiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $responseErrorParameterNames = array();
    foreach ($responseError as $data) 
    {
      $responseErrorParameterNames[] = $data->param->field;
    }
    
    $this->assertContains('websiteid', $responseErrorParameterNames);
  }
  
  /**
   * @test
   * @group integration
   */
  public function importFileShouldThrowValidationErrorOnNonExistingUpload()
  {
    $_FILES = array();
    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $responseErrorParameterNames = array();
    foreach ($responseError as $data) 
    {
      $responseErrorParameterNames[] = $data->param->field;
    }
    
    $this->assertContains('fileupload', $responseErrorParameterNames);
  }
  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-866
   */
  public function importValidationFailsWhenImportExtensionsIsNotInConfiguredAllows()
  {
    $formerAllowedImportExtensions = $this->setAllowedImportExtensions(
      array('xml,csv')
    );
    
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportUnzipDirectory = $testImportDirectory 
      . DIRECTORY_SEPARATOR . substr($testImportFilename, 0, -4);
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpJ2f3ie';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $assertionMessage = "Import Request didn't fail as expected";
    $this->assertFalse($response->getSuccess(), $assertionMessage);

    $responseError = $response->getError();
    $responseText = $responseError[0]->text;
    $expectedTextPart = "Import file extension 'zip' is not in configured "
      . "allowed extension(s) [xml,csv]";
    
    $this->assertTrue(strlen(strstr($responseText, $expectedTextPart)) > 0);

    $this->setAllowedImportExtensions($formerAllowedImportExtensions);
  }
  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-866
   */
  public function importValidationFailsWhenAllowedImportExtensionsAreNotConfigured()
  {
    $formerAllowedImportExtensions = $this->setAllowedImportExtensions(array());
    
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportUnzipDirectory = $testImportDirectory 
      . DIRECTORY_SEPARATOR . substr($testImportFilename, 0, -4);
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpJ2f3ie';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $assertionMessage = "Import Request didn't fail as expected";
    $this->assertFalse($response->getSuccess(), $assertionMessage);
    
    $responseError = $response->getError();
    $responseText = $responseError[0]->text;
    $expectedTextPart = "Allowed import file extensions not configured";
    
    $this->assertTrue(strlen(strstr($responseText, $expectedTextPart)) > 0);

    $this->setAllowedImportExtensions($formerAllowedImportExtensions);
  }
  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-866
   */
  public function importValidationFailsWhenNoAllowedImportExtensionsAreConfigured()
  {
    $formerAllowedImportExtensions = $this->unsetAllowedImportExtensions();
    
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportUnzipDirectory = $testImportDirectory 
      . DIRECTORY_SEPARATOR . substr($testImportFilename, 0, -4);
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpJ2f3ie';
    $fileInputname = 'import';
    
    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $assertionMessage = "Import Request didn't fail as expected";
    $this->assertFalse($response->getSuccess(), $assertionMessage);
    
    $responseError = $response->getError();
    $responseText = $responseError[0]->text;
    $expectedTextPart = " Allowed import file types not configured";
    
    $this->assertTrue(strlen(strstr($responseText, $expectedTextPart)) > 0);

    $this->setAllowedImportExtensions($formerAllowedImportExtensions);
  }
  /**
   * @test
   * @group  integration
   * @group  bugs
   * @ticket SBCMS-866
   */
  public function importForCorruptZipFileIsRejected()
  {
    $config = Registry::getConfig();
    
    $nonImportDeleteAfterImportArray = array(
      'import' => array(
        'uploadFile' =>
          array('doNotRename' => 1),
      )
    );
    $nonImportDeleteAfterImportConfig = new \Zend_Config(
      $nonImportDeleteAfterImportArray
    );
    $config->merge($nonImportDeleteAfterImportConfig);
    
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'corrupt_export.zip';
    
    $corruptTestImportFile = $testFilesDirectory 
      . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $corruptTestImportFile
    );
    $this->assertFileExists($corruptTestImportFile, $assertionMessage);
    
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpJ2f3ie';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $corruptTestImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedErrorResponse();

    $responseError = $response->getError();
    $errorCode = $responseError[0]->code;
    $errorText = $responseError[0]->text;
    $this->assertEquals(10, $errorCode);
    $this->assertRegExp('/: Not a zip archive\.$/', $errorText, $response->getRawResponseBody());
    
    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->removeImportFileAndUnzipDirectory($importFile);
  }
  /**
   * @test
   * @group integration
   */
  public function importFilesAreDeletedAfterImport()
  {
    $config = Registry::getConfig();

    $importDeleteAfterImportArray = array(
      'import' => array(
        'delete' => 
          array('after' => 
            array('import' => 1)),
        'uploadFile' =>
          array('doNotRename' => 1),
      )
    );
    $importDeleteAfterImportConfig = new \Zend_Config(
      $importDeleteAfterImportArray
    );
    $config->merge($importDeleteAfterImportConfig);

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportUnzipDirectory = $testImportDirectory 
      . DIRECTORY_SEPARATOR . substr($testImportFilename, 0, -4);
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Entpack Verzeichnis '%s' existiert bereits",
      $expectedImportUnzipDirectory
    );
    $this->assertFalse(is_dir($expectedImportUnzipDirectory), $assertionMessage);
    
    $assertionMessage = sprintf(
      "Hochzuladendes Import file '%s' existiert bereits '%s'",
      $expectedImportFile,
      $testImportDirectory
    );
    $this->assertFileNotExists($expectedImportFile, $assertionMessage);

    
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpJ2f3ie';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $assertionMessage = sprintf(
      "Hochgeladenes Import file '%s' existiert noch in '%s'",
      $expectedImportFile,
      $testImportDirectory
    );
    $this->assertFileNotExists($expectedImportFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Entpack Verzeichnis '%s' des hochgeladenes Import file existiert noch",
      $expectedImportUnzipDirectory
    );
    $this->assertFalse(is_dir($expectedImportUnzipDirectory), $assertionMessage);
  } 
  
  /**
   * @test
   * @group integration
   */
  public function importFileShouldWorkWithDefaultEmptyWebsiteIdInRequest()
  {
    $this->dispatch('/website/getall');
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websites', $responseData);
    $this->assertInternalType('array', $responseData->websites);
    $existingWebsitesCountBeforeImport = count($responseData->websites);
    
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_1_website.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3iw';
    $fileInputname = 'import';

    $defaultEmptyWebsiteId = '-';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $defaultEmptyWebsiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $reponseDataImportedWebsite = $response->getData();
    
    $this->assertTrue(isset($reponseDataImportedWebsite->website[0]));
    $importedWebsiteId = $reponseDataImportedWebsite->website[0]->id;
    
    $this->assertTrue(
      $this->removeImportMediaDirectoryAndFiles($importedWebsiteId)
    );
    
    $this->dispatch('/website/getall');
    $response = $this->getValidatedSuccessResponse();

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websites', $responseData);
    $this->assertInternalType('array', $responseData->websites);
    
    $existingWebsitesCountAfterImport = count($responseData->websites);
    
    $existingWebsiteIdsAfterImport = array();
    foreach ($responseData->websites as $existingWebsite) 
    {
      $this->assertInstanceOf('stdClass', $existingWebsite);
      $this->assertObjectHasAttribute('id', $existingWebsite);
      $existingWebsiteIdsAfterImport[] = $existingWebsite->id;
    }
    $this->assertContains($importedWebsiteId, $existingWebsiteIdsAfterImport);
    $this->assertTrue(
      $existingWebsitesCountAfterImport === $existingWebsitesCountBeforeImport + 1
    );
  }
  
  /**
   * @test
   * @group integration
   */
  public function importFileShouldMoveImportFileIntoImportDirectory()
  {
    $config = Registry::getConfig();
    
    $nonImportDeleteAfterImportArray = array(
      'import' => array(
        'delete' => 
          array('after' => 
            array('import' => 0)),
        'uploadFile' =>
          array('doNotRename' => 1),
      )
    );
    $nonImportDeleteAfterImportConfig = new \Zend_Config(
      $nonImportDeleteAfterImportArray
    );
    $config->merge($nonImportDeleteAfterImportConfig);
    
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3im';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $assertionMessage = sprintf(
      "Hochgeladenes Import file '%s' existiert nicht in '%s'",
      $expectedImportFile,
      $testImportDirectory
    );
    $this->assertFileExists($expectedImportFile, $assertionMessage);

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $assertionMessage = 'Import Request was not successful';
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $this->assertTrue(unlink($expectedImportFile));

    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->removeImportFileAndUnzipDirectory($importFile);
  }

  
  /**
   * @test
   * @group integration
   */
  public function importFileShouldDeleteFilesOnSuccess()
  {
    $config = Registry::getConfig();
    
    $nonImportDeleteAfterImportArray = array(
      'import' => array(
        'delete' => 
          array('after' => 
            array('import' => 1)),
        'uploadFile' =>
          array('doNotRename' => 1),
      )
    );
    $nonImportDeleteAfterImportConfig = new \Zend_Config(
      $nonImportDeleteAfterImportArray
    );
    $config->merge($nonImportDeleteAfterImportConfig);
    
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports' 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $deletedImportFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $deletedImportFileUnzipDirectory = $testImportDirectory 
      . DIRECTORY_SEPARATOR . basename($testImportFilename, '.zip');
    
    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3im';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e64a6-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $this->assertHeaderContains('Content-Type', 'text/plain');

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $assertionMessage = 'Import Request was not successful';
    $this->assertTrue($response->getSuccess(), $assertionMessage);

    $assertionMessage = sprintf(
      "Hochgeladene Importdata '%s' existiert",
      $deletedImportFile
    );
    $this->assertFileNotExists($deletedImportFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Unzip-Verzeichnis '%s' der hochgeladenen Importdatei existiert",
      $deletedImportFileUnzipDirectory
    );
    $this->assertFileNotExists($deletedImportFileUnzipDirectory, $assertionMessage);

    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
  }

  /**
   * @test
   * @group integration
   * @ticket SBCMS-890
   */
  public function importOnExistingWebsiteShouldCreateANewWebsite()
  {
    $existingWebsiteId = 'SITE-im12up2c-20da-4ea8-a477-4ee79e8e62we-SITE';
    
    $requestUri = sprintf(
      '/website/getbyid/params/{"id":"%s"}',
      $existingWebsiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $assertionMessage = sprintf(
      "Seems like the Website '%s' isn't there anymore, though it should be",
      $existingWebsiteId
    );
    $this->assertTrue($response->getSuccess(), $assertionMessage);
    
    $responseDataExitingWebsite = $response->getData();
    
    $this->assertObjectHasAttribute('id', $responseDataExitingWebsite);
    $this->assertEquals($existingWebsiteId, $responseDataExitingWebsite->id);
    
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_website.zip';

    $testImportFile = $testFilesDirectory 
      . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3iw';
    $fileInputname = 'import';

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $existingWebsiteId,
      $fileInputname
    );
        
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');

    $responseDataImportedWebsite = $response->getData();
    
    $this->assertTrue(isset($responseDataImportedWebsite->website[0]));
    
    $this->assertObjectHasAttribute('id', $responseDataImportedWebsite->website[0]);
    $importedWebsiteId = $responseDataImportedWebsite->website[0]->id;
    
    $assertionMessage = 'Import website id is equal to the already existing '
      . 'website id';
    $this->assertNotEquals(
      $existingWebsiteId, 
      $importedWebsiteId,
      $assertionMessage
    );

    $expectedImportedWebsiteNameStart =  'Export_Test_Website_0_Rename';
    $this->assertObjectHasAttribute(
      'name', $responseDataImportedWebsite->website[0]
    );
    
    $assertionMessage = sprintf(
      "Import website name doesn't start with '%s'", 
      $expectedImportedWebsiteNameStart
    );
    $this->assertStringStartsWith(
      $expectedImportedWebsiteNameStart, 
      $responseDataImportedWebsite->website[0]->name,
      $assertionMessage
    );
    
    $importName = $responseDataImportedWebsite->website[0]->name;
    
    $formattedDateTime = substr(strrchr($importName, '_'), 1);
    
    try {
      $dateTime = new \DateTime($formattedDateTime);
    } catch (\Exception $e) {
      $failMessage = sprintf(
        "Last part of import name '%s' isn't a parseable time string",
        $formattedDateTime
      );
      $this->fail($failMessage);
    }
    
    $requestUri = sprintf(
      '/website/getbyid/params/{"id":"%s"}',
      $existingWebsiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $assertionMessage = sprintf(
      "Seems like the Website '%s' isn't there anymore, though it should be",
      $existingWebsiteId
    );
    $this->assertTrue($response->getSuccess(), $assertionMessage);
    
    $this->assertTrue(
      $this->removeImportMediaDirectoryAndFiles($importedWebsiteId)
    );
    
    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->removeImportFileAndUnzipDirectory($importFile);
  }

  /**
   * @test
   * @group integration
   */
  public function importShouldCreateDefaultAlbumAsExpectedAndAssociateAllMediaItemsToIt()
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_website_albumless.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);
    
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3iw';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20da-4ea8-a477-mel79e8e62we-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');

    $responseDataImportedWebsite = $response->getData();
    
    $this->assertTrue(isset($responseDataImportedWebsite->website[0]));
    
    $this->assertObjectHasAttribute('id', $responseDataImportedWebsite->website[0]);
    $importedWebsiteId = $responseDataImportedWebsite->website[0]->id;
    
    $this->assertTrue(
      $this->removeImportMediaDirectoryAndFiles($importedWebsiteId)
    );
    
    $expectedDefaultAlbumName = $config->import->default->album->name;
    $expectedDefaultAlbumWebsiteId = $importedWebsiteId;
    $expectedDefaulAlbumCount = 1;
    
    $getAllAlbumsRequestUri = sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $importedWebsiteId
    );
    
    $this->dispatch($getAllAlbumsRequestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $assertionMessage = 'album/getall request was not successful';
    $this->assertTrue($response->getSuccess(), $assertionMessage);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $albumsOfWebsite = $responseData->albums;
    $this->assertTrue(count($albumsOfWebsite) === $expectedDefaulAlbumCount);
    $assertionMessage = sprintf(
      "The actual default album has not the expected name (%s)",
      $expectedDefaultAlbumName
    );
    $this->assertObjectHasAttribute('name', $albumsOfWebsite[0]);
    $this->assertSame(
      $expectedDefaultAlbumName, 
      $albumsOfWebsite[0]->name,
      $assertionMessage
    );
    $assertionMessage = sprintf(
      "The actual default album has not the expected website id (%s)",
      $expectedDefaultAlbumWebsiteId
    );
    $this->assertObjectHasAttribute('websiteId', $albumsOfWebsite[0]);
    $this->assertSame(
      $expectedDefaultAlbumWebsiteId, 
      $albumsOfWebsite[0]->websiteId
    );
    $this->assertObjectHasAttribute('id', $albumsOfWebsite[0]);
    $albumId = $albumsOfWebsite[0]->id;
    
    $getAllMediaRequestUri = sprintf(
      '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
      $websiteId,
      $albumId
    );
    
    $this->dispatch($getAllMediaRequestUri);
    $response = $this->getResponseBody();
    $response = new Response($response);
    $assertionMessage = 'media/getall request was not successful';
    $this->assertTrue($response->getSuccess(), $assertionMessage);
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertInternalType('array', $responseData->media);
    foreach ($responseData->media as $media) 
    {
      $this->assertInstanceOf('stdClass', $media);
      $this->assertObjectHasAttribute('albumId', $media);
      $this->assertSame($albumId, $media->albumId);
    }
    
    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->removeImportFileAndUnzipDirectory($importFile);
  }
  /**
   * @test
   * @group integration
   */
  public function importShouldCreateUsergroupsAsExpected()
  {
    $expectedGroupCountAfterImport = 3;
    $expectedGroupNamesAfterImport = array(
      'group_export_test_0',
      'group_export_test_1',
      'group_export_test_2'
    );

    $expectedGroupRightsAfterImport[] = array(
      0 => array(
       'area' => 'pages',
       'privilege' => 'subEdit',
       'units' => array(
        'PAGE-163b62or-b045-40ce-8b4e-c795a87a03ex-PAGE',
        'PAGE-163b62or-b046-40ce-8b4e-c795a87a03ex-PAGE'
       )
      ),
      1 => array(
       'area' => 'modules',
       'privilege' => 'all',
       'units' => null
      ),
      2 => array(
       'area' => 'templates',
       'privilege' => 'none',
       'units' => null
      )
    );
    $expectedGroupRightsAfterImport[] = array(
      0 => array(
       'area' => 'pages',
       'privilege' => 'subAll',
       'units' => array(
        'PAGE-163b62or-b045-40ce-8b4e-c795a87a03ex-PAGE',
        'PAGE-163b62or-b046-40ce-8b4e-c795a87a03ex-PAGE'
       )
      ),
      1 => array(
       'area' => 'modules',
       'privilege' => 'none',
       'units' => null
      ),
      2 => array(
       'area' => 'templates',
       'privilege' => 'all',
       'units' => null
      )
    );
    $expectedGroupRightsAfterImport[] = array(
      0 => array(
       'area' => 'website',
       'privilege' => 'publish',
       'units' => null
      ),
      1 => array(
       'area' => 'templates',
       'privilege' => 'all',
       'units' => null
      ),
      2 => array(
       'area' => 'modules',
       'privilege' => 'all',
       'units' => null
      )
    );

    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_website_usergroups.zip';

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
      . DIRECTORY_SEPARATOR . 'phpe9f3iu';
    $fileInputname = 'import';

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      \Cms\Request\Import\File::DEFAULT_EMPTY_WEBSITE_ID,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);
    $response = $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');

    $responseDataImportedWebsite = $response->getData();
    
    $this->assertTrue(isset($responseDataImportedWebsite->website[0]));
    
    $this->assertObjectHasAttribute('id', $responseDataImportedWebsite->website[0]);
    $importedWebsiteId = $responseDataImportedWebsite->website[0]->id;
    
    
    $getAllRequest = sprintf(
      '/group/getall/params/{"websiteId":"%s"}',
      $importedWebsiteId
    );
    $this->dispatch($getAllRequest);
    $response = $this->getValidatedSuccessResponse();

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('groups', $responseData);
    $this->assertInternalType('array', $responseData->groups);

    $actualGroups = $responseData->groups;

    $this->assertSame($expectedGroupCountAfterImport, count($actualGroups));

    $expectedGroupFields = array(
      'id',
      'websiteId',
      'name',
      'rights',
      'users'
    );
    sort($expectedGroupFields);

    $actualGroupNames = $actualGroupRights = array();

    foreach ($actualGroups as $actualGroup)
    {
      $this->assertInstanceOf('stdClass', $actualGroup);
      $actualGroupFields = array_keys(get_object_vars($actualGroup));
      sort($actualGroupFields);
      $this->assertSame($expectedGroupFields, $actualGroupFields);
      $this->assertObjectHasAttribute('websiteId', $actualGroup);
      $this->assertSame($importedWebsiteId, $actualGroup->websiteId);
      $this->assertObjectHasAttribute('users', $actualGroup);
      $this->assertInternalType('array', $actualGroup->users);
      $this->assertObjectHasAttribute('name', $actualGroup);
      $actualGroupNames[] = $actualGroup->name;
      $this->assertObjectHasAttribute('rights', $actualGroup);
      $actualGroupRights[] = $actualGroup->rights;
    }

    sort($expectedGroupNamesAfterImport);
    sort($actualGroupNames);
    $this->assertSame($expectedGroupNamesAfterImport, $actualGroupNames);

    sort($expectedGroupRightsAfterImport);
    sort($actualGroupRights);
    $this->assertSame(
      json_encode($expectedGroupRightsAfterImport), 
      json_encode($actualGroupRights)
    );

    $this->assertTrue(
      $this->removeImportMediaDirectoryAndFiles($importedWebsiteId)
    );

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
  public function importShouldCreateAlbumsAsExpectedAndMediaItemsShouldBeAssosciatedToThem()
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_website_album.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);
    
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3iw';
    $fileInputname = 'import';

    $websiteId = 'SITE-im12up2c-20dr-4ea8-a477-m4e79e8e62we-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedSuccessResponse();
    $this->assertHeaderContains('Content-Type', 'text/plain');

    $responseDataImportedWebsite = $response->getData();
    
    $this->assertTrue(isset($responseDataImportedWebsite->website[0]));
    
    $this->assertObjectHasAttribute('id', $responseDataImportedWebsite->website[0]);
    $importedWebsiteId = $responseDataImportedWebsite->website[0]->id;
    
    $this->assertTrue(
      $this->removeImportMediaDirectoryAndFiles($importedWebsiteId)
    );
    
    $getAllAlbumsRequestUri = sprintf(
      '/album/getall/params/{"websiteid":"%s"}',
      $importedWebsiteId
    );
    
    $this->dispatch($getAllAlbumsRequestUri);
    $response = $this->getValidatedSuccessResponse();
    
    $expectedAlbumCount = 4;
    $expectedAlbumIds = array(
      'ALBUM-ex0wcf0d-acc4-4fdb-dem4-72ebb08780im-ALBUM',
      'ALBUM-ex1wcf0d-acc4-4fdb-dem4-72ebb08780im-ALBUM',
      'ALBUM-ex2wcf0d-acc4-4fdb-dem4-72ebb08780im-ALBUM',
      'ALBUM-ex3wcf0d-acc4-4fdb-dem4-72ebb08780im-ALBUM',
    );
    $expectedAlbumNames = array(
      'import_album_0',
      'import_album_1',
      'import_album_2',
      'import_album_3',
    );
    
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $albumsOfWebsite = $responseData->albums;
    $this->assertTrue(count($albumsOfWebsite) === $expectedAlbumCount);
    
    $actualAlbumNames = $actualAlbumIds = array();
    foreach ($albumsOfWebsite as $album) 
    {
      $this->assertInstanceOf('stdClass', $album);
      $this->assertObjectHasAttribute('websiteId', $album);
      $this->assertSame($importedWebsiteId, $album->websiteId);
      $this->assertObjectHasAttribute('id', $album);
      $actualAlbumIds[] = $album->id;
      $this->assertObjectHasAttribute('name', $album);
      $actualAlbumNames[] = $album->name;
    }
    sort($expectedAlbumIds);
    sort($actualAlbumIds);
    
    $this->assertEquals($expectedAlbumIds, $actualAlbumIds);
    
    sort($expectedAlbumNames);
    sort($actualAlbumNames);
    
    $this->assertEquals($expectedAlbumNames, $actualAlbumNames);
 
    foreach ($actualAlbumIds as $albumId) {
      $getAllMediaRequestUri = sprintf(
        '/media/getall/params/{"websiteid":"%s","albumid":"%s"}',
        $importedWebsiteId,
        $albumId
      );
      $this->dispatch($getAllMediaRequestUri);
      $response = $this->getResponseBody();
      $response = new Response($response);
      $assertionMessage = 'media/getall request was not successful';
      $this->assertTrue($response->getSuccess(), $assertionMessage);
      $responseData = $response->getData();
      $this->assertObjectHasAttribute('media', $responseData);
      $this->assertInternalType('array', $responseData->media);
      $mediasOfWebsiteAndAlbum = $responseData->media;
      foreach ($mediasOfWebsiteAndAlbum as $media) 
      {
        $this->assertInstanceOf('stdClass', $media);
        $this->assertObjectHasAttribute('albumId', $media);
        $this->assertSame($albumId, $media->albumId);
      }
    }
    
    $importFile = $testImportDirectory 
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $this->removeImportFileAndUnzipDirectory($importFile);
  }
  /**
   * @return array
   */
  private function unsetAllowedImportExtensions()
  {
    $config = Registry::getConfig();
    $configuredTypes = $config->import->allowed->types;
    unset($config->import->allowed->types);
    
    $this->assertFalse(isset($config->import->allowed->types));
    
    return explode(',', $configuredTypes);
  }
  /**
   * @param  array $extensions
   * @return array
   */
  private function setAllowedImportExtensions(array $extensions)
  {
    $config = Registry::getConfig();
    $configuredTypes = $config->import->allowed->types;
    
    $modifiedAllowedImportExtensions = array(
      'import' => array(
        'allowed' => array(
          'types' => implode(',', $extensions)
        )
      )
    );
    $modifiedAllowedImportExtensionsConfig = new \Zend_Config(
      $modifiedAllowedImportExtensions
    );
    $config->merge($modifiedAllowedImportExtensionsConfig);
    $this->assertEquals(
      implode(',', $extensions), 
      $config->import->allowed->types
    );
    
    return explode(',', $configuredTypes);
  }
}