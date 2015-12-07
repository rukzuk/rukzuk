<?php
namespace Cms\Business\Media;

use Cms\Business\Media as MediaBusiness,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Delete Funktionalitaet Cms\Service\Media
 *
 * @package      Cms
 * @subpackage   Business\Media
 */

class DeleteTest extends ServiceTestCase
{
  protected $business;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new MediaBusiness('Media');
  }

  /**
   * @test
   * @group library
   */
  public function deleteShouldDeleteAndReturnReferencedMedias()
  {
    $deletableIds = array(
      'MDB-del1d0ec-cb0f-4961-92bd-765d4aa581a3-MDB',
      'MDB-del24b9d-426d-4998-af19-959c76d46afa-MDB'
    );
    $deletableIdsWithTemplateReference = array(
      'MDB-sf4te24-1a5f-b9fh-92bd-af5d4c4df4a6-MDB'
    );
    $deletableIdsWithPageReference = array(
      'MDB-sf4pa25-1a5f-b9fh-92bd-af5d4c4df4a6-MDB'
    );
    $websiteId = 'SITE-ra15e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $mediaIds = array_merge(
      $deletableIds,
      $deletableIdsWithTemplateReference,
      $deletableIdsWithPageReference
    );
    $nonDeletables = $this->business->delete($mediaIds, $websiteId);
        
    $this->assertInternalType('array', $nonDeletables);

    foreach ($nonDeletables as $nonDeletableId) 
    {
      $this->assertTrue(
           in_array($nonDeletableId, $deletableIdsWithTemplateReference) 
        || in_array($nonDeletableId, $deletableIdsWithPageReference) 
      );
      $this->assertFalse(
           in_array($nonDeletableId, $deletableIds) 
      );
    }
  }

  /**
   * @test
   * @group library
   */
  public function deleteShouldRejectDeleteWhenMediaIsReferencedByTemplate()
  {
    $deletableIdsWithTemplateReference = array(
      'MDB-me4te49-1a5f-b9fh-92bd-af5d4c4df4a6-MDB'
    );
    $websiteId = 'SITE-ra12e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $result = $this->business->delete($deletableIdsWithTemplateReference, $websiteId);
    $this->assertInternalType('array', $result);
    $this->assertTrue(in_array($deletableIdsWithTemplateReference[0], $result));
  }

  /**
   * @test
   * @group library
   */
  public function deleteShouldRejectDeleteWhenMediaIsReferencedByPage()
  {
    $deletableIdsWithPageReference = array(
      'MDB-me4pa49-1a5f-b9fh-92bd-af5d4c4df4a6-MDB'
    );
    $websiteId = 'SITE-ra14e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $result = $this->business->delete($deletableIdsWithPageReference, $websiteId);
    $this->assertInternalType('array', $result);
    $this->assertTrue(in_array($deletableIdsWithPageReference[0], $result));
  }

  /**
   * @test
   * @group library
   */
  public function deleteShouldDeleteMediaFilesAndCacheFiles()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;
    $testMediaCacheFilesDirectory = $config->media->cache->directory;

    $websiteId = 'SITE-ra14e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $deletableIds = array(
      'MDB-d3l1d0mc-cb0f-4961-92bd-765d4aa581a3-MDB',
      'MDB-d2l1d0mc-cb0f-4961-92bd-765d4aa581a3-MDB'
    );
    $mediaFiles = array('d3l1d0mc.jpg', 'd2l1d0mc.png');
    $mediaCacheFiles = array('d3l1d0mc_80x80.jpg', 'd2l1d0mc_120x120.png');

    $this->assertTrue(
      $this->createTestMedias($websiteId, $mediaFiles)
    );
    $this->assertTrue(
      $this->createTestMediaCaches($websiteId, $mediaCacheFiles)
    );

    foreach ($mediaFiles as $mediaFile)
    {
      $deletableMediaFile = $testMediaFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaFile;

      $this->assertFileExists($deletableMediaFile);
    }

    foreach ($mediaCacheFiles as $mediaCacheFile)
    {
      $deletableMediaCacheFile = $testMediaCacheFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaCacheFile;

      $this->assertFileExists($deletableMediaCacheFile);
    }

    $this->business->delete($deletableIds, $websiteId);

    foreach ($mediaFiles as $mediaFile)
    {
      $deletableMediaFile = $testMediaFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaFile;

      $this->assertFileNotExists($deletableMediaFile);
    }

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
  
  /**
   * @param  string $websiteId
   * @param  array  $medias
   * @return boolean
   */
  protected function createTestMedias($websiteId, array $medias)
  {
    $config = Registry::getConfig();
    $testMediaDirectory = $config->media->files->directory;
    if (is_dir($testMediaDirectory))
    {
      $testWebsiteMediaDirectory = $testMediaDirectory
        . DIRECTORY_SEPARATOR . $websiteId;
      if (!is_dir($testWebsiteMediaDirectory))
      {
        mkdir($testWebsiteMediaDirectory);
      }

      foreach ($medias as $name)
      {
        $testMediaFile = $testWebsiteMediaDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testMediaFile, '');
      }
      return true;
    }
    return false;
  }
}