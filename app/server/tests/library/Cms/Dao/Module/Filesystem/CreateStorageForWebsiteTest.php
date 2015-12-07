<?php


namespace Cms\Dao\Module\Filesystem;


use Test\Cms\Dao\Module\AbstractFilesystemTestCase;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;

class createStorageForWebsiteTest extends AbstractFilesystemTestCase
{
  protected $newWebsiteId = 'SITE-dao0modu-le00-new0-webs-ite000000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_createStorageForWebsite_success()
  {
    // ARRANGE
    $dao = $this->createModuleDao(true);
    $websiteId = $this->newWebsiteId;
    $expectedModuleDataDirectory = FS::joinPath(
      Registry::getConfig()->item->data->directory,
      $websiteId, $dao::SUBDIR_MODULES);
    FS::rmdir($expectedModuleDataDirectory);
    $this->assertFileNotExists($expectedModuleDataDirectory);

    // ACT
    $dao->createStorageForWebsite($this->newWebsiteId);

    // ASSERT
    $this->assertFileExists($expectedModuleDataDirectory);
  }

}
 