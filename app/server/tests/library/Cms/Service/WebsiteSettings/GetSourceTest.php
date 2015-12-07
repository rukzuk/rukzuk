<?php


namespace Cms\Service\WebsiteSettings;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Test\Cms\Service\WebsiteSettings\AbstractTestCase;
use Seitenbau\FileSystem as FS;
use Cms\Service\Package as PackageService;


/**
 * Class GetSourceTest
 *
 * @package Cms\Service\WebsiteSettings
 *
 * @group   websiteSettings
 */
class GetSourceTest extends AbstractTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getSource_retrieveExpectedWebsiteSettingsSource()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';

    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $packageServiceMock = $this->getPackageServiceMock($this->getPackages($websiteId, $globalSetDirectory));
    $websiteSettingsService = $this->getMockBuilder('\Cms\Service\WebsiteSettings')
      ->setConstructorArgs(array('WebsiteSettings'))
      ->setMethods(array('getPackageService'))
      ->getMock();
    $websiteSettingsService->expects($this->any())
      ->method('getPackageService')
      ->will($this->returnValue($packageServiceMock));

    $expectedSources = array(
      new SourceItem('rz_shop',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'websiteSettings', 'rz_shop'),
        '/url/to/_packages/rz_package_1/websiteSettings/rz_shop',
        SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_shop_pro',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'websiteSettings', 'rz_shop_pro'),
        '/url/to/_packages/rz_package_1/websiteSettings/rz_shop_pro',
        SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_website_settings_test',
        FS::joinPath($globalSetDirectory, 'rz_package_2', 'websiteSettings', 'rz_website_settings_test'),
        '/url/to/_packages/rz_package_2/websiteSettings/rz_website_settings_test',
        SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualWebsiteSettingsSource = $this->callMethod($websiteSettingsService,
      'getSource', array($websiteId));

    // ASSERT
    $actualSources = $actualWebsiteSettingsSource->getSources();
    $this->assertEquals($expectedSources, $actualSources);
  }

  /**
   * @param array $packages
   *
   * @return PackageService
   */
  protected function getPackageServiceMock(array $packages)
  {
    $serviceMock = $this->getMockBuilder('\Cms\Service\Package')
      ->setConstructorArgs(array('Package'))
      ->setMethods(array('getAll'))
      ->getMock();
    $serviceMock->expects($this->any())
      ->method('getAll')
      ->will($this->returnCallback(function($websiteId) use ($packages) {
        return $packages[$websiteId];
      }));

    return $serviceMock;
  }

  /**
   * @param string $websiteId
   * @param string $baseDirectory
   *
   * @return array
   */
  protected function getPackages($websiteId = '', $baseDirectory = '')
  {
    $packageData = array(
      array(
        'id' => 'rz_package_1',
        'source' => new SourceItem('rz_package_1',
          FS::joinPath($baseDirectory, 'rz_package_1'),
          '/url/to/_packages/rz_package_1', SourceItem::SOURCE_REPOSITORY, true, false),
        'websiteSettings' => array('rz_shop', 'rz_shop_pro'),
      ),
      array(
        'id' => 'rz_package_2',
        'source' => new SourceItem('rz_package_2',
          FS::joinPath($baseDirectory, 'rz_package_2'),
          '/url/to/_packages/rz_package_2', SourceItem::SOURCE_REPOSITORY, true, false),
        'websiteSettings' => array('rz_website_settings_test'),
      )
    );

    $packages = array();
    foreach ($packageData as $data) {
      $package = new \Cms\Data\Package();
      $package->setWebsiteid($websiteId);
      $package->setId($data['id']);
      $package->setWebsiteSettings($data['websiteSettings']);
      $package->setWebsiteSettingsSource($this->getWebsiteSettingsSourceSource($data['source'],
        $data['websiteSettings']));
      $package->setSource($data['source']);
      $package->setSourceType($data['source']->getType());
      $packages[$websiteId][] = $package;
    }

    return $packages;
  }

  /**
   * @param SourceItem $packageSource
   * @param array      $websiteSettings
   *
   * @return array
   */
  protected function getWebsiteSettingsSourceSource(SourceItem $packageSource, array $websiteSettings)
  {
    $sources = array();
    foreach ($websiteSettings as $websiteSettingsId) {
      $sources[] = $this->createSubdirSourceItem($packageSource, $websiteSettingsId, 'websiteSettings');
    }
    return $sources;
  }

  /**
   * @param SourceItem $sourceItem
   * @param string     $newId
   * @param string     $subdirectory
   *
   * @return SourceItem
   */
  public function createSubdirSourceItem(SourceItem $sourceItem, $newId, $subdirectory)
  {
    return new SourceItem($newId,
      FS::joinPath($sourceItem->getDirectory(), $subdirectory, $newId),
      $sourceItem->getUrl() . '/' . $subdirectory . '/' . $newId,
      $sourceItem->getType(), $sourceItem->isReadonly(), false);
  }
}