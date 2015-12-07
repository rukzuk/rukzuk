<?php

namespace Cms\Dao\Module;

use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;
use Cms\Data\Modul as DataModule;
use Seitenbau\FileSystem as FS;


class FilesystemTest extends AbstractFilesystemTestCase
{
  const BACKUP_CONFIG = true;

  protected $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';
  protected $websiteIdWithLocalModules = 'SITE-module00-dao0-test-0000-000000000001-SITE';
  protected $websiteIdWithoutLocalModules = 'SITE-module00-dao0-test-0no0-locals000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_getAllModuleBaseInfoSuccess()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test_3', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedAllModuleIds = array(
      'MODUL-0rap5eb8-0df3-47e9-afac-90ae9d96d3c2-MODUL',
      'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-4mrap53m-2af9-4g2f-a4rb-4a93in3f70mu-MODUL',
      'rz_tests_global_default_module_v2',
      'rz_tests_global_extension_module_v2',
      'rz_tests_global_root_module_v2',
    );
    sort($expectedAllModuleIds);
    $method = new \ReflectionMethod(get_class($dao), 'getAllBaseInfo');
    $method->setAccessible(true);

    // ACT
    /** @var \Cms\Dao\Base\SourceItem[] $actualAllModuleInfo */
    $actualAllModuleInfo = $method->invoke($dao, $moduleSource);
    $actualAllModuleIds = array_keys($actualAllModuleInfo);
    sort($actualAllModuleIds);

    // ASSERT
    $this->assertEquals($expectedAllModuleIds, $actualAllModuleIds);
    foreach ($actualAllModuleInfo as $moduleId => $sourceItem) {
      $this->assertInstanceOf('\Cms\Dao\Base\SourceItem', $sourceItem);
      $this->assertNotEmpty($sourceItem->getDirectory());
      $this->assertFileExists($sourceItem->getDirectory());
      $this->assertThat($sourceItem->getType(), $this->logicalOr(
        $this->equalTo($sourceItem::SOURCE_LOCAL),
        $this->equalTo($sourceItem::SOURCE_REPOSITORY)
      ));
    }
  }
}
 