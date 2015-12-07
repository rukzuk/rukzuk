<?php
namespace Test\Seitenbau;

use Seitenbau\Registry as Registry,
    Cms\Service\Modul as ModulService,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\Cms\Response as Response;
/**
 * ImportControllerTestCase
 *
 * @package      Test
 * @subpackage   Seitenbau
 */
class ImportControllerTestCase extends ControllerTestCase
{
  /**
   * @var \Cms\Service\Modul
   */
  protected $moduleService = null;
  
  protected function setUp()
  {
    parent::setUp();

    $this->moduleService = new ModulService('Modul');
  }
  
  /**
   * @param string $importFile
   */
  protected function removeImportFileAndUnzipDirectory($importFile)
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    
    if (file_exists($importFile)) {
      unlink($importFile);
    }
    $unzipDirectory = dirname($importFile) 
      . DIRECTORY_SEPARATOR . basename($importFile, '.zip');
    
    if (is_dir($unzipDirectory)) {    
      DirectoryHelper::removeRecursiv($unzipDirectory, $testImportDirectory);
    }
  }
  /**
   * @param  string  $websiteId
   * @return boolean
   */
  protected function removeImportMediaDirectoryAndFiles($websiteId)
  {
    $config = Registry::getConfig();
    $testFilesDirectory = $config->test->files->directory;
    $testMediaDirectory = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . $websiteId;
    if (is_dir($testFilesDirectory) && is_dir($testMediaDirectory))
    {
      DirectoryHelper::removeRecursiv($testMediaDirectory, $testFilesDirectory);
      return true;
    }
    return false;
  }
  /**
   * @param  string  $websiteId
   * @param  string  $modulId
   * @param  array   $files
   * @return boolean
   */
  protected function createTestAssets($websiteId, $modulId, array $files)
  {
    $config = Registry::getConfig();
    
    $moduleAssetsDirectory = $this->moduleService->getAssetsPath($websiteId, $modulId);

    if (is_dir($moduleAssetsDirectory))
    {
      foreach ($files as $name)
      {
        if (strstr($name, DIRECTORY_SEPARATOR))
        {
          $testAssetDirectory = $moduleAssetsDirectory
            . DIRECTORY_SEPARATOR . dirname($name);
          mkdir($testAssetDirectory);
        }
        $testAssetFile = $moduleAssetsDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testAssetFile, '');
      }
      return true;
    }
    return false;
  }
  /**
   * @param string $websiteId
   * @param string $assertionMessage
   */
  protected function assertHasNoMedias($websiteId, $assertionMessage = null)
  {
    $mediaRequestUri = sprintf(
      '/media/get/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($mediaRequestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('media', $responseData);
    $responseMedias = $responseData->media;
    if ($assertionMessage === null)
    {
      $assertionMessage = 'There is already media attached to this website';
    }
    $this->assertTrue(empty($responseMedias), $assertionMessage);
  }
  /**
   * @param string $websiteId
   * @param string $assertionMessage
   */
  protected function assertHasNoTemplates($websiteId, $assertionMessage = null)
  {
    $mediaRequestUri = sprintf(
      '/template/getAll/params/{"websiteid":"%s"}',
      $websiteId
    );
    $this->dispatch($mediaRequestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('templates', $responseData);
    $responseMedias = $responseData->templates;
    if ($assertionMessage === null)
    {
      $assertionMessage = 'There are already templates attached to this website';
    }
    $this->assertTrue(empty($responseMedias), $assertionMessage);
  }
}