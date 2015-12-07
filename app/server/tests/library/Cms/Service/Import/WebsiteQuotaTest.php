<?php


namespace Cms\Service\Import;


use Seitenbau\FileSystem as FS;
use Test\Cms\Service\Import\AbstractImportQuotaTestCase;

class WebsiteQuotaTest extends AbstractImportQuotaTestCase
{
  /**
   * @test
   * @group                 library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2300
   */
  public function test_importWebsiteShouldThrowWebsiteQuotaException()
  {
    // ARRANGE
    $expectedWebsiteCount = 10;
    $this->setQuotaWebsiteMaxCount($expectedWebsiteCount);
    $importFilePath = $this->createImportFile(
      'test_service_import_website_quota_website.zip');
    $websiteDao = $this->getWebsiteDaoMock($expectedWebsiteCount, true);
    $import = $this->createImportService($websiteDao);

    // ACT
    $import->import('WEBSITE-ID', $importFilePath, null);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2300
   */
  public function test_importModuleShouldThrowWebsiteQuotaException()
  {
    // ARRANGE
    $expectedWebsiteCount = 10;
    $this->setQuotaWebsiteMaxCount($expectedWebsiteCount);
    $importFilePath = $this->createImportFile(
      'test_service_import_website_quota_only_modules.zip');
    $websiteDao = $this->getWebsiteDaoMock($expectedWebsiteCount, false);
    $import = $this->createImportService($websiteDao);

    // ACT
    $import->import('WEBSITE-ID', $importFilePath, null);
  }

  /**
   * @test
   * @group library
   */
  public function test_importModuleSuccessIfWebsiteQuotaNotReached()
  {
    // ARRANGE
    $expectedWebsiteCount = 1000;
    $this->setQuotaWebsiteMaxCount($expectedWebsiteCount);
    $importFilePath = $this->createImportFile(
      'test_service_import_website_quota_only_modules.zip');
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
}
