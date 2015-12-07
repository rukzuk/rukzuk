<?php
namespace Application\Controller\Export;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\System\Helper as SystemHelper;

/**
 * ExportController TemplateSnippets Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class TemplateSnippetsTest extends ControllerTestCase
{
  const EXPORT_FILE_EXTENSION = \Cms\Business\Export::EXPORT_FILE_EXTENSION;

  /**
   * @test
   * @group integration
   * @dataProvider invalidNameProvider
   * @param mixed $name
   */
  public function exportTemplateSnippetsShouldReturnValidationErrorForInvalidExportNames($name)
  {
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';
    $templateSnippetIds = array(
      'TPLS-template-snip-pet0-test-000000000021-TPLS',
      'TPLS-template-snip-pet0-test-000000000022-TPLS',
      'TPLS-template-snip-pet0-test-000000000023-TPLS',
    );
    $requestUri = sprintf(
      'export/templatesnippets/params/%s',
      \Zend_Json::encode(array(
        'websiteid' => $websiteId,
        'ids'       => $templateSnippetIds,
        'name'      => $name,
      ))
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
   * @param mixed $templateSnippetIds
   */
  public function exportTemplateSnippetsShouldReturnValidationErrorForInvalidWebsiteIdAndTemplateSnippetIds(
    $websiteId, $templateSnippetIds)
  {
    $requestUri = sprintf(
      'export/templatesnippets/params/{"websiteid":"%s","ids":%s}',
      $websiteId,
      $templateSnippetIds
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
  public function exportTemplateSnippetsShouldExportAsExpectedWithoutSpecifiedName()
  {
    $config = Registry::getConfig();
    $exportBaseDirectory = $config->export->directory;
    $exportBaseName = 'Export_Test_Website_1_templatesnippet';
    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';

    $templateSnippetIds = array(
      'TPLS-template-snip-pet0-test-000000000021-TPLS',
      'TPLS-template-snip-pet0-test-000000000022-TPLS',
      'TPLS-template-snip-pet0-test-000000000023-TPLS',
    );
    $requestUri = sprintf(
      'export/templatesnippets/params/%s',
      \Zend_Json::encode(array(
        'websiteid' => $websiteId,
        'ids'     => $templateSnippetIds,
      ))            
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
  public function exportTemplateSnippetsShouldExportAsExpectedWithSpecifiedName()
  {
    $config = Registry::getConfig();
    $exportDirectoryName = 'test_export_0_templatesnippet';
    $exportBaseDirectory = $config->export->directory;
    $exportDirectory = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName);
    $exportZipFile = $exportBaseDirectory
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . DIRECTORY_SEPARATOR . md5($exportDirectoryName)
      . '.' . self::EXPORT_FILE_EXTENSION;

    $websiteId = 'SITE-ae6e702f-10ac-4e1e-exmo-307e4b8765db-SITE';
    $templateSnippetIds = array(
      'TPLS-template-snip-pet0-test-000000000021-TPLS',
      'TPLS-template-snip-pet0-test-000000000022-TPLS',
      'TPLS-template-snip-pet0-test-000000000023-TPLS',
    );
    $requestUri = sprintf(
      'export/templatesnippets/params/%s',
      \Zend_Json::encode(array(
        'websiteid' => $websiteId,
        'ids'       => $templateSnippetIds,
        'name'      => $exportDirectoryName,
      ))            
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
      . DIRECTORY_SEPARATOR . 'expected_templatesnippets_export.tree';
    
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
      array('murks', '["TPLS-template-snip-pet0-test-000000000021-TPLS", "TPLS-template-snip-pet0-test-000000000022-TPLS"]'),
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
      array(''),
      array($tooLongString)
    );
  }

}