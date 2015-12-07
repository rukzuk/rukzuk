<?php


namespace Test\Cms\Service\Package;

use Test\Seitenbau\ServiceTestCase;
use Cms\Dao\Website\GlobalSetSource;
use Cms\Dao\Base\SourceItem;
use Seitenbau\FileSystem as FS;
use Seitenbau\Registry;


/**
 * Class AbstractPackageTestCase
 *
 * @package Test\Cms\Service\Package
 */
class AbstractPackageTestCase extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @param GlobalSetSource $globalSetSource
   *
   * @return \Cms\Service\Package
   */
  protected function getPackageService(GlobalSetSource $globalSetSource = null)
  {
    if (is_null($globalSetSource)) {
      return new \Cms\Service\Package('Package');
    }

    $websiteServiceMock = $this->getMockBuilder('\Cms\Service\Website')
      ->setConstructorArgs(array('Website'))
      ->setMethods(array('getUsedSetSource'))
      ->getMock();
    $websiteServiceMock->expects($this->once())
      ->method('getUsedSetSource')
      ->will($this->returnValue($globalSetSource));

    $serviceMock = $this->getMockBuilder('\Cms\Service\Package')
      ->setConstructorArgs(array('Package'))
      ->setMethods(array('getService'))
      ->getMock();
    $serviceMock->expects($this->once())
      ->method('getService')
      ->with($this->equalTo('Website'))
      ->will($this->returnValue($websiteServiceMock));

    return $serviceMock;
  }

  /**
   * @param string  $websiteId
   * @param string  $globalSetId
   *
   * @return GlobalSetSource
   */
  protected function getGlobalSetSource($websiteId, $globalSetId)
  {
    $config = Registry::getConfig()->item->sets;
    return new GlobalSetSource($websiteId, array(
      new SourceItem($globalSetId, FS::joinPath($config->directory, $globalSetId),
        $config->url . '/' . $globalSetId, SourceItem::SOURCE_REPOSITORY, true, false)));
  }

} 