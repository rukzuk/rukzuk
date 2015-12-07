<?php


namespace Cms\Service\PageType;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Test\Cms\Service\PageType\AbstractTestCase;
use Seitenbau\FileSystem as FS;


/**
 * Class GetByIdTest
 *
 * @package Cms\Service\PageType
 *
 * @group   pageType
 */
class GetByIdTest extends AbstractTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getById_retrieveExpectedPageType()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $pageTypeId = 'rz_shop_product';
    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');

    $pageTypeSource = $this->getPageTypeSource($websiteId);
    $service = $this->getPageTypeService($pageTypeSource);

    $expectedPageType = array(
      'websiteId' => $websiteId,
      'id' => $pageTypeId,
      'name' => (object)array(
        'de' => 'Shop Produkt',
        'en' => 'Shop product',
      ),
      'description' => (object)array(
        'de' => 'rz_shop_product.description.de',
        'en' => 'rz_shop_product.description.en',
      ),
      'version' => 'rz_shop_product.version',
      'form' => array(
        (object)array(
          'foo' => 'bar',
          'emtpyStdClass' => new \stdClass(),
          'emptyArray' => array(),
        ),
      ),
      'formValues' => (object)array(
        'price' => 9999,
      ),
      'previewImageUrl' => '/url/to/rz_shop_product/assets/pageType.svg',
      'readonly' => true,
      'sourceType' => SourceItem::SOURCE_REPOSITORY,
      'source' => new SourceItem('rz_shop_product',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'pageTypes', 'rz_shop_product'),
        '/url/to/rz_shop_product', SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualPageType = $service->getById($websiteId, $pageTypeId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\PageType', $actualPageType);
    $actualPageTypeAsArray = $actualPageType->toArray();
    foreach ($expectedPageType as $attributeName => $expectedValue) {
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