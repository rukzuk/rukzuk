<?php


namespace Cms\Service\WebsiteSettings;

use Test\Cms\Service\WebsiteSettings\AbstractTestCase;
use Seitenbau\FileSystem as FS;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;


/**
 * Class DeleteByWebsiteIdTest
 *
 * @package Cms\Service\WebsiteSettings
 *
 * @group websiteSettings
 */
class DeleteByWebsiteIdTest extends AbstractTestCase
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
  public function test_deleteByWebsiteId_deletesAllWebsiteSettingsOfTheGivingWebsiteId()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000001-SITE';

    $websiteSettingsSource = $this->getWebsiteSettingsSource($websiteId);
    $service = $this->getWebsiteSettingsService($websiteSettingsSource);

    // ACT
    $service->deleteByWebsiteId($websiteId);

    // ASSERT
    $actualWebsiteSettings = $service->getAll($websiteId);
    foreach ($actualWebsiteSettings as $websiteSettings) {
      $this->assertNull($websiteSettings->getFormValues());
    }
  }
}