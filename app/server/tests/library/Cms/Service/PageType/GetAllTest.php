<?php


namespace Cms\Service\PageType;

use Cms\Dao\Base\AbstractSource;
use Cms\Dao\Base\SourceItem;
use Test\Cms\Service\PageType\AbstractTestCase;
use Seitenbau\FileSystem as FS;


/**
 * Class GetAllTest
 *
 * @package Cms\Service\PageType
 *
 * @group   pageType
 */
class GetAllTest extends AbstractTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAll_retrieveExpectedPageTypes()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';

    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $pathToPackage1PageTypeDir = FS::joinPath($globalSetDirectory, 'rz_package_1', 'pageTypes');
    $pathToPackage3PageTypeDir = FS::joinPath($globalSetDirectory, 'rz_package_3', 'pageTypes');
    $pageTypeSource = $this->getPageTypeSource($websiteId);
    $service = $this->getPageTypeService($pageTypeSource);

    $form = array(
      (object)array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );

    $expectedPageTypes = array(
      'rz_shop_product' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop_product',
        'name' => (object)array(
          'de' => 'Shop Produkt',
          'en' => 'Shop product',
        ),
        'description' => (object)array(
          'de' => 'rz_shop_product.description.de',
          'en' => 'rz_shop_product.description.en',
        ),
        'version' => 'rz_shop_product.version',
        'form' => $form,
        'formValues' => (object)array(
          'price' => 9999,
        ),
        'previewImageUrl' => '/url/to/rz_shop_product/assets/pageType.svg',
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_shop_product',
          FS::joinPath($pathToPackage1PageTypeDir, 'rz_shop_product'), '/url/to/rz_shop_product',
          SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_shop_product_pro' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop_product_pro',
        'name' => (object)array(
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
          FS::joinPath($pathToPackage1PageTypeDir, 'rz_shop_product_pro'),
          '/url/to/rz_shop_product_pro', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_blog_post' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_blog_post',
        'name' => (object)array(
          'de' => 'Blog Artikel',
          'en' => 'Blog post',
        ),
        'description' => (object)array(
          'de' => 'rz_blog_post.description',
        ),
        'version' => null,
        'form' => $form,
        'formValues' => null,
        'previewImageUrl' => null,
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_blog_post',
          FS::joinPath($pathToPackage3PageTypeDir, 'rz_blog_post'), '/url/to/rz_blog_post',
          SourceItem::SOURCE_REPOSITORY, true, false),
      ),
    );

    // ACT
    $allPageTypes = $service->getAll($websiteId);

    // ASSERT
    $this->assertInternalType('array', $allPageTypes);
    $this->assertCount(count($expectedPageTypes), $allPageTypes);
    foreach ($allPageTypes as $actualPageType) {
      $this->assertInstanceOf('\Cms\Data\PageType', $actualPageType);
      $this->assertArrayHasKey($actualPageType->getId(), $expectedPageTypes);
      $expectedPageType = $expectedPageTypes[$actualPageType->getId()];
      $actualPageTypeAsArray = $actualPageType->toArray();
      foreach ($expectedPageType as $attributeName => $expectedValue) {
        if ($attributeName == 'source') {
          $this->assertEquals($expectedValue->toArray(),
            $actualPageTypeAsArray[$attributeName]->toArray(),
            "Failed asserting that page type source is equal.");
        } else {
          $this->assertEquals($expectedValue, $actualPageTypeAsArray[$attributeName],
            sprintf("Failed asserting that page type property '%s' is equal.", $attributeName));
        }
      }
    }
  }
}