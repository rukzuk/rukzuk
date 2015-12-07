<?php


namespace Cms\Dao\Module\Filesystem;


use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;
use Cms\Data\Modul as DataModule;

class GetByIdTest extends AbstractFilesystemTestCase
{
  protected $websiteId = 'SITE-module00-dao0-test-0000-000000000001-SITE';

  /**
   * @test
   * @group library
   */
  public function test_getById_success()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedModuleId = 'rz_tests_module_only_global';

    $expectedModule = new DataModule();
    $expectedModule->setSourceType(DataModule::SOURCE_REPOSITORY);
    $expectedModule->setOverwritten(false);
    $expectedModule->setId($expectedModuleId);
    $expectedModule->setName('this is the name: rz_tests_module_only_global (global)');
    $expectedModule->setDescription('');
    $expectedModule->setVersion('test');
    $expectedModule->setIcon('application.png');
    $expectedModule->setCategory('{"de":"Default Module","en":"Default Modules"}');
    $expectedModule->setModuletype('default');
    $expectedModule->setAllowedchildmoduletype('*');
    $expectedModule->setRerenderrequired(true);
    $expectedModule->setApiType('APIv1');
    $expectedModule->setSessionRequired(true);

    // ACT
    $module = $dao->getById($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertSame($expectedModule->getId(), $module->getId());
    $this->assertSame($expectedModule->getSourceType(), $module->getSourceType());
    $this->assertSame($expectedModule->isOverwritten(), $module->isOverwritten());
    $this->assertSame($expectedModule->getName(), $module->getName());
    $this->assertSame($expectedModule->getDescription(), $module->getDescription());
    $this->assertSame($expectedModule->getVersion(), $module->getVersion());
    $this->assertSame($expectedModule->getIcon(), $module->getIcon());
    $this->assertSame($expectedModule->getCategory(), $module->getCategory());
    $this->assertSame($expectedModule->getModuletype(), $module->getModuletype());
    $this->assertSame($expectedModule->getAllowedchildmoduletype(), $module->getAllowedchildmoduletype());
    $this->assertSame($expectedModule->getRerenderrequired(), $module->getRerenderrequired());
    $this->assertSame($expectedModule->getApiType(), $module->getApiType());
    $this->assertSame($expectedModule->getSessionRequired(), $module->getSessionRequired());
  }

  /**
   * @test
   * @group library
   */
  public function test_getById_retrieveTheLocalModule()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedModuleId = 'rz_tests_module_only_local';
    $expectedModuleName = 'this is the name: rz_tests_module_only_local (local)';

    // ACT
    $module = $dao->getById($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertEquals($expectedModuleId, $module->getId());
    $this->assertEquals($expectedModuleName, $module->getName());
    $this->assertEquals(DataModule::SOURCE_LOCAL, $module->getSourceType());
    $this->assertFalse($module->isOverwritten());
  }

  /**
   * @test
   * @group library
   */
  public function test_getById_retrieveTheLocalModuleIfNoGlobalSetIsGiven()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $moduleSource = new ModuleSource($this->websiteId, array());
    $expectedModuleId = 'rz_tests_module_local_and_global';
    $expectedModuleName = 'this is the name: rz_tests_module_local_and_global (local)';

    // ACT
    $module = $dao->getById($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertEquals($expectedModuleId, $module->getId());
    $this->assertEquals($expectedModuleName, $module->getName());
    $this->assertEquals(DataModule::SOURCE_LOCAL, $module->getSourceType());
    $this->assertFalse($module->isOverwritten());
  }

  /**
   * @test
   * @group library
   */
  public function test_getById_retrieveTheLocalModuleEvenIfGlobalModuleExists()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedModuleId = 'rz_tests_module_local_and_global';
    $expectedModuleName = 'this is the name: rz_tests_module_local_and_global (local)';

    // ACT
    $module = $dao->getById($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertEquals($expectedModuleId, $module->getId());
    $this->assertEquals($expectedModuleName, $module->getName());
    $this->assertEquals(DataModule::SOURCE_LOCAL, $module->getSourceType());
    $this->assertTrue($module->isOverwritten());
  }

  /**
   * @test
   * @group library
   */
  public function test_getById_retrieveTheGlobalModuleIfModuleDoesNotExistLocal()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedModuleId = 'rz_tests_module_only_global';
    $expectedModuleName = 'this is the name: rz_tests_module_only_global (global)';

    // ACT
    $module = $dao->getById($moduleSource, $expectedModuleId);

    // ASSERT
    $this->assertEquals($expectedModuleId, $module->getId());
    $this->assertEquals($expectedModuleName, $module->getName());
    $this->assertEquals(DataModule::SOURCE_REPOSITORY, $module->getSourceType());
    $this->assertFalse($module->isOverwritten());
  }
}
 