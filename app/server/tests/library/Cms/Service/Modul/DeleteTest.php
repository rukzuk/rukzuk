<?php
namespace Cms\Service\Modul;

use Cms\Service\Modul as ModulService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;

/**
 * DeleteByIdTest
 *
 * @package      Application
 * @subpackage   Controller
 */

class DeleteTest extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @var string[]
   */
  public $sqlFixtures = array('library_Cms_Service_Module_DeleteTest.json');

  /**
   * @var string
   */
  protected $websiteId = 'SITE-143abc2f-10ac-4e1e-951f-307e4b8765db-SITE';

  /**
   * @var string
   */
  protected $websiteIdWithLocalAndGlobalModules = 'SITE-module00-serv-ice0-test-000000000001-SITE';

  /**
   * @var \Cms\Service\Modul
   */
  private $service;

  public function setUp()
  {
    parent::setUp();
    
    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Module', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });

    $this->service = new ModulService('Modul');
  }

  public function tearDown()
  {
    ModuleWriteableMock::tearDown();
    
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function deleteByIdShouldDeleteExpectedModule()
  {
    $modulId = 'MODUL-5rap62rs-0t4f-42c7-8de8-f2cb4236eb45-MODUL';
    $config = Registry::getConfig();

    $modulIdsAndFiles = array(
      $modulId =>
        array(
          'foo' . DIRECTORY_SEPARATOR . 'delete-1.0.js',
          'boo' . DIRECTORY_SEPARATOR . 'delete-2.0.js',
          'baz' . DIRECTORY_SEPARATOR . 'delete-3.0.js',
          'delete-0.0.js',
        )
    );
    $assertionMessage = 'Creation of test assets failed';
    foreach ($modulIdsAndFiles as $modulId => $files) {
      $this->assertTrue(
        $this->createTestAssets($this->websiteId, $modulId, $files),
        $assertionMessage
      );
    }

    $modules = $this->service->getAll($this->websiteId);
    $countBeforeDelete = count($modules);
    
    $deleteModuleAssetsDirectory = $this->service->getAssetsPath($this->websiteId, $modulId);

    $this->service->delete($modulId, $this->websiteId);

    $modules = $this->service->getAll($this->websiteId);

    $this->assertSame($countBeforeDelete - 1, count($modules));

    $assertionMessage = sprintf(
      "Delete of modul assets directory '%s' failed",
      $deleteModuleAssetsDirectory
    );
    $this->assertFalse(is_dir($deleteModuleAssetsDirectory), $assertionMessage);
  }

  /**
   * @test
   * @group library
   * @expectedException  \Cms\Service\Module\RelationException
   * @expectedExceptionCode 108
   */
  public function deleteByIdShouldRejectDeleteWhenTemplateAssociatedToModule()
  {
    // ARRANGE
    $websiteId = $this->websiteIdWithLocalAndGlobalModules;
    $localAndGlobalModuleId = 'rz_tests_module_only_local';

    // ACT
    $this->service->delete($localAndGlobalModuleId, $websiteId);
  }

  /**
   * @test
   * @group library
   * @expectedException  \Cms\Service\Module\RelationException
   * @expectedExceptionCode 108
   */
  public function deleteByIdShouldRejectDeleteWhenGlobalTemplateAssociatedToModule()
  {
    // ARRANGE
    $websiteId = $this->websiteIdWithLocalAndGlobalModules;
    $localAndGlobalModuleId = 'rz_tests_module_only_global';

    // ACT
    $this->service->delete($localAndGlobalModuleId, $websiteId);
  }

  /**
   * @test
   * @group library
   */
  public function deleteByIdShouldDeleteOverwrittenLocalModuleEvenIfTemplateAssociatedToModule()
  {
    // ARRANGE
    $websiteId = $this->websiteIdWithLocalAndGlobalModules;
    $localAndGlobalModuleId = 'rz_tests_module_local_and_global';

    // ACT
    $this->service->delete($localAndGlobalModuleId, $websiteId);
    $module = $this->service->getById($localAndGlobalModuleId, $websiteId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\Modul', $module);
    $this->assertEquals($localAndGlobalModuleId, $module->getId());
    $this->assertFalse($module->isOverwritten());
  }

  /**
   * @param  string  $websiteId
   * @param  string  $modulId
   * @param  array   $files
   * @return boolean
   */
  protected function createTestAssets($websiteId, $modulId, array $files)
  {
    $config = Registry::getConfig();
    
    $moduleAssetsDirectory = $this->service->getAssetsPath($websiteId, $modulId);

    if (is_dir($moduleAssetsDirectory))
    {
      foreach ($files as $name)
      {
        if (strstr($name, DIRECTORY_SEPARATOR))
        {
          $testAssetDirectory = $moduleAssetsDirectory
            . DIRECTORY_SEPARATOR . dirname($name);
          mkdir($testAssetDirectory);
        }
        $testAssetFile = $moduleAssetsDirectory
          . DIRECTORY_SEPARATOR . $name;
        file_put_contents($testAssetFile, '');
      }
      return true;
    }
    return false;
  }
}