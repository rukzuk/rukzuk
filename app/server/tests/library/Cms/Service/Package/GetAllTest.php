<?php


namespace Cms\Service\Package;

use Test\Cms\Service\Package\AbstractPackageTestCase;


/**
 * Class GetAllTest
 *
 * @package Cms\Service\Package
 *
 * @group package
 */
class GetAllTest extends AbstractPackageTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function getAllShouldRetrieveExpectedPackages()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $service = $this->getPackageService($this->getGlobalSetSource($websiteId, 'rukzuk_test'));

    $expectedPackageIds = array(
      'rz_package_1',
      'rz_package_2',
      'rz_package_3',
    );

    // ACT
    $allPackages = $service->getAll($websiteId);

    // ASSERT
    $this->assertInternalType('array', $allPackages);
    $this->assertCount(3, $allPackages);
    foreach($allPackages as $actualPackage) {
      $this->assertInstanceOf('\Cms\Data\Package', $actualPackage);
      $this->assertContains($actualPackage->getId() ,$expectedPackageIds);
    }
  }
}