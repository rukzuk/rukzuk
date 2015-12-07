<?php


namespace Cms\Dao\PageType\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\PageType\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * Class GetAllTest
 *
 * @package Cms\Dao\PageType\Filesystem
 *
 * @group pageType
 */
class GetAllTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAll_returnsDataAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getPageTypeSource('WEBSITE-ID', array(
      array($baseDirectory, 'rz_shop_product', SourceItem::SOURCE_REPOSITORY),
      array($baseDirectory, 'rz_shop_product_pro', SourceItem::SOURCE_REPOSITORY),
    ));
    $form = array(
      (object) array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );
    $expectedPageTypes = array(
      'rz_shop_product' => array(
        'name' => (object) array(
          'de' => 'Shop Produkt',
          'en' => 'Shop product',
        ),
        'description' => (object) array(
          'de' => 'rz_shop_product.description.de',
          'en' => 'rz_shop_product.description.en',
        ),
        'version' => 'rz_shop_product.version',
        'form' => $form,
        'formValues' => (object) array(
          'price' => 9999,
        ),
        'previewImageUrl' => '/url/to/pageType/rz_shop_product/assets/pageType.svg',
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_shop_product',
          FS::joinPath($baseDirectory, 'rz_shop_product'),
          '/url/to/pageType/rz_shop_product', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_shop_product_pro' => array(
        'name' => (object) array(
          'de' => 'Shop Pro-Produkt',
          'en' => 'Shop pro product',
        ),
        'description' => null,
        'version' => null,
        'form' => $form,
        'formValues' => null,
        'previewImageUrl' => null,
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_shop_product_pro',
          FS::joinPath($baseDirectory, 'rz_shop_product_pro'),
          '/url/to/pageType/rz_shop_product_pro', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
    );

    // ACT
    $allPageTypes = $dao->getAll($source);

    // ASSERT
    $this->assertInternalType('array', $allPageTypes);
    $this->assertCount(count($expectedPageTypes), $allPageTypes);
    foreach($allPageTypes as $actualPageType) {
      $this->assertInstanceOf('\Cms\Data\PageType', $actualPageType);
      $this->assertArrayHasKey($actualPageType->getId(), $expectedPageTypes);
      $expectedPageType = $expectedPageTypes[$actualPageType->getId()];
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
  }
}
 