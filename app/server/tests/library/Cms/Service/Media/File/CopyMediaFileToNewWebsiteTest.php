<?php
namespace Cms\Service\Media\File;

use Cms\Service\Media\File as FileService,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * CopyTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */
class CopyMediaFileToNewWebsiteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media\File
   */
  private $service;
  /**
   * @var string
   */
  private $testMediaFilesDirectory;
  /**
   * @var Zend\Config
   */
  private $config;

  protected function setUp()
  {
    parent::setUp();

    $this->config = Registry::getConfig();
    $this->testMediaFilesDirectory = $this->config->media->files->directory;
    $this->service = new FileService($this->testMediaFilesDirectory);
  }
  
  /**
   * @test
   * @group library
   * @expectedException InvalidArgumentException
   */
  public function copyShouldThrowExceptionOnNonExistingSourceDirectory()
  {
    $this->service->copyMediaFileToNewWebsite('NON_EXISTING_SOURCE_DIRECTORY', 'FOO');
  }

  /**
   * @test
   * @group library
   */
  public function copyShouldCopyAsExpected()
  {
    $sourceWebsiteId = 'SITE-mf12copy-20da-4ea8-a477-4ee793source-SITE';
    $destinationWebsiteId = 'SITE-mf12copy-20da-4ea8-a477-4ee7j3f5dest-SITE';
    $this->service->copyMediaFileToNewWebsite($sourceWebsiteId, $destinationWebsiteId);

    $expectedDestinationDirectory = $this->testMediaFilesDirectory
      . DIRECTORY_SEPARATOR . $destinationWebsiteId;

    $assertionMessage = sprintf(
      "Destination directory '%s' has not been created",
      $expectedDestinationDirectory
    );

    $this->assertTrue(
      is_dir($expectedDestinationDirectory),
      $assertionMessage
    );

    $testFilesDirectory = $this->config->test->files->directory;
    
    $expectedCopyTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'expected_media_copy.tree';
    $expectedCopyTree = file_get_contents($expectedCopyTreeFile);

    $this->assertSame(
      $expectedCopyTree,
      DirectoryHelper::getRecursiveAsJson($expectedDestinationDirectory, true),
      "Tree mismatch between copied directory tree and expected directory tree"
    );

    DirectoryHelper::removeRecursiv($expectedDestinationDirectory, $this->testMediaFilesDirectory);
  }

  /**
   * Entfernt den Pfad von tree (man tree) Aufruf Ergebnissen
   *
   * @param  string
   * @return string
   */
  private function removePathPartFromTreeResult($result)
  {
    $tree = explode("\n", $result);
    array_shift($tree);
    return implode("\n", $tree);
  }

}