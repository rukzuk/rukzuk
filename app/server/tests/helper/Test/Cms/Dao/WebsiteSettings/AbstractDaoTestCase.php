<?php


namespace Test\Cms\Dao\WebsiteSettings;


use Cms\Dao\Base\SourceItem;
use Test\Seitenbau\TransactionTestCase;
use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Test\Seitenbau\Cms\Dao\WebsiteSettings\Filesystem\ReadonlyMock as FilesystemReadonlyDao;
use Test\Seitenbau\Cms\Dao\WebsiteSettings\Doctrine\ReadonlyMock as DoctrineReadonlyDao;
use Test\Seitenbau\Cms\Dao\WebsiteSettings\All\ReadonlyMock as AllReadonlyDao;
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
    return FS::joinPath($this->getGlobalSetDirectory('rukzuk_test'),
      'rz_package_1', 'websiteSettings');
  }

  /**
   * @param string $websiteId
   * @param array  $baseInfo
   *
   * @return WebsiteSettingsSource
   */
  protected function getWebsiteSettingsSource($websiteId = '', array $baseInfo = null)
  {
    $sources = array();
    if (is_array($baseInfo)) {
      foreach ($baseInfo as $data) {
        $baseDirectory = $data[0];
        $subDirectory = $data[1];
        $sourceType = (isset($data[2]) ? $data[2] : SourceItem::SOURCE_UNKNOWN);
        $sources[] = new SourceItem($subDirectory, FS::joinPath($baseDirectory, $subDirectory),
          '/url/to/websiteSettings/'.$subDirectory, $sourceType, true, false);
      }
    }
    return new WebsiteSettingsSource($websiteId, $sources);
  }


  /**
   * @param \Cms\Dao\WebsiteSettings\Filesystem|null $daoFilesystem
   * @param \Cms\Dao\WebsiteSettings\Doctrine|null $daoDoctrine
   *
   * @return AllReadonlyDao
   */
  protected function getAllDao($daoFilesystem = null, $daoDoctrine = null)
  {
    $allDao = new AllReadonlyDao();
    if (!is_null($daoDoctrine)) {
      $allDao->phpunit_setDoctrineDao($daoDoctrine);
    }
    if (!is_null($daoFilesystem)) {
      $allDao->phpunit_setFilesystemDao($daoFilesystem);
    }
    return $allDao;
  }

  /**
   * @return FilesystemReadonlyDao
   */
  protected function getFilesystemDao()
  {
    return new FilesystemReadonlyDao();
  }

  /**
   * @return \Cms\Dao\WebsiteSettings\Filesystem|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getFilesystemDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\WebsiteSettings\Filesystem')->getMock();
  }

  /**
   * @return DoctrineReadonlyDao
   */
  protected function getDoctrineDao()
  {
    return new DoctrineReadonlyDao();
  }

  /**
   * @return \Cms\Dao\WebsiteSettings\Doctrine|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getDoctrineDaoMock()
  {
    return $this->getMockBuilder('\Cms\Dao\WebsiteSettings\Doctrine')->getMock();
  }
}