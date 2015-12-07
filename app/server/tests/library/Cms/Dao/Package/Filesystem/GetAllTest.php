<?php


namespace Cms\Dao\Package\Filesystem;

use Cms\Dao\Base\SourceItem;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Test\Cms\Dao\Package\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * Class GetAllTest
 *
 * @package Cms\Dao\Package\Filesystem
 *
 * @group package
 */
class GetAllTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsPackagesAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $globalSetDir = $this->getGlobalSetDirectory('rukzuk_test');
    $source = $this->getPackageSource('WEBSITE-ID', $globalSetDir);
    $expectedPackages = array(
      'rz_package_1' => array(
        'name' => (object) array(
          'en' => 'rz_package_1.name',
        ),
        'description' => (object) array(
          'en' => 'rz_package_1.description',
        ),
        'version' => 'rz_package_1.version',
        'websiteSettings' => array('rz_shop', 'rz_shop_pro'),
        'pageTypes' => array('rz_shop_product', 'rz_shop_product_pro'),
        'readonly' => true,
        'sourceType' => 'repo',
        'source' => new SourceItem('rz_package_1',
          FS::joinPath($globalSetDir, 'rz_package_1'),
          '/url/to/package/rz_package_1', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_package_2' => array(
        'name' => (object) array(
          'en' => 'rz_package_2.name',
        ),
        'description' => null,
        'version' => null,
        'websiteSettings' => array('rz_website_settings_test'),
        'pageTypes' => array(),
        'readonly' => true,
        'sourceType' => 'repo',
        'source' => new SourceItem('rz_package_2',
          FS::joinPath($globalSetDir, 'rz_package_2'),
          '/url/to/package/rz_package_2', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_package_3' => array(
        'name' => (object) array(
          'en' => 'rz_package_3.name',
        ),
        'description' => null,
        'version' => null,
        'websiteSettings' => array(),
        'pageTypes' => array('rz_blog_post', 'rz_no_existing_page_type'),
        'readonly' => true,
        'sourceType' => 'repo',
        'source' => new SourceItem('rz_package_3',
          FS::joinPath($globalSetDir, 'rz_package_3'),
          '/url/to/package/rz_package_3', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
    );

    // ACT
    $allPackages = $dao->getAll($source);

    // ASSERT
    $this->assertInternalType('array', $allPackages);
    $this->assertCount(3, $allPackages);
    foreach($allPackages as $actualPackage) {
      $this->assertInstanceOf('\Cms\Data\Package', $actualPackage);
      $expectedPackage = $expectedPackages[$actualPackage->getId()];
      $actualPackageData = $actualPackage->toArray();
      foreach($expectedPackage as $attributeName => $expectedValue) {
        if ($attributeName == 'source') {
          $this->assertEquals($expectedValue->toArray(),
            $actualPackageData[$attributeName]->toArray(),
            'Failed asserting that package source is equal.');
        } else {
          $this->assertEquals($expectedValue, $actualPackageData[$attributeName],
            sprintf("Failed asserting that package property '%s' is equal.", $attributeName));
        }
      }
    }
  }
}
 