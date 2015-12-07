<?php
namespace Cms\Dao\Module\Filesystem;

use Cms\Dao\Module\Source as ModuleSource;
use Test\Cms\Dao\Module\AbstractFilesystemTestCase;
use Cms\Validator\UniqueId as UniqueIdValidator;

/**
 * GetAll Test
 *
 * @package      Test
 * @subpackage   Dao
 */
class GetAllTest extends AbstractFilesystemTestCase
{
  protected $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';

  /**
   * @test
   * @group library
   */
  public function getAllOnlyRetrieveModulesFromWebsite()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $moduleSource = new ModuleSource($this->websiteId);
    $expectedModuleIds = array(
      'MODUL-0rap5eb8-0df3-47e9-afac-90ae9d96d3c2-MODUL',
      'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-4mrap53m-2af9-4g2f-a4rb-4a93in3f70mu-MODUL',
    );

    // ACT
    $modules = $dao->getAll($moduleSource);

    // ASSERT
    $this->assertEquals(count($expectedModuleIds), count($modules));
    foreach ($modules as $module) {
      $assertionMessage = 'module "'.$module->getId().'" not expected';
      $this->assertTrue(in_array($module->getId(), $expectedModuleIds), $assertionMessage);
      
      if ($module->getId() == 'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL') {
        $this->assertInstanceOf('Cms\Data\Modul', $module);
        $this->assertEquals('Basismodul_Page', $module->getName());
        $this->assertEquals('some_description', $module->getDescription());
        $this->assertEquals(null, $module->getVersion());
        $this->assertEquals('some_icon', $module->getIcon());
        $this->assertEquals(array(), $module->getForm());
        $this->assertEquals(new \stdClass(), $module->getFormvalues());
        $this->assertEquals('some_category', $module->getCategory());
        $this->assertEquals('root', $module->getModuletype());
        $this->assertEquals('*', $module->getAllowedchildmoduletype());
        $this->assertEquals(true, $module->getRerenderrequired());

        $uuidValidator = new UniqueIdValidator(
          \Orm\Data\Modul::ID_PREFIX,
          \Orm\Data\Modul::ID_SUFFIX
        );
        $this->assertTrue($uuidValidator->isValid($module->getId()));
      }
    }
  }

  /**
   * @test
   * @group library
   */
  public function getAllRetrieveModulesFromWebsiteAndModulesFormGlobalModuleSet()
  {
    // ARRANGE
    $dao = $this->createModuleDao();
    $sources = $this->getModuleSourceItemsFromGlobalSetAndPackage('module_test_2', 'rz_testing');
    $moduleSource = new ModuleSource($this->websiteId, $sources);
    $expectedModuleIds = array(
      'MODUL-0bin62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-afac-90ae9d96d3c2-MODUL',
      'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL',
      'MODUL-4mrap53m-2af9-4g2f-a4rb-4a93in3f70mu-MODUL',
      'rz_tests_global_default_module_v1',
    );
    sort($expectedModuleIds);

    // ACT
    $modules = $dao->getAll($moduleSource);

    // ASSERT
    $this->assertInternalType('array', $modules);
    $actualModuleIds = array();
    foreach ($modules as $module) {
      $actualModuleIds[] = $module->getId();
    }
    sort($actualModuleIds);
    $this->assertEquals($expectedModuleIds, $actualModuleIds);
  }

}