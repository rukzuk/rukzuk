<?php


namespace Cms\Dao\Module\Filesystem;


use Cms\Dao\Base\SourceItem;
use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;

class CopyToNewWebsiteTest extends AbstractFilesystemTestCase
{
  protected $fromWebsiteId = 'SITE-module00-dao0-test-0000-000000000001-SITE';
  protected $toWebsiteId = 'SITE-module00-dao0-test-0000-000000000002-SITE';


  protected function setUp()
  {
    parent::setUp();

  }

  /**
   * @test
   * @group library
   */
  public function test_copyToNewWebsite_shouldCopyOnlyLocalModules()
  {
    // ARRANGE
    $dao = $this->createModuleDao(true);
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $fromModuleSource = new ModuleSource($this->fromWebsiteId, $sources);
    $toModuleSource = new ModuleSource($this->toWebsiteId);
    $allFromModules = $dao->getAll($fromModuleSource);
    $allLocalFromModules = array();
    foreach ($allFromModules as $module) {
      if ($module->getSourceType() != SourceItem::SOURCE_LOCAL) {
        continue;
      }
      $allLocalFromModules[$module->getId()] = $module;
    }

    // ACT
    $dao->copyToNewWebsite($fromModuleSource, $toModuleSource);

    // ASSERT
    $allLocalToModules = $dao->getAll($toModuleSource);
    $this->assertCount(count($allLocalFromModules), $allLocalToModules);
    foreach ($allLocalToModules as $actualModule) {
      $this->assertInstanceOf('\Cms\Data\Modul', $actualModule);
      $this->assertArrayHasKey($actualModule->getId(), $allLocalFromModules);
      $this->assertEquals(\Cms\Data\Modul::SOURCE_LOCAL, $actualModule->getSourceType());
    }
  }
}