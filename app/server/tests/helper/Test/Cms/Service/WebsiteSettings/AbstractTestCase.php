<?php


namespace Test\Cms\Service\WebsiteSettings;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Service\Website as WebsiteService;
use Test\Seitenbau\ServiceTestCase;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;


/**
 * Class AbstractTestCase
 *
 * @package Test\Cms\Service\WebsiteSettings
 */
class AbstractTestCase extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @param WebsiteSettingsSource $websiteSettingsSource
   *
   * @return \Cms\Service\WebsiteSettings
   */
  protected function getWebsiteSettingsService(WebsiteSettingsSource $websiteSettingsSource)
  {
    $serviceMock = $this->getMockBuilder('\Cms\Service\WebsiteSettings')
      ->setConstructorArgs(array('WebsiteSettings'))
      ->setMethods(array('getWebsiteService', 'getSource'))
      ->getMock();
    $serviceMock->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue($websiteSettingsSource));

    return $serviceMock;
  }

  /**
   * @param string $websiteId
   *
   * @return WebsiteSettingsSource
   */
  protected function getWebsiteSettingsSource($websiteId)
  {
    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $pathToPackage1WebsiteSettingsDir = FS::joinPath($globalSetDirectory, 'rz_package_1', 'websiteSettings');
    $pathToPackage2WebsiteSettingsDir = FS::joinPath($globalSetDirectory, 'rz_package_2', 'websiteSettings');

    $sources = array(
      new SourceItem('rz_shop', FS::joinPath($pathToPackage1WebsiteSettingsDir, 'rz_shop'),
        '/url/to/rz_shop', SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_shop_pro', FS::joinPath($pathToPackage1WebsiteSettingsDir, 'rz_shop_pro'),
        '/url/to/rz_shop_pro', SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_website_settings_test',
        FS::joinPath($pathToPackage2WebsiteSettingsDir, 'rz_website_settings_test'),
        '/url/to/rz_website_settings_test', SourceItem::SOURCE_REPOSITORY, true, false),
    );

    return new WebsiteSettingsSource($websiteId, $sources);
  }
}