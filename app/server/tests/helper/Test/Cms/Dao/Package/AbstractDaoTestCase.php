<?php


namespace Test\Cms\Dao\Package;


use Cms\Dao\Base\DynamicSourceItem;
use Test\Seitenbau\TransactionTestCase;
use Cms\Dao\Package\Source as PackageSource;
use Test\Seitenbau\Cms\Dao\Package\ReadonlyMock as ReadonlyDaoFilesystem;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;


abstract class AbstractDaoTestCase extends TransactionTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @param string      $websiteId
   * @param string|null $globalSetDirectory
   *
   * @return PackageSource
   */
  protected function getPackageSource($websiteId = '', $globalSetDirectory = null)
  {
    $sources = array();
    if (!empty($globalSetDirectory)) {
      $sources[] = new DynamicSourceItem($globalSetDirectory, '/url/to/package',
        DynamicSourceItem::SOURCE_REPOSITORY, true, false);
    }
    return new PackageSource($websiteId, $sources);
  }

  /**
   * @return ReadonlyDaoFilesystem
   */
  protected function getFilesystemDao()
  {
    return new ReadonlyDaoFilesystem();
  }
}