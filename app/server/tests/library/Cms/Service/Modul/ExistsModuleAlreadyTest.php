<?php
namespace Cms\Service\Modul;

use Cms\Service\Modul as ModuleService;
use Cms\Data\Modul as DataModule;
use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Zend_Config;
use Cms\Dao\Module\Source as ModuleSource;

class ExistsModuleAlreadyTest extends ServiceTestCase
{
  /**
   * @var array
   */
  protected $sqlFixtures = array('library_Cms_Service_Module_ExistsModuleAlreadyTest.json');

  /**
   * @var string
   */
  protected $websiteIdWithLocalModules = 'SITE-module00-serv-ice0-test-000000000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_existsModulAlready_returnTrueForExistingLocalAndGlobalModuleAndGlobalSetDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $service = $this->getModuleService();
    $moduleId = 'rz_tests_module_local_and_global';

    // ACT
    $exists = $service->existsModulAlready($moduleId, $this->websiteIdWithLocalModules);

    // ASSERT
    $this->assertTrue($exists);
  }

  /**
   * @test
   * @group library
   */
  public function test_existsModule_returnTrueForExistingLocalModuleAndGlobalSetDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $service = $this->getModuleService();
    $moduleId = 'rz_tests_module_local_and_global';

    // ACT
    $exists = $service->existsModulAlready($moduleId, $this->websiteIdWithLocalModules);

    // ASSERT
    $this->assertTrue($exists);
  }

  /**
   * @test
   * @group library
   */
  public function test_existsModulAlready_returnFalseForExistingGlobalModuleAndGlobalSetDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $service = $this->getModuleService();
    $moduleId = 'rz_tests_module_only_global';

    // ACT
    $exists = $service->existsModulAlready($moduleId, $this->websiteIdWithLocalModules);

    // ASSERT
    $this->assertFalse($exists);
  }

  /**
   * @return ModuleService
   */
  protected function getModuleService()
  {
    return new ModuleService('Modul');
  }
}
