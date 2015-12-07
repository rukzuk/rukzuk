<?php


namespace Cms\Dao\WebsiteSettings\Doctrine;

use Test\Cms\Dao\WebsiteSettings\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;
use \Cms\Data\WebsiteSettings as DataWebsiteSettings;

/**
 * Class GetAllTest
 *
 * @package Cms\Dao\WebsiteSettings\Doctrine
 *
 * @group websiteSettings
 */
class GetAllTest extends AbstractDaoTestCase
{
  public $sqlFixtures = array('WebsiteSettings.json');

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsDataAsExpected()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-test-000000000002-SITE';
    $dao = $this->getDoctrineDao();
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getWebsiteSettingsSource($websiteId, array(
      array($baseDirectory, 'rz_shop'),
      array($baseDirectory, 'rz_shop_pro'),
    ));
    $form = array(
      (object) array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );
    $expectedWebsiteSettings = array(
      'rz_shop' => array(
        'name' => null,
        'description' => null,
        'version' => null,
        'form' => null,
        'formValues' => (object) array(
          'bar' => 'foo'
        ),
        'readonly' => true,
        'sourceType' => DataWebsiteSettings::SOURCE_DATA,
        'source' => null,
      ),
      'rz_shop_pro' => array(
        'name' => null,
        'description' => null,
        'version' => null,
        'form' => null,
        'formValues' => (object) array(
          'foo' => 'bar'
        ),
        'readonly' => true,
        'sourceType' => DataWebsiteSettings::SOURCE_DATA,
        'source' => null,
      ),
    );

    // ACT
    $allWebsiteSettings = $dao->getAll($source);

    // ASSERT
    $this->assertInternalType('array', $allWebsiteSettings);
    $this->assertCount(count($expectedWebsiteSettings), $allWebsiteSettings);
    foreach($allWebsiteSettings as $actualWebsiteSettings) {
      $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
      $this->assertArrayHasKey($actualWebsiteSettings->getId(), $expectedWebsiteSettings);
      $expectedSettings = $expectedWebsiteSettings[$actualWebsiteSettings->getId()];
      $actualWebsiteSettingsAsArray = $actualWebsiteSettings->toArray();
      foreach($expectedSettings as $attributeName => $expectedValue) {
        $this->assertEquals($expectedSettings[$attributeName], $actualWebsiteSettingsAsArray[$attributeName],
          sprintf("Failed asserting that property '%s' is equal.", $attributeName));
      }
    }
  }
}
 