<?php
namespace Cms\Service\Modul;

use Cms\Service\Modul as ModuleService;
use Cms\Data\Modul as DataModule;
use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Zend_Config;

class ModuleQuotaTest extends ServiceTestCase
{
  const BACKUP_CONFIG = true;

  /**
   * @var string
   */
  private $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';

  protected function setUp()
  {
    parent::setUp();

    // default config override
    $this->updateConfigModuleEnableDev(false);
  }

  /**
   * @test
   * @group                 library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2301
   */
  public function test_checkModuleDevOnService()
  {
    // ARRANGE
    $this->updateConfigModuleEnableDev(false);
    $service = new ModuleService('Modul');

    // ACT
    $service->checkModuleDevelopmentQuota();
  }

  /**
   * @test
   * @group                 library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2301
   */
  public function test_checkCreateNotAllowed()
  {
    // ARRANGE
    $this->updateConfigModuleEnableDev(false);
    $moduleService = $this->getServiceWithDaoMock(false);
    // ACT
    $moduleService->create($this->websiteId, new DataModule());
    // Assert: DAO mock will fail if create called
  }

  /**
   * @test
   * @group                 library
   */
  public function test_checkCreateAllowed()
  {
    // ARRANGE
    $this->updateConfigModuleEnableDev(true);
    $moduleService = $this->getServiceWithDaoMock(true);
    // ACT
    $moduleService->create($this->websiteId, new DataModule());
    // Assert: DAO mock will fail if create called
  }

  protected function updateConfigModuleEnableDev($enable)
  {
    // set quota in config
    $newConfig = new \Zend_Config(Registry::getConfig()->toArray(), true);
    $newConfig->quota->module->enableDev = $enable;
    $newConfig->setReadOnly();
    Registry::setConfig($newConfig);
  }

  protected function getServiceWithDaoMock($allowCallDaoMethods)
  {
    $testService = new ModuleService('Modul');
    $daoMock = $this->getMock('\Cms\Dao\Module');

    // getById
    $daoMock->expects($this->any())
      ->method('getById')
      ->will($this->returnValue($this->getMock('\Cms\Data\Modul')));

    if ($allowCallDaoMethods == true) {
      $daoMock->expects($this->any())->method($this->anything());
    } else {
      $daoMock->expects($this->never())->method($this->anything());
    }

    /** @noinspection PhpParamsInspection */
    $testService->setDao($daoMock);
    return $testService;
  }

}
