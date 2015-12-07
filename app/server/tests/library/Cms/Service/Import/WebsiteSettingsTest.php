<?php


namespace Cms\Service\Import;


use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Service\Import as ImportService;
use Test\Seitenbau\ServiceTestCase;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

class WebsiteSettingsTest extends ServiceTestCase
{
  protected $sqlFixtures = array('WebsiteSettings.json');

  protected $websiteId = 'SITE-website0-sett-ings-impo-rt0test00001-SITE';
  protected $testFilesDirectory = null;

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);

    parent::setUp();

    $config = Registry::getConfig();
    $this->testFilesDirectory = $config->test->files->directory;
  }

  /**
   * @test
   * @group library
   */
  public function importWebsiteSettingsFromJsonFileSuccess()
  {
    // ARRANGE
    $importService = new ImportService();
    $unzipDirectory = FS::joinPath($this->testFilesDirectory, 'import', 'import_test_001');
    $expectedImportData = array(
      'rz_shop' => array(
        'id' => 'rz_shop',
        'name' => (object) array(
          'de' => 'Shop Konfiguration',
          'en' => 'Shop configuration',
        ),
      ),
      'rz_shop_pro' => array(
        'id' => 'rz_shop_pro',
        'name' => (object) array(
          'de' => 'Pro-Shop Konfiguration',
          'en' => 'Pro shop configuration',
        ),
      ),
    );

    // ACT
    $actualImportedData = $this->callMethod($importService,
      'importWebsiteSettingsFromJsonFile', array($this->websiteId, $unzipDirectory));

    // ASSERT
    $this->assertEquals($expectedImportData, $actualImportedData);
  }
}
 