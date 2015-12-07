<?php
namespace Cms\Service\Modul;

use Cms\Service\Modul as ModuleService;
use Cms\Response;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * GetByIdTest
 *
 * @package      Application
 * @subpackage   Controller
 */

class GetByIdTest extends ServiceTestCase
{
  /**
   * @var string
   */
  private $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';

  /**
   * @test
   * @group library
   */
  public function success()
  {
    // ARRANGE
    $service = $this->getModuleService();
    $expectedModuleId = 'MODUL-0rap62pl-0t4f-23c9-8628-f2cb4136ef45-MODUL';

    // ACT
    $result = $service->getById($expectedModuleId, $this->websiteId);

    // ASSERT
    $this->assertInstanceOf('Cms\Data\Modul', $result);
    $this->assertSame($this->websiteId, $result->getWebsiteId());
    $this->assertSame($expectedModuleId, $result->getId());
  }

  /**
   * @test
   * @group library
   *
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 102
   */
  public function test_getById_throwExceptionIfModuleOnlyExistInGlobalSetAndGlobalSetDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $service = $this->getModuleService();
    $expectedModuleId = 'rz_tests_module_only_global';

    // ACT
    $service->getById($expectedModuleId, $this->websiteId);
  }

  /**
   * @return ModuleService
   */
  protected function getModuleService()
  {
    return new ModuleService('Modul');
  }
}