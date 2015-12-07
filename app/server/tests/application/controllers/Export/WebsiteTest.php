<?php
namespace Application\Controller\Export;

use Seitenbau\Registry as Registry,
  Test\Seitenbau\Cms\Response as Response,
  Test\Seitenbau\ControllerTestCase,
  Test\Seitenbau\Directory\Helper as DirectoryHelper,
  Test\Seitenbau\System\Helper as SystemHelper;
use Seitenbau\FileSystem as FS;

/**
 * ExportController Website Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class WebsiteTest extends ControllerTestCase
{
  const EXPORT_FILE_EXTENSION = \Cms\Business\Export::EXPORT_FILE_EXTENSION;

  protected $sqlFixtures = array('ExportWebsiteController.json');

  protected function tearDown()
  {
    $exportDirNames = array(
      'test_export_0_modules',
      'test_export_0_modules_album_id',
      'test_export_0_pages',
      'test_export_0_templates',
      'test_export_0_templates_with_album',
      'test_export_0_templates_with_usergroup',
      'test_export_0_website',
      'test_export_0_website_with_album',
      'Export_Test_Website_1_module_1306501297'
    );

    foreach ($exportDirNames as $dirname) {
      $exportDir = \Seitenbau\Registry::getConfig()->export->directory .
        DIRECTORY_SEPARATOR .
        $dirname .
        DIRECTORY_SEPARATOR;
      $this->removeDir($exportDir);
    }

    parent::tearDown();
  }

  /**
   * @test
   * @group        integration
   * @dataProvider invalidNameProvider
   *
   * @param mixed $name
   */
  public function exportWebsiteShouldReturnValidationErrorForInvalidExportNames($name)
  {
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';
    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $name
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);

    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldReturnValidationErrorForInvalidWebsiteId()
  {
    $invalidWebsiteId = 'invalid_website_id';
    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s"}',
      $invalidWebsiteId
    );

    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
  }

  /**
   * @test
   * @group        integration
   * @dataProvider invalidCompleteValueProvider
   */
  public function exportWebsiteShouldReturnValidationErrorForNonBooleanCompleteValue($completeValue)
  {
    $exportDirectoryName = 'test_export_0_website_complete';
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwc-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s","complete":"%s"}',
      $websiteId,
      $exportDirectoryName,
      $completeValue
    );

    $this->dispatch($requestUri);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('complete', $responseError[0]->param->field);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldExportAsExpectedWithActivatedComplete()
  {
    $config = Registry::getConfig();
    $exportDirectoryName = 'test_export_0_website_complete';

    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;

    $websiteId = 'SITE-co6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s","complete":true}',
      $websiteId,
      $exportDirectoryName
    );

    $this->dispatch($requestUri);

    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('url', $responseData);

    $nameDataPart = sprintf(
      '{"name":"%s"}', $exportDirectoryName
    );
    $this->assertStringEndsWith($nameDataPart, $responseData->url);
    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }
    $testFilesDirectory = $config->test->files->directory;
    $expectedExportTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'trees'
      . DIRECTORY_SEPARATOR . 'export'
      . DIRECTORY_SEPARATOR . 'expected_export_website_complete.tree';
    $expectedExportTree = file_get_contents($expectedExportTreeFile);

    $exportDirectoryTree = DirectoryHelper::getRecursiveAsJson($exportDirectory, true);

    $this->assertSame(
      $expectedExportTree,
      $exportDirectoryTree,
      "Tree mismatch between export directory tree and expected directory tree"
    );

    $expectedAlbumJsonContentFile = $config->test->json->storage->directory
      . DIRECTORY_SEPARATOR . 'expected_album_json_complete_export.json';
    $expectedAlbumJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . 'album.json';
    $assertionMessage = sprintf(
      "Expected album.json (%s) file doesn't exists",
      $expectedAlbumJsonFile
    );
    $this->assertFileExists($expectedAlbumJsonFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Json file (%s) with expected album.json content doesn't exists",
      $expectedAlbumJsonContentFile
    );
    $this->assertFileExists($expectedAlbumJsonContentFile, $assertionMessage);
    $assertionTemplate = "The content of expected album.json (%s) isn't "
      . "equal to the content of the actual album.json (%s)";
    $assertionMessage = sprintf(
      $assertionTemplate,
      $expectedAlbumJsonContentFile,
      $expectedAlbumJsonFile
    );
    $this->assertFileEquals($expectedAlbumJsonContentFile, $expectedAlbumJsonFile);

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldExportAsExpectedWithDeactivatedComplete()
  {
    $config = Registry::getConfig();

    $exportDirectoryName = 'test_export_0_website';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s","complete":false}',
      $websiteId,
      $exportDirectoryName
    );

    $this->dispatch($requestUri);

    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('url', $responseData);

    $nameDataPart = sprintf(
      '{"name":"%s"}', $exportDirectoryName
    );
    $this->assertStringEndsWith($nameDataPart, $responseData->url);
    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }
    $testFilesDirectory = $config->test->files->directory;
    $expectedExportTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'trees'
      . DIRECTORY_SEPARATOR . 'export'
      . DIRECTORY_SEPARATOR . 'expected_website_export.tree';
    $expectedExportTree = file_get_contents($expectedExportTreeFile);

    $exportDirectoryTree = DirectoryHelper::getRecursiveAsJson($exportDirectory, true);

    $this->assertSame(
      $expectedExportTree,
      $exportDirectoryTree,
      "Tree mismatch between export directory tree and expected directory tree"
    );

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldCreateExpectedUsergroupJson()
  {
    $config = Registry::getConfig();

    $exportDirectoryName = 'test_export_0_website_with_album';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;
    $expectedUsergroupJsonContentFile = $config->test->json->storage->directory
      . DIRECTORY_SEPARATOR . 'expected_usergroup.json';

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $exportDirectoryName
    );

    $this->dispatch($requestUri);

    $this->getValidatedSuccessResponse();

    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $expectedUsergroupJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'usergroup.json';
    $assertionMessage = sprintf(
      "Expected usergroup.json (%s) file doesn't exists",
      $expectedUsergroupJsonFile
    );
    $this->assertFileExists($expectedUsergroupJsonFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Json file (%s) with expected usergroup.json content doesn't exists",
      $expectedUsergroupJsonContentFile
    );
    $this->assertFileExists($expectedUsergroupJsonContentFile, $assertionMessage);
    $this->assertFileEquals($expectedUsergroupJsonContentFile, $expectedUsergroupJsonFile);

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldCreateExpectedAlbumJson()
  {
    $config = Registry::getConfig();

    $exportDirectoryName = 'test_export_0_website_with_album';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;
    $expectedAlbumJsonContentFile = $config->test->json->storage->directory
      . DIRECTORY_SEPARATOR . 'expected_album.json';

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $exportDirectoryName
    );

    $this->dispatch($requestUri);

    $this->getValidatedSuccessResponse();

    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $expectedAlbumJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . 'album.json';
    $assertionMessage = sprintf(
      "Expected album.json (%s) file doesn't exists",
      $expectedAlbumJsonFile
    );
    $this->assertFileExists($expectedAlbumJsonFile, $assertionMessage);

    $assertionMessage = sprintf(
      "Json file (%s) with expected album.json content doesn't exists",
      $expectedAlbumJsonContentFile
    );
    $this->assertFileExists($expectedAlbumJsonContentFile, $assertionMessage);
    $assertionTemplate = "The content of expected album.json (%s) isn't "
      . "equal to the content of the actual album.json (%s)";
    $assertionMessage = sprintf(
      $assertionTemplate,
      $expectedAlbumJsonContentFile,
      $expectedAlbumJsonFile
    );
    $this->assertFileEquals($expectedAlbumJsonContentFile, $expectedAlbumJsonFile);

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportJsonShouldContainUserEmailOfLoggedInUser()
  {
    $config = Registry::getConfig();

    $exportDirectoryName = 'test_export_0_website_with_album';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;
    $expectedExportJsonContentFile = $config->test->json->storage->directory
      . DIRECTORY_SEPARATOR . 'expected_export.json';

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $exportDirectoryName
    );

    $userlogin = 'export.json@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch($requestUri);

    $this->deactivateGroupCheck();

    $this->getValidatedSuccessResponse();

    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $expectedExportJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'export.json';
    $assertionMessage = sprintf(
      "Expected export.json (%s) file doesn't exists",
      $expectedExportJsonFile
    );
    $this->assertFileExists($expectedExportJsonFile, $assertionMessage);

    $actualExportJsonContent = file_get_contents($expectedExportJsonFile);
    $actualExportJsonAsArray = json_decode($actualExportJsonContent, true);

    $expectedExportJsonFields = array(
      'version', 'date', 'websiteId', 'user', 'mode'
    );

    sort($expectedExportJsonFields);

    $actualExportJsonFields = array_keys($actualExportJsonAsArray);

    sort($actualExportJsonFields);

    $this->assertSame($expectedExportJsonFields, $actualExportJsonFields);

    $expectedExportJsonContent = file_get_contents(
      $expectedExportJsonContentFile
    );

    // Alter dynamic \Cms\Version::NUMBER and time values
    $expectedExportJsonContentAsArray = json_decode($expectedExportJsonContent, true);
    $expectedExportJsonContentAsArray['version'] = \Cms\Version::EXPORT;
    $expectedExportJsonContentAsArray['date'] = $actualExportJsonAsArray['date'];
    $expectedExportJsonContentAsArray['user'] = $userlogin;

    $expectedExportJsonContent = json_encode($expectedExportJsonContentAsArray);

    $this->assertSame($expectedExportJsonContent, $actualExportJsonContent);

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldCreateExpectedExportJson()
  {
    $config = Registry::getConfig();

    $exportDirectoryName = 'test_export_0_website_with_album';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;
    $expectedExportJsonContentFile = $config->test->json->storage->directory
      . DIRECTORY_SEPARATOR . 'expected_export.json';

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exwe-307e4b8765db-SITE';

    $requestUri = sprintf(
      'export/website/params/{"websiteid":"%s","name":"%s"}',
      $websiteId,
      $exportDirectoryName
    );

    $this->dispatch($requestUri);

    $this->getValidatedSuccessResponse();

    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $expectedExportJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'export.json';
    $assertionMessage = sprintf(
      "Expected export.json (%s) file doesn't exists",
      $expectedExportJsonFile
    );
    $this->assertFileExists($expectedExportJsonFile, $assertionMessage);

    $actualExportJsonContent = file_get_contents($expectedExportJsonFile);
    $actualExportJsonAsArray = json_decode($actualExportJsonContent, true);

    $expectedExportJsonFields = array(
      'version', 'date', 'websiteId', 'user', 'mode'
    );

    sort($expectedExportJsonFields);

    $actualExportJsonFields = array_keys($actualExportJsonAsArray);

    sort($actualExportJsonFields);

    $this->assertSame($expectedExportJsonFields, $actualExportJsonFields);

    $expectedExportJsonContent = file_get_contents(
      $expectedExportJsonContentFile
    );

    // Alter dynamic \Cms\Version::NUMBER and time values
    $expectedExportJsonContentAsArray = json_decode($expectedExportJsonContent, true);
    $expectedExportJsonContentAsArray['version'] = \Cms\Version::EXPORT;
    $expectedExportJsonContentAsArray['date'] = $actualExportJsonAsArray['date'];

    $expectedExportJsonContent = json_encode($expectedExportJsonContentAsArray);

    $this->assertSame($expectedExportJsonContent, $actualExportJsonContent);

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @test
   * @group integration
   */
  public function exportWebsiteShouldCreateExpectedFiles()
  {
    // ARRANGE
    $websiteId = 'SITE-controll-er0e-xpor-t0we-bsite0000001-SITE';
    $config = Registry::getConfig();
    $expectedJsonFilesDirectory = $config->test->json->storage->directory;
    $testFilesDirectory = $config->test->files->directory;

    $expectedExportedPackagesTree = FS::readContentFromFile(FS::joinPath($testFilesDirectory,
      'trees', 'export', $websiteId, 'packages.tree'));

    $exportDirectoryName = 'test_export_0_website_with_album';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = FS::joinPath($exportBaseDirectory, md5($exportDirectoryName));
    $exportZipFileName = md5($exportDirectoryName) . '.' . self::EXPORT_FILE_EXTENSION;
    $exportZipFile = FS::joinPath($exportDirectory, $exportZipFileName);

    $expectedTemplates = array(
      'TPL-controll-er0e-xpor-t0we-bsite0000001-TPL' => (object)array(
        'id' => 'TPL-controll-er0e-xpor-t0we-bsite0000001-TPL',
        'name' => 'template export 1',
        'content' => json_encode(array((object)array('abc' => 'def'))),
        'pageType' => 'the_page_type_id',
      ),
      'TPL-controll-er0e-xpor-t0we-bsite0000002-TPL' => (object)array(
        'id' => 'TPL-controll-er0e-xpor-t0we-bsite0000002-TPL',
        'name' => 'template export 2',
        'content' => json_encode(array()),
        'pageType' => null,
      ),
    );


    // ACT
    $this->dispatchWithParams('export/website', array(
      'websiteid' => $websiteId,
      'name' => $exportDirectoryName,
    ));


    // ASSERT
    $this->getValidatedSuccessResponse();

    $this->assertTrue(is_dir($exportDirectory));
    $this->assertFileExists($exportZipFile);

    $unzipCommand = sprintf("unzip %s -d %s", $exportZipFile, $exportDirectory);
    if (strstr($unzipCommand, $exportBaseDirectory)) {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $expectedWebsiteJsonFile = FS::joinPath($expectedJsonFilesDirectory, 'expected_website.json');
    $this->assertFileExists($expectedWebsiteJsonFile);
    $this->assertWebsiteJsonCreatedAsExpected($exportDirectory, $expectedWebsiteJsonFile);

    $expectedWebsiteSettingsJsonFile = FS::joinPath($expectedJsonFilesDirectory, 'expected_export_websitesettings.json');
    $this->assertFileExists($expectedWebsiteSettingsJsonFile);
    $this->assertWebsiteSettingsJsonCreatedAsExpected($exportDirectory, $expectedWebsiteSettingsJsonFile);

    $this->assertTemplateJsonCreatedAsExpected($exportDirectory, $expectedTemplates);

    $exportedPackagesDirectory = FS::joinPath($exportDirectory, 'packages');
    $actualExportedPackagesTree = DirectoryHelper::getRecursiveAsJson($exportedPackagesDirectory, true);
    $this->assertSame($actualExportedPackagesTree, $expectedExportedPackagesTree,
      "Tree mismatch between export package directory tree and expected package directory tree"
    );

    DirectoryHelper::removeRecursiv($exportDirectory, $exportBaseDirectory);
  }

  /**
   * @param string $exportDirectory
   * @param string $expectedWebsiteJsonFile
   *
   * @throws \Exception
   */
  protected function assertWebsiteJsonCreatedAsExpected($exportDirectory, $expectedWebsiteJsonFile)
  {
    $expectedWebsiteJson = file_get_contents($expectedWebsiteJsonFile);
    $expectedWebsite = json_decode($expectedWebsiteJson);

    $actualWebsiteJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'website'
      . DIRECTORY_SEPARATOR . 'website.json';
    $this->assertFileExists($actualWebsiteJsonFile, sprintf(
      "Actual website.json (%s) file doesn't exists", $actualWebsiteJsonFile));
    $actualWebsiteJson = file_get_contents($actualWebsiteJsonFile);
    $actualWebsite = json_decode($actualWebsiteJson);

    $this->assertEquals($expectedWebsite, $actualWebsite);
  }


  /**
   * @param string $exportDirectory
   * @param string $expectedWebsiteSettingsJsonFile
   *
   * @throws \Exception
   */
  protected function assertWebsiteSettingsJsonCreatedAsExpected($exportDirectory,
                                                                $expectedWebsiteSettingsJsonFile)
  {
    $expectedWebsiteSettingsJson = file_get_contents($expectedWebsiteSettingsJsonFile);
    $expectedWebsiteSettings = json_decode($expectedWebsiteSettingsJson);

    $actualWebsiteSettingsJsonFile = $exportDirectory
      . DIRECTORY_SEPARATOR . 'website'
      . DIRECTORY_SEPARATOR . 'websitesettings.json';
    $this->assertFileExists($actualWebsiteSettingsJsonFile, sprintf(
      "Actual websitesettings.json (%s) file doesn't exists", $actualWebsiteSettingsJsonFile));
    $actualWebsiteSettingsJson = file_get_contents($actualWebsiteSettingsJsonFile);
    $actualWebsiteSettings = json_decode($actualWebsiteSettingsJson);

    $this->assertEquals($expectedWebsiteSettings, $actualWebsiteSettings);
  }

  /**
   * @param string $exportDirectory
   * @param array  $expectedTemplates
   */
  protected function assertTemplateJsonCreatedAsExpected($exportDirectory, $expectedTemplates)
  {
    foreach ($expectedTemplates as $templateId => $expectedTemplateArray) {
      $actualTemplateJsonFile = FS::joinPath($exportDirectory, 'templates',
        $templateId, 'template.json');
      $this->assertFileExists($actualTemplateJsonFile, sprintf(
        "Actual template.json (%s) file doesn't exists", $templateId));
      $actualTemplateArray = json_decode(file_get_contents($actualTemplateJsonFile));

      $this->assertEquals($expectedTemplateArray, $actualTemplateArray);
    }
  }

  /**
   * @return array
   */
  public function invalidNameProvider()
  {
    $tooLongString = str_repeat('toolongname', 24);
    return array(
      array('a'),
      array(null),
      array($tooLongString)
    );
  }

  /**
   * @return array
   */
  public function invalidCompleteValueProvider()
  {
    return array(
      array('ab'),
      array(null),
      array(18)
    );
  }

  /**
   * Loescht ein Verzeichnis samt Inhalt (Dateien und Unterordner)
   *
   * @param string $websiteDir
   */
  private function removeDir($dir)
  {
    if (\is_dir($dir)) {
      $dirHandle = opendir($dir);
      while (($file = \readdir($dirHandle)) !== false) {
        if ($file == '.' || $file == '..') continue;
        $handle = $dir . DIRECTORY_SEPARATOR . $file;

        $filetype = filetype($handle);

        if ($filetype == 'dir') {
          $this->removeDir($handle);
        } else {
          unlink($handle);
        }
      }
      closedir($dirHandle);
      rmdir($dir);
    }
  }
}