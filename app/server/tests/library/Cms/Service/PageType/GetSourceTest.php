<?php


namespace Cms\Service\PageType;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Seitenbau\Registry;
use Test\Cms\Service\PageType\AbstractTestCase;
use Seitenbau\FileSystem as FS;
use Cms\Service\Package as PackageService;


/**
 * Class GetSourceTest
 *
 * @package Cms\Service\PageType
 *
 * @group   pageType
 */
class GetSourceTest extends AbstractTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getSource_retrieveExpectedPageTypeSource()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';

    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $packageServiceMock = $this->getPackageServiceMock($this->getPackages($websiteId, $globalSetDirectory));
    $pageTypeService = $this->getMockBuilder('\Cms\Service\PageType')
      ->setConstructorArgs(array('PageType'))
      ->setMethods(array('getPackageService'))
      ->getMock();
    $pageTypeService->expects($this->any())
      ->method('getPackageService')
      ->will($this->returnValue($packageServiceMock));

    $defaultPageTypeConfig = Registry::getConfig()->pageType->defaultPageType;
    $expectedSources = array(
      new SourceItem($defaultPageTypeConfig->id, $defaultPageTypeConfig->directory,
        $defaultPageTypeConfig->url, SourceItem::SOURCE_UNKNOWN, true, false),
      new SourceItem('rz_shop_product',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'pageTypes', 'rz_shop_product'),
        '/url/to/package/rz_package_1/pageTypes/rz_shop_product',
        SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_shop_product_pro',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'pageTypes', 'rz_shop_product_pro'),
        '/url/to/package/rz_package_1/pageTypes/rz_shop_product_pro',
        SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_blog_post',
        FS::joinPath($globalSetDirectory, 'rz_package_3', 'pageTypes', 'rz_blog_post'),
        '/url/to/package/rz_package_3/pageTypes/rz_blog_post',
        SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualPageTypeSource = $this->callMethod($pageTypeService, 'getSource', array($websiteId));

    // ASSERT
    $actualSources = $actualPageTypeSource->getSources();
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
          '/url/to/package/rz_package_1', SourceItem::SOURCE_REPOSITORY, true, false),
        'pageTypes' => array('rz_shop_product', 'rz_shop_product_pro'),
      ),
      array(
        'id' => 'rz_package_3',
        'source' => new SourceItem('rz_package_3',
          FS::joinPath($baseDirectory, 'rz_package_3'),
          '/url/to/package/rz_package_3', SourceItem::SOURCE_REPOSITORY, true, false),
        'pageTypes' => array('rz_blog_post'),
      )
    );

    $packages = array();
    foreach ($packageData as $data) {
      $package = new \Cms\Data\Package();
      $package->setWebsiteid($websiteId);
      $package->setId($data['id']);
      $package->setPageTypes($data['pageTypes']);
      $package->setPageTypesSource($this->getPageTypesSource($data['source'], $data['pageTypes']));
      $package->setSource($data['source']);
      $package->setSourceType($data['source']->getType());
      $packages[$websiteId][] = $package;
    }

    return $packages;
  }

  /**
   * @param SourceItem $packageSource
   * @param array $pageTypes
   *
   * @return array
   */
  protected function getPageTypesSource(SourceItem $packageSource, array $pageTypes)
  {
    $sources = array();
    foreach ($pageTypes as $pageTypeId) {
      $sources[] = $this->createSubdirSourceItem($packageSource, $pageTypeId, 'pageTypes');
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