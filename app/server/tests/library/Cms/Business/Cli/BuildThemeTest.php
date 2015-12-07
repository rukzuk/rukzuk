<?php
namespace Cms\Business\Cli;

use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Seitenbau\Registry as Registry;
use Cms\Business\Cli as CliBusiness;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;

class BuildThemeTest extends ServiceTestCase
{
  protected $targetPath;

  protected function setUp()
  {
    parent::setUp();

    DirectoryHelper::clearDirectory($this->getTestTargetPath());
  }

  protected function tearDown()
  {
    DirectoryHelper::clearDirectory($this->getTestTargetPath());

    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function test_buildTheme_buildThemeFilesAsExpected()
  {
    // ARRANGE
    $cliBusiness = $this->getCliBusiness();
    $rawThemeVars = array(
      'color' => '#091929',
      'logo' => 'http://localhost/this/is/the/url/of/the/logo.svg',
      'unusedvar' => 'this is am unused variable'
    );
    $keysOfValuesInThemeFiles = array_intersect_key($rawThemeVars, array_fill_keys(array(
        'color', 'logo'
    ), null));

    // ACT
    $cliBusiness->buildTheme($rawThemeVars);

    // ASSERT
    $this->assertValuesExistsInFiles($keysOfValuesInThemeFiles);
  }

  /**
   * @test
   * @group library
   */
  public function test_buildTheme_removedThemeFilesIfNoValuesGiven()
  {
    // ARRANGE
    $cliBusiness = $this->getCliBusiness();

    // ACT

    // 1. check if theme folder is empty
    $themeFolderEmpty = DirectoryHelper::getRecursive($this->getTestTargetPath());
    $this->assertArrayNotHasKey('children', $themeFolderEmpty);

    // 2. create theme files
    $cliBusiness->buildTheme(array(
      'color' => '#091929',
      'logo' => 'http://localhost/this/is/the/url/of/the/logo.svg',
    ));
    $themeFolder = DirectoryHelper::getRecursive($this->getTestTargetPath());
    $this->assertArrayHasKey('children', $themeFolder);
    $this->assertGreaterThan(0, $themeFolder['children']);

    // 3. reset theme
    $cliBusiness->buildTheme(array());
    $resetThemeFolder = DirectoryHelper::getRecursive($this->getTestTargetPath());

    // ASSERT
    $this->assertArrayNotHasKey('children', $resetThemeFolder);
  }

  /**
   * @param array $expectedValuesInThemeFiles
   *
   * @throws FS\FileSystemException
   */
  protected function assertValuesExistsInFiles(array $expectedValuesInThemeFiles)
  {
    foreach (new \DirectoryIterator($this->getTestTargetPath()) as $fileInfo) {
      if($fileInfo->isDot() || !$fileInfo->isFile()) continue;
      $content = FS::readContentFromFile($fileInfo->getPathname());
      foreach ($expectedValuesInThemeFiles as $key => $searchValue) {
        if (strstr($content, $searchValue) !== false) {
          unset($expectedValuesInThemeFiles[$key]);
        }
      }
    }
    if (count($expectedValuesInThemeFiles) > 0) {
      $this->fail(sprintf("The values of the keys '%s' didn't exists in theme files",
        implode(', ',array_keys($expectedValuesInThemeFiles))
      ));
    }
  }

/**
   * @return CliBusiness
   */
  protected function getCliBusiness()
  {
    return new \Cms\Business\Cli('Cli');
  }

  /**
   * @return string
   */
  private function getTestTargetPath()
  {
    return Registry::getConfig()->theme->sass->target_path;
  }
}