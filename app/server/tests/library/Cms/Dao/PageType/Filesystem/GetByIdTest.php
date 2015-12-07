<?php


namespace Cms\Dao\PageType\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\PageType\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * Class GetByIdTest
 *
 * @package Cms\Dao\PageType\Filesystem
 *
 * @group pageType
 */
class GetByIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getById_returnsDataAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $pageTypeId = 'rz_shop_product';
    $dao = $this->getFilesystemDao();
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getPageTypeSource($websiteId, array(
      array($baseDirectory, 'rz_shop_product', SourceItem::SOURCE_REPOSITORY),
      array($baseDirectory, 'rz_shop_product_pro', SourceItem::SOURCE_REPOSITORY),
    ));
    $expectedPageType = array(
      'websiteId' => $websiteId,
      'id' => $pageTypeId,
      'name' => (object) array(
        'de' => 'Shop Produkt',
        'en' => 'Shop product',
      ),
      'description' => (object) array(
        'de' => 'rz_shop_product.description.de',
        'en' => 'rz_shop_product.description.en',
      ),
      'version' => 'rz_shop_product.version',
      'form' => array(
        (object) array(
          'foo' => 'bar',
          'emtpyStdClass' => new \stdClass(),
          'emptyArray' => array(),
        ),
      ),
      'formValues' => (object) array(
        'price' => 9999,
      ),
      'previewImageUrl' => '/url/to/pageType/rz_shop_product/assets/pageType.svg',
      'readonly' => true,
      'sourceType' => SourceItem::SOURCE_REPOSITORY,
      'source' => new SourceItem('rz_shop_product',
        FS::joinPath($baseDirectory, 'rz_shop_product'), '/url/to/pageType/rz_shop_product',
        SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualPageType = $dao->getById($source, $pageTypeId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\PageType', $actualPageType);
    $actualPageTypeAsArray = $actualPageType->toArray();
    foreach($expectedPageType as $attributeName => $expectedValue) {
      if ($attributeName == 'source') {
        $this->assertEquals($expectedValue->toArray(),
          $actualPageTypeAsArray[$attributeName]->toArray(),
          'Failed asserting that page type source is equal.');
      } else {
        $this->assertEquals($expectedValue, $actualPageTypeAsArray[$attributeName],
          sprintf("Failed asserting that page type property '%s' is equal.", $attributeName));
      }
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2702
   */
  public function test_getById_throwExceptionIfPageTypeNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $pageTypeId = 'rz_page_type_not_exists';
    $dao = $this->getFilesystemDao();
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getPageTypeSource($websiteId, array(
      array($baseDirectory, 'rz_shop_product'),
      array($baseDirectory, 'rz_shop_product_pro'),
    ));

    // ACT
    $dao->getById($source, $pageTypeId);
  }
}
 