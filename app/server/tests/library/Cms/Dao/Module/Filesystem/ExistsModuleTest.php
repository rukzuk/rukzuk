<?php


namespace Cms\Dao\Module\Filesystem;


use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;

class ExistsModuleTest extends AbstractFilesystemTestCase
{
  protected $websiteIdWithLocalModules = 'SITE-module00-dao0-test-0000-000000000001-SITE';
  protected $websiteIdWithoutLocalModules = 'SITE-module00-dao0-test-0no0-locals000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_existsModule_returnTrueForExistingLocalModule()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModuleId = 'rz_tests_module_only_local';

    // ACT
    $actualModuleExists = $dao->existsModule($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertTrue($actualModuleExists);
  }

  /**
   * @test
   * @group library
   */
  public function test_existsModule_returnTrueForExistingGlobalModule()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModuleId = 'rz_tests_module_only_global';

    // ACT
    $actualModuleExists = $dao->existsModule($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertTrue($actualModuleExists);
  }

  /**
   * @test
   * @group library
   */
  public function test_existsModule_returnTrueForExistingLocalAndGlobalModule()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModuleId = 'rz_tests_module_local_and_global';

    // ACT
    $actualModuleExists = $dao->existsModule($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertTrue($actualModuleExists);
  }

  /**
   * @test
   * @group library
   */
  public function test_existsModule_returnFalseForNotExistingModule()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteIdWithLocalModules, $sources);
    $expectedModuleId = 'rz_tests_module_not_exists';

    // ACT
    $actualModuleExists = $dao->existsModule($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertFalse($actualModuleExists);
  }
}
 