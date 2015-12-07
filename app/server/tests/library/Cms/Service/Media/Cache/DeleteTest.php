<?php
namespace Cms\Service\Media\Cache;

use Cms\Service\Media\Cache as CacheService,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * DeleteTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class DeleteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media\Cache
   */
  private $service;
  /**
   * @test
   * @group library
   * @expectedException InvalidArgumentException
   */
  public function serviceShouldThrowExceptionOnFaultyConstruction()
  {
    $this->service = new CacheService('foo/bar');
  }
  /**
   * @test
   * @group library
   */
  public function deleteByNameShouldDeleteExpectedMediaCacheFiles()
  {
    $config = Registry::getConfig();
    $testMediaCacheFilesDirectory = $config->media->cache->directory;
    $websiteId = 'SITE-mcde007d-12a5-46cd-a651-fc42dc78fe5f-SITE';

    $mediaCacheFiles = array(
      'rs7dad6afc94668485dbd2d1a27dc3f1.gif',
      'rs7dad6afc94668485dbd2d1a27dc3f1_70x70.gif',
      'rs7dad6afc94668485dbd2d1a27dc3f1_180x180.gif',
      'rs7dad6afc94668485dbd2d1a27dc3f1_100x100.gif',
    );

    $this->assertTrue(
      $this->createTestMediaCaches($websiteId, $mediaCacheFiles)
    );
    
    foreach ($mediaCacheFiles as $mediaCacheFile)
    {
      $deletableMediaCacheFile = $testMediaCacheFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaCacheFile;

      $this->assertFileExists($deletableMediaCacheFile);
    }
    
    $this->service = new CacheService($testMediaCacheFilesDirectory);
    $this->assertTrue(
      $this->service->delete($websiteId, 'rs7dad6afc94668485dbd2d1a27dc3f1.gif')
    );
    
    foreach ($mediaCacheFiles as $mediaCacheFile)
    {
      $deletableMediaCacheFile = $testMediaCacheFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaCacheFile;

      $this->assertFileNotExists($deletableMediaCacheFile);
    }
  }

  /**
   * @param  string $websiteId
   * @param  array  $medias
   * @return boolean
   */
  protected function createTestMediaCaches($websiteId, array $medias)
  {
    $config = Registry::getConfig();
    $testMediaCacheDirectory = $config->media->cache->directory;
    if (is_dir($testMediaCacheDirectory))
    {
      $testWebsiteMediaCacheDirectory = $testMediaCacheDirectory
        . DIRECTORY_SEPARATOR . $websiteId;
      if (!is_dir($testWebsiteMediaCacheDirectory))
      {
        mkdir($testWebsiteMediaCacheDirectory);
      }

      foreach ($medias as $name)
      {
        $testMediaCacheFile = $testWebsiteMediaCacheDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testMediaCacheFile, '');
      }
      return true;
    }
    return false;
  }
}