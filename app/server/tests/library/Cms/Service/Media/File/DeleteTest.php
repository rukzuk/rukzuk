<?php
namespace Cms\Service\Media\File;

use Cms\Service\Media\File as FileService,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * DeleteTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */
class DeleteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media\File
   */
  private $service;

  /**
   * @test
   * @group library
   * @expectedException InvalidArgumentException
   */
  public function serviceShouldThrowExceptionOnFaultyConstruction()
  {
    $this->service = new FileService('foo/bar');
  }
  /**
   * @test
   * @group library
   */
  public function deleteByNameShouldDeleteExpectedMediaFiles()
  {
    $config = Registry::getConfig();
    $testMediaFilesDirectory = $config->media->files->directory;

    $websiteId = 'SITE-mede000d-11a5-46cd-a651-fc42dc78fe5f-SITE';

    $mediaFiles = array(
      'rs7fad6afc94668485dbd2d1a27dc3f0.png',
      'rs7dad6afc94668485dbd2d1a27dc3f1.gif',
      'rs7had6afc94668485dbd2d1a27dc3f2.jpg'
    );

    $this->assertTrue(
      $this->createTestMedias($websiteId, $mediaFiles)
    );

    foreach ($mediaFiles as $mediaFile)
    {
      $deletableMediaFile = $testMediaFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaFile;
      
      $this->assertFileExists($deletableMediaFile);
    }

    $this->service = new FileService($testMediaFilesDirectory);

    foreach ($mediaFiles as $mediaFile)
    {
      $this->assertTrue($this->service->delete($websiteId, $mediaFile));
    }

    foreach ($mediaFiles as $mediaFile)
    {
      $deletableMediaFile = $testMediaFilesDirectory
        . DIRECTORY_SEPARATOR . $websiteId
        . DIRECTORY_SEPARATOR . $mediaFile;

      $this->assertFileNotExists($deletableMediaFile);
    }

  }
}