<?php


namespace Cms\Service\WebsiteSettings;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Service\WebsiteSettings\AbstractTestCase;
use Seitenbau\FileSystem as FS;


/**
 * Class GetAllTest
 *
 * @package Cms\Service\WebsiteSettings
 *
 * @group websiteSettings
 */
class GetAllTest extends AbstractTestCase
{
  public $sqlFixtures = array('WebsiteSettings.json');

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function getAllShouldRetrieveExpectedWebsiteSettings()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';

    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');
    $pathToPackage1WebsiteSettingsDir = FS::joinPath($globalSetDirectory, 'rz_package_1', 'websiteSettings');
    $pathToPackage2WebsiteSettingsDir = FS::joinPath($globalSetDirectory, 'rz_package_2', 'websiteSettings');
    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    $form = array(
      (object) array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );

    $expectedWebsiteSettings = array(
      'rz_shop' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop',
        'name' => (object) array(
          'de' => 'Shop Konfiguration',
          'en' => 'Shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop.version',
        'form' => $form,
        'formValues' => (object) array(
          'foo' => 'bar',
        ),
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_shop',
          FS::joinPath($pathToPackage1WebsiteSettingsDir, 'rz_shop'), '/url/to/rz_shop',
          SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_shop_pro' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop_pro',
        'name' => (object) array(
          'de' => 'Pro-Shop Konfiguration',
          'en' => 'Pro shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop_pro.version',
        'form' => $form,
        'formValues' => (object) array(
          'bar' => 'foo',
        ),
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_shop_pro',
          FS::joinPath($pathToPackage1WebsiteSettingsDir, 'rz_shop_pro'), '/url/to/rz_shop_pro',
          SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_website_settings_test' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_website_settings_test',
        'name' => (object) array(
          'de' => 'Website Konfiguration',
          'en' => 'Website configuration',
        ),
        'description' => null,
        'version' => null,
        'form' => $form,
        'formValues' => null,
        'readonly' => true,
        'sourceType' => SourceItem::SOURCE_REPOSITORY,
        'source' => new SourceItem('rz_website_settings_test',
          FS::joinPath($pathToPackage2WebsiteSettingsDir, 'rz_website_settings_test'),
          '/url/to/rz_website_settings_test', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
    );

    // ACT
    $allWebsiteSettings = $service->getAll($websiteId);

    // ASSERT
    $this->assertInternalType('array', $allWebsiteSettings);
    $this->assertCount(3, $allWebsiteSettings);
    foreach($allWebsiteSettings as $actualWebsiteSettings) {
      $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
      $this->assertArrayHasKey($actualWebsiteSettings->getId(), $expectedWebsiteSettings);
      $expectedSettings = $expectedWebsiteSettings[$actualWebsiteSettings->getId()];
      $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
      foreach($expectedSettings as $attributeName => $expectedValue) {
        if ($attributeName == 'source') {
          $this->assertEquals($expectedValue->toArray(),
            $actualWebsiteSettingsAsArray[$attributeName]->toArray(),
            'Failed asserting that website setting source are equal.');
        } else {
          $this->assertEquals($expectedValue, $actualWebsiteSettingsAsArray[$attributeName],
            sprintf("Failed asserting that website setting property '%s' are equal.", $attributeName));
        }
      }
    }
  }
}