<?php


namespace Cms\Service\Import;


use Seitenbau\FileSystem as FS;
use Test\Cms\Service\Import\AbstractImportQuotaTestCase;

class ModuleQuotaTest extends AbstractImportQuotaTestCase
{
  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2301
   */
  public function test_importModuleShouldThrowModuleQuotaException()
  {
    // ARRANGE
    $this->setQuotaModuleEnableDev(false);
    $importFilePath = $this->createImportFile(
      'test_service_import_module_quota_only_modules.zip');
    $import = $this->createImportService();

    // ACT
    $import->import('WEBSITE-ID', $importFilePath, null);
  }

  /**
   * @test
   * @group library
   */
  public function test_importModuleSuccessIfModuleDevQuotaEnabled()
  {
    // ARRANGE
    $this->setQuotaModuleEnableDev(true);
    $importFilePath = $this->createImportFile(
      'test_service_import_module_quota_only_modules.zip');
    $import = $this->createImportService();

    // ACT
    $importData = $import->import('WEBSITE-ID', $importFilePath, null);

    // ASSERT
    $this->assertInternalType('array', $importData);
    $this->assertArrayHasKey('website', $importData);
    $this->assertInternalType('array', $importData['website']);
    $this->assertCount(1, $importData['website']);
    $this->assertArrayHasKey('modules', $importData);
    $this->assertInternalType('array', $importData['modules']);
    $this->assertCount(1, $importData['modules']);
  }

  /**
   * @test
   * @group library
   */
  public function test_importSuccessIfModuleDevQuotaDisabledAndNoModuleOrPackageInsideImport()
  {
    // ARRANGE
    $this->setQuotaModuleEnableDev(true);
    $importFilePath = $this->createImportFile(
      'test_service_import_module_quota_only_media.zip');
    $import = $this->createImportService();

    // ACT
    $importData = $import->import('WEBSITE-ID', $importFilePath, null);

    // ASSERT
    $this->assertInternalType('array', $importData);
    $this->assertArrayHasKey('website', $importData);
    $this->assertInternalType('array', $importData['website']);
    $this->assertCount(1, $importData['website']);
    $this->assertArrayHasKey('media', $importData);
    $this->assertInternalType('array', $importData['media']);
    $this->assertCount(1, $importData['media']);
    $this->assertArrayNotHasKey('module', $importData);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2301
   */
  public function test_importShouldThrowModuleQuotaExceptionIfAtLeastOnePackageIncluded()
  {
    // ARRANGE
    $this->setQuotaModuleEnableDev(false);
    $importFilePath = $this->createImportFile(
      'test_service_import_development_quota_only_packages.zip', 'test_imports');
    $import = $this->createImportService();

    // ACT
    $import->import('WEBSITE-ID', $importFilePath, null);
  }

  /**
   * @test
   * @group library
   */
  public function test_importSuccessIfModuleDevQuotaEnabledAndAtLeastOnePackageIncluded()
  {
    // ARRANGE
    $this->setQuotaModuleEnableDev(true);
    $importFilePath = $this->createImportFile(
      'test_service_import_development_quota_only_packages.zip', 'test_imports');
    $import = $this->createImportService();

    // ACT
    $importData = $import->import('WEBSITE-ID', $importFilePath, null);

    // ASSERT
    $this->assertInternalType('array', $importData);
    $this->assertArrayHasKey('website', $importData);
    $this->assertInternalType('array', $importData['website']);
    $this->assertCount(1, $importData['website']);
    $this->assertArrayHasKey('packages', $importData);
    $this->assertInternalType('array', $importData['packages']);
    $this->assertCount(2, $importData['packages']);
  }
}
