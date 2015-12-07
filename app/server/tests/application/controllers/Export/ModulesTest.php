<?php
namespace Application\Controller\Export;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\System\Helper as SystemHelper;

/**
 * ExportController Modules Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class ModulesTest extends ControllerTestCase
{
  const EXPORT_FILE_EXTENSION = \Cms\Business\Export::EXPORT_FILE_EXTENSION;

  protected function tearDown()
  {
    $exportDirNames = array(
      'Export_Test_Website_1_module_1306738492',
      'test_export_0_modules',
      'test_export_0_modules_album_id',
      'Export_Test_Website_1_module_1306759604',
      'Export_Test_Website_1_module_1306760005/',
      'Export_Test_Website_1_module_1306760108',
      'test_export_0_website'
    );

    foreach ($exportDirNames as $dirname)
    {
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
   * @group integration
   * @dataProvider invalidNameProvider
   * @param mixed $name
   */
  public function exportModulesShouldReturnValidationErrorForInvalidExportNames($name)
  {
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';
    $moduleIds = array(
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c1-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c2-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c3-MODUL"'
    );
    $requestUri = sprintf(
      'export/modules/params/{"websiteid":"%s","ids":[%s],"name":"%s"}',
      $websiteId,
      implode(',', $moduleIds),
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
   * @dataProvider invalidIdsProvider
   * @param mixed $websiteId
   * @param mixed $moduleIds
   */
  public function exportModulesShouldReturnValidationErrorForInvalidWebsiteIdAndModulIds(
    $websiteId, $moduleIds)
  {
    $requestUri = sprintf(
      'export/modules/params/{"websiteid":"%s","ids":%s}',
      $websiteId,
      $moduleIds
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
  public function exportModulesShouldExportAsExpectedWithoutSpecifiedName()
  {
    $config = Registry::getConfig();
    $exportBaseDirectory = $config->export->directory;
    $exportBaseName = 'Export_Test_Website_1_module';
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';

    $moduleIds = array(
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c1-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c2-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c3-MODUL"'
    );
    $requestUri = sprintf(
      'export/modules/params/{"websiteid":"%s","ids":[%s]}',
      $websiteId,
      implode(',', $moduleIds)
    );

    $this->dispatch($requestUri);

    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();

    $this->assertObjectHasAttribute('url', $responseData);
    $zipFileNameFromResponseJson = str_replace(
      $config->server->url.'/cdn/export/params/',
      '', 
      $responseData->url
    );
    $zipFileNameFromResponseAsArray = json_decode($zipFileNameFromResponseJson, true);
    $zipFileNameFromResponse = $zipFileNameFromResponseAsArray['name'];

    $expectedExportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($zipFileNameFromResponse)
      . DIRECTORY_SEPARATOR . md5($zipFileNameFromResponse)
      . '.' . self::EXPORT_FILE_EXTENSION;

    $exportDirectoriesToRemove = array();
    $exportDirectoriesToRemove[] = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($zipFileNameFromResponse);

    $assertionMessage = 'No export zip file available';
    $this->assertFileExists($expectedExportZipFile);

    $this->assertRegExp('/' . $exportBaseName . '_' . '/', $responseData->url);

    $assertionMessage = 'No export directory available';
    $this->assertTrue(count($exportDirectoriesToRemove) > 0, $assertionMessage);

    foreach ($exportDirectoriesToRemove as $exportDirectoryToRemove)
    {
      DirectoryHelper::removeRecursiv($exportDirectoryToRemove, $exportBaseDirectory);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function exportModulesShouldExportAsExpectedWithSpecifiedName()
  {
    $config = Registry::getConfig();
    $exportDirectoryName = 'test_export_0_modules';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';
    $moduleIds = array(
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c1-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c2-MODUL"',
      '"MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c3-MODUL"'
    );
    $requestUri = sprintf(
      'export/modules/params/{"websiteid":"%s","ids":[%s],"name":"%s"}',
      $websiteId,
      implode(',', $moduleIds),
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

    if (strstr($unzipCommand, $exportBaseDirectory))
    {
      SystemHelper::user_proc_exec($unzipCommand);
      DirectoryHelper::removeRecursiv($exportZipFile, $exportBaseDirectory);
    }

    $testFilesDirectory = $config->test->files->directory;
    $expectedExportTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'trees'
      . DIRECTORY_SEPARATOR . 'export'
      . DIRECTORY_SEPARATOR . 'expected_modules_export.tree';
    
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
   * @return array
   */
  public function invalidIdsProvider()
  {
    return array(
      array('murks', '["MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL", "MODUL-0bin62pr-0t5f-28c9-eg28-f2cb4136ef45-MODUL"]'),
      array('SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE', 'quark'),
      array('murks', 'quark')
    );
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
   * Loescht ein Verzeichnis samt Inhalt (Dateien und Unterordner)
   *
   * @param string $websiteDir
   */
  private function removeDir($dir)
  {
    if (\is_dir($dir))
    {
      $dirHandle = opendir($dir);
      while(($file = \readdir($dirHandle)) !== false)
      {
        if ($file == '.' || $file == '..') continue;
        $handle = $dir . DIRECTORY_SEPARATOR . $file;

        $filetype = filetype($handle);

        if ($filetype == 'dir')
        {
          $this->removeDir($handle);
        }
        else
        {
          unlink($handle);
        }
      }
      closedir($dirHandle);
      rmdir($dir);
    }
  }
  /**
   * @param  string  $var
   * @return boolean
   */
  private function isMd5($var)
  {
    return preg_match('/^[A-Fa-f0-9]{32}$/', $var);
  }
}