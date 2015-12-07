<?php


namespace Cms\Service\WebsiteSettings;

use Test\Cms\Service\WebsiteSettings\AbstractTestCase;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * Class SaveTest
 *
 * @package Cms\Service\WebsiteSettings
 *
 * @group websiteSettings
 */
class SaveTest extends AbstractTestCase
{
  public $sqlFixtures = array('WebsiteSettings.json');

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);
    parent::setUp();
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function saveShouldUpdateExistingWebsiteSettings()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';
    $settingsId = 'rz_shop';

    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    $newFormValues = (object) array(
      'newVar' => (object) array(
        'FOO' => 'BAR'
      )
    );
    $attributes = array('formValues' => $newFormValues);

    $existingWebsiteSettings = $service->getAll($websiteId);
    $expectedWebsiteSettings = $existingWebsiteSettings[$settingsId];
    $expectedWebsiteSettings->setFormValues($newFormValues);

    // ACT
    $actualWebsiteSettings = $service->update($websiteId, $settingsId, $attributes);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
    $expectedWebsiteSettingsAsArray = $expectedWebsiteSettings->toArray();
    $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
    $this->assertEquals($expectedWebsiteSettingsAsArray, $actualWebsiteSettingsAsArray);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function saveShouldCreateNewWebsiteSettings()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';
    $settingsId = 'rz_website_settings_test';

    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    $newFormValues = (object) array(
      'newVar' => (object) array(
        'BAR' => 'FOO'
      )
    );
    $attributes = array('formValues' => $newFormValues);

    $existingWebsiteSettings = $service->getAll($websiteId);
    $expectedWebsiteSettings = $existingWebsiteSettings[$settingsId];
    $expectedWebsiteSettings->setFormValues($newFormValues);

    // ACT
    $actualWebsiteSettings = $service->update($websiteId, $settingsId, $attributes);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
    $expectedWebsiteSettingsAsArray = $expectedWebsiteSettings->toArray();
    $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
    $this->assertEquals($expectedWebsiteSettingsAsArray, $actualWebsiteSettingsAsArray);
  }
}