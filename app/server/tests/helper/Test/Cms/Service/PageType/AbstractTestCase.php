<?php


namespace Test\Cms\Service\PageType;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\PageType\Source as PageTypeSource;
use Cms\Service\Website as WebsiteService;
use Test\Seitenbau\ServiceTestCase;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;


/**
 * Class AbstractTestCase
 *
 * @package Test\Cms\Service\PageType
 */
class AbstractTestCase extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @param PageTypeSource $pageTypeSource
   *
   * @return \Cms\Service\PageType
   */
  protected function getPageTypeService(PageTypeSource $pageTypeSource)
  {
    $serviceMock = $this->getMockBuilder('\Cms\Service\PageType')
      ->setConstructorArgs(array('PageType'))
      ->setMethods(array('getSource'))
      ->getMock();
    $serviceMock->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue($pageTypeSource));

    return $serviceMock;
  }

  /**
   * @param string $websiteId
   *
   * @return PageTypeSource
   */
  protected function getPageTypeSource($websiteId)
  {
    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $pathToPackage1PageTypeDir = FS::joinPath($globalSetDirectory, 'rz_package_1', 'pageTypes');
    $pathToPackage3PageTypeDir = FS::joinPath($globalSetDirectory, 'rz_package_3', 'pageTypes');

    $sources = array(
      new SourceItem('rz_shop_product',
        FS::joinPath($pathToPackage1PageTypeDir, 'rz_shop_product'),
        '/url/to/rz_shop_product', SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_shop_product_pro',
        FS::joinPath($pathToPackage1PageTypeDir, 'rz_shop_product_pro'),
        '/url/to/rz_shop_product_pro', SourceItem::SOURCE_REPOSITORY, true, false),
      new SourceItem('rz_blog_post',
        FS::joinPath($pathToPackage3PageTypeDir, 'rz_blog_post'),
        '/url/to/rz_blog_post', SourceItem::SOURCE_REPOSITORY, true, false),
    );
    return new PageTypeSource($websiteId, $sources);
  }
}