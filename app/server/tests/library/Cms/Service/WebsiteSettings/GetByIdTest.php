<?php


namespace Cms\Service\WebsiteSettings;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Service\WebsiteSettings\AbstractTestCase;
use Seitenbau\FileSystem as FS;


/**
 * Class GetByIdTest
 *
 * @package Cms\Service\WebsiteSettings
 *
 * @group websiteSettings
 */
class GetByIdTest extends AbstractTestCase
{
  protected $sqlFixtures = array('WebsiteSettings.json');

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getById_retrieveExpectedWebsiteSettings()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';
    $websiteSettingsId = 'rz_shop';
    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');

    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    $expectedWebsiteSettings = array(
      'websiteId' => $websiteId,
      'id' => $websiteSettingsId,
      'name' => (object) array(
        'de' => 'Shop Konfiguration',
        'en' => 'Shop configuration',
      ),
      'description' => null,
      'version' => 'rz_shop.version',
      'form' => array(
        (object) array(
          'foo' => 'bar',
          'emtpyStdClass' => new \stdClass(),
          'emptyArray' => array(),
        ),
      ),
      'formValues' => (object) array(
        'foo' => 'bar',
      ),
      'readonly' => true,
      'sourceType' => SourceItem::SOURCE_REPOSITORY,
      'source' => new SourceItem('rz_shop',
        FS::joinPath($globalSetDirectory, 'rz_package_1', 'websiteSettings', 'rz_shop'),
        '/url/to/rz_shop', SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualWebsiteSettings = $service->getById($websiteId, $websiteSettingsId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
    $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
    foreach($expectedWebsiteSettings as $attributeName => $expectedValue) {
      if ($attributeName == 'source') {
        $this->assertEquals($expectedValue->toArray(),
          $actualWebsiteSettingsAsArray[$attributeName]->toArray(),
          'Failed asserting that website settings source are equal.');
      } else {
        $this->assertEquals($expectedValue, $actualWebsiteSettingsAsArray[$attributeName],
          sprintf("Failed asserting that website setting property '%s' are equal.", $attributeName));
      }
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getById_retrieveNullForFormValuesIfNoDataExistsAtDb()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';
    $websiteSettingsId = 'rz_website_settings_test';
    $globalSetDirectory = $this->getGlobalSetDirectory('rukzuk_test');

    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    $expectedWebsiteSettings = array(
      'websiteId' => $websiteId,
      'id' => $websiteSettingsId,
      'name' => (object) array(
        'de' => 'Website Konfiguration',
        'en' => 'Website configuration',
      ),
      'description' => null,
      'version' => null,
      'form' => array(
        (object) array(
          'foo' => 'bar',
          'emtpyStdClass' => new \stdClass(),
          'emptyArray' => array(),
        ),
      ),
      'formValues' => null,
      'readonly' => true,
      'sourceType' => SourceItem::SOURCE_REPOSITORY,
      'source' => new SourceItem('rz_website_settings_test',
        FS::joinPath($globalSetDirectory, 'rz_package_2', 'websiteSettings', 'rz_website_settings_test'),
        '/url/to/rz_website_settings_test', SourceItem::SOURCE_REPOSITORY, true, false),
    );

    // ACT
    $actualWebsiteSettings = $service->getById($websiteId, $websiteSettingsId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
    $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
    foreach($expectedWebsiteSettings as $attributeName => $expectedValue) {
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