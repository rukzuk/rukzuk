<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Cms\Service\Media\File as FileService,
    Cms\Service\Media\Cache as CacheService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * CopyMediaTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */
class CopyMediaToNewWebsiteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
  }

  /**
   * @test
   * @group library
   */
  public function copyShouldCopyMediaMediafilesAsExpected()
  {
    $sourceWebsiteId = 'SITE-mc10e89c-2rtf-46cd-a651-fc42dc7812so-SITE';
    $newWebsiteId = 'SITE-mc10e89c-2rtf-46cd-a651-fc42dc7f75de-SITE';

    $this->service->copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId);

    $medias = $this->service->getByWebsiteIdAndFilter($newWebsiteId);
    $this->assertTrue(count($medias) === 5);

    $medias = $this->service->getByWebsiteIdAndFilter($sourceWebsiteId);
    $this->assertTrue(count($medias) === 5);

    $config = Registry::getConfig();

    $copiedMediaDirectory = $config->media->files->directory
      . DIRECTORY_SEPARATOR . $newWebsiteId;

    $this->assertTrue(is_dir($copiedMediaDirectory));

    $testFilesDirectory = $config->test->files->directory;
    
    $expectedCopyTreeFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'expected_media_copy_in_aggregating_service.tree';
    $expectedCopyTree = file_get_contents($expectedCopyTreeFile);

    $this->assertSame(
      $expectedCopyTree,
      DirectoryHelper::getRecursiveAsJson($copiedMediaDirectory, true),
      "Tree mismatch between copied directory tree and expected directory tree"
    );

    DirectoryHelper::removeRecursiv($copiedMediaDirectory, $config->media->files->directory);
  }
  /**
   * @test
   * @group library
   */
  public function copyMediaShouldKeepSourceMediaIds()
  {
    $sourceWebsiteId = 'SITE-mc10e89c-2rtf-46cd-a651-fc42dc7812so-SITE';
    $newWebsiteId = 'SITE-mc1fe89c-2rtf-46cd-a651-fc42dc7f75de-SITE';

    $this->service->copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId);
    
    $sourceMedia = $this->service->getByWebsiteIdAndFilter($sourceWebsiteId);

    $sourceMediaIds = array();

    $assertionMessage = 'No expected source media available';
    $this->assertTrue(count($sourceMedia) > 0, $assertionMessage);

    foreach ($sourceMedia as $media)
    {
      $sourceMediaIds[] = $media->getId();
    }

    $copyMedia = $this->service->getByWebsiteIdAndFilter($newWebsiteId);
    $copyMediaIds = array();

    $assertionMessage = 'No expected copy media available';
    $this->assertTrue(count($copyMedia) > 0, $assertionMessage);

    foreach ($copyMedia as $media)
    {
      $copyMediaIds[] = $media->getId();
    }

    sort($sourceMediaIds);
    sort($copyMediaIds);

    $assertionMessage = 'Media ids of source and copied media are not identical';
    $this->assertSame($sourceMediaIds, $copyMediaIds, $assertionMessage);

    $config = Registry::getConfig();
    $copiedMediaDirectory = $config->media->files->directory
      . DIRECTORY_SEPARATOR . $newWebsiteId;
    
    DirectoryHelper::removeRecursiv($copiedMediaDirectory, $config->media->files->directory);
  }
  /**
   * @test
   * @group library
   */
  public function copyMediaShouldBeIgnoredCopyWhenSourceHasNoMedia()
  {
    $sourceWebsiteId = 'SITE-mc10e89c-2cmf-46cd-a651-fc42dc7812so-SITE';
    $newWebsiteId = 'SITE-mc10e89c-2cmf-46cd-a651-fc42dc7f75de-SITE';

    try
    {
      $this->service->copyMediaToNewWebsite($sourceWebsiteId, $newWebsiteId);
    }
    catch (\InvalidArgumentException $e)
    {
      $this->fail('Copy of non existing source media should not occur');
    }
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