<?php


namespace Render\InfoStorage\WebsiteInfoStorage;


use Test\Render\InfoStorage\WebsiteInfoStorage\AbstractWebsiteInfoStorageTestCase;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;

/**
 * Test Class for LiveArrayWebsiteInfoStorageTest
 *
 * @package Render\InfoStorage\WebsiteInfoStorage
 */
class LiveArrayWebsiteInfoStorageTest extends AbstractWebsiteInfoStorageTestCase
{
  /**
   * @param string $websiteId
   * @param array  $websiteSettings
   *
   * @return IWebsiteInfoStorage
   */
  protected function getWebsiteInfoStorage($websiteId, array $websiteSettings)
  {
    $websiteSettingsFile = FS::joinPath(
      Registry::getConfig()->test->renderer->websiteinfostorage->directory,
      'websiteSettingsTestData_001.php'
    );
    return new LiveArrayWebsiteInfoStorage($websiteSettingsFile);
  }
}