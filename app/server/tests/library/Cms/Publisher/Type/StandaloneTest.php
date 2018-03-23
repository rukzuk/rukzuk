<?php

namespace Cms\Publisher\Type;


use Seitenbau\Registry;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\TransactionTestCase;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;

class StandaloneTest extends TransactionTestCase
{
  protected function setUp()
  {
    DirectoryHelper::clearDirectory($this->getTempDirectory());
    DirectoryHelper::clearDirectory($this->getOutputDirectory());
    parent::setUp();
  }

  /**
   * @test
   * @group small
   * @group library
   */
  public function test_publish_shouldPublishAsExpected()
  {
    // ARRANGE
    ConfigHelper::mergeIntoConfig(array('publisher' => array('type' => 'standalone')));
    $websiteId = 'INTERNAL_THIS_IS_THE_WEBSITE_ID';
    $shortId = 'THIS_IS_THE_SHORT_ID';
    $publishingId = 'INTERNAL_THIS_IS_THE_PUBLISHING_ID';
    $cname = 'internal.my.live.domain.intern';
    $publishingFilePath = FS::joinPath($this->getTestFileDirectory(), 'publishing_file.zip');
    $publishConfig = array(
      'type' => 'internal',
      'cname' => $cname,
      'shortId' => $shortId,
    );
    $serviceUrls = array(
      'download' => '/INTERNAL/service/endpoint/for/download/website/zip',
      'status' => '/INTERNAL/service/endpoint/for/status/request',
    );
    $expectedFilePathname = FS::joinPath($this->getTestFileDirectory(), 'expected_live_tree.json');
    $expectedFileTree = json_decode(FS::readContentFromFile($expectedFilePathname), true);
    $outputLiveDirectory = $this->getOutputDirectoryForShortId($shortId);

    // ACT
    $actualPublishedStatus = $this->getPublisher()->publish($websiteId, $publishingId,
      $publishingFilePath, $publishConfig, $serviceUrls);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\PublisherStatus', $actualPublishedStatus);
    $this->assertSame($actualPublishedStatus::STATUS_FINISHED,
      $actualPublishedStatus->getStatus());
    $actualFileTreeAsJson = DirectoryHelper::getRecursive($outputLiveDirectory);
    $this->assertEquals($expectedFileTree, $actualFileTreeAsJson);
    $this->assertCnameLinksExists($this->getOutputDirectory(), $cname, $outputLiveDirectory);
    $this->assertDirectoryIsEmpty($this->getTempDirectory());
  }

  /**
   * @param string $outputDirectory
   * @param string $cname
   */
  protected function assertCnameLinksExists($outputDirectory, $cname, $targetDirectory)
  {
    $symlinks = array(
      $cname,
    );
    // add non-www link if cname starts with www.
    if (substr($cname, 0, 4) == 'www.') {
        $symlinks[] = substr($cname, 4);
    }
    foreach ($symlinks as $link) {
      $actualTarget = readlink(FS::joinPath($outputDirectory, $link));
      $this->assertEquals($targetDirectory, $actualTarget);
    }
  }

  /**
   * @param string $directory
   */
  protected function assertDirectoryIsEmpty($directory)
  {
    $actualDirectoryTree = DirectoryHelper::getRecursive($directory);
    $this->assertEquals(array(), $actualDirectoryTree);
  }

  /**
   * @return string
   */
  protected function getTestFileDirectory()
  {
    return FS::joinPath(Registry::getConfig()->test->publisher->storage->directory, 'standalone');
  }

  /**
   * @param string $shortId
   *
   * @return string
   */
  protected function getOutputDirectoryForShortId($shortId)
  {
    return FS::joinPath($this->getOutputDirectory(), $shortId);
  }

  /**
   * @return string
   */
  protected function getOutputDirectory()
  {
    return Registry::getConfig()->publisher->standalone->liveHostingDirectory;
  }

  /**
   * @return string
   */
  protected function getTempDirectory()
  {
    return Registry::getConfig()->publisher->standalone->tempDirectory;
  }

  /**
   * @return Standalone
   */
  protected function getPublisher()
  {
    return new Standalone();
  }
}
