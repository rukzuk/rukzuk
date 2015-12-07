<?php


namespace Cms\Dao\Module\Filesystem;


use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;

class GetByIdsTest extends AbstractFilesystemTestCase
{
  protected $websiteIdWithLocalModules = 'SITE-module00-dao0-test-0000-000000000001-SITE';
  protected $websiteIdWithoutLocalModules = 'SITE-module00-dao0-test-0no0-locals000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_getByIds_retrieveLocalAndGlobalModules()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModules = array(
      'rz_tests_module_only_local' => 'this is the name: rz_tests_module_only_local (local)',
      'rz_tests_module_local_and_global' => 'this is the name: rz_tests_module_local_and_global (local)',
      'rz_tests_module_only_global' => 'this is the name: rz_tests_module_only_global (global)',
    );
    $expectedModulesIds = array_keys($expectedModules);
    sort($expectedModulesIds);

    // ACT
    $actualModules = $dao->getByIds($moduleSource, $expectedModulesIds);

    // ASSERT
    $actualModuleIds = array();
    foreach ($actualModules as $module) {
      $actualModuleIds[] = $module->getId();
      $this->assertEquals($expectedModules[$module->getId()], $module->getName());
    }
    sort($actualModuleIds);
    $this->assertEquals($expectedModulesIds, $actualModuleIds);
  }

  /**
   * @test
   * @group library
   *
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 102
   */
  public function test_getByIds_throwExceptionIfGlobalModuleNotExist()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test_3', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModulesIds = array(
      'rz_tests_module_only_local',
      'rz_tests_module_local_and_global',
      'rz_tests_module_only_global',
    );

    // ACT
    $dao->getByIds($moduleSource, $expectedModulesIds);
  }
}
 