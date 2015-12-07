<?php


namespace Test\Cms\Dao\PageType;


use Cms\Dao\Base\SourceItem;
use Test\Seitenbau\TransactionTestCase;
use Cms\Dao\PageType\Source as PageTypeSource;
use Test\Seitenbau\Cms\Dao\PageType\ReadonlyMock as ReadonlyDaoFilesystem;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;



abstract class AbstractDaoTestCase extends TransactionTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @return string
   */
  protected function getBaseDirectory()
  {
    return FS::joinPath($this->getGlobalSetDirectory('rukzuk_test'), 'rz_package_1', 'pageTypes');
  }

  /**
   * @param string $websiteId
   * @param array  $baseInfo
   *
   * @return PageTypeSource
   */
  protected function getPageTypeSource($websiteId = '', array $baseInfo = null)
  {
    $sources = array();
    if (is_array($baseInfo)) {
      foreach ($baseInfo as $data) {
        $baseDirectory = $data[0];
        $subDirectory = $data[1];
        $sourceType = (isset($data[2]) ? $data[2] : SourceItem::SOURCE_UNKNOWN);
        $sources[] = new SourceItem($subDirectory, FS::joinPath($baseDirectory, $subDirectory),
          '/url/to/pageType/'.$subDirectory, $sourceType, true, false);
      }
    }
    return new PageTypeSource($websiteId, $sources);
  }

  /**
   * @return ReadonlyDaoFilesystem
   */
  protected function getFilesystemDao()
  {
    return new ReadonlyDaoFilesystem();
  }

  /**
   * @return \Cms\Dao\PageType\Filesystem|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getFilesystemDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\PageType\Filesystem')->getMock();
  }
}