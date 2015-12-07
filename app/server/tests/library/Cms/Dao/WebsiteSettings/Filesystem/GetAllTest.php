<?php


namespace Cms\Dao\WebsiteSettings\Filesystem;

use Cms\Dao\Base\SourceItem;
use Cms\Data\WebsiteSettings;
use Test\Cms\Dao\WebsiteSettings\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;

/**
 * Class GetAllTest
 *
 * @package Cms\Dao\WebsiteSettings\Filesystem
 *
 * @group websiteSettings
 */
class GetAllTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsDataAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getWebsiteSettingsSource('WEBSITE-ID', array(
      array($baseDirectory, 'rz_shop', SourceItem::SOURCE_REPOSITORY),
      array($baseDirectory, 'rz_shop_pro', SourceItem::SOURCE_REPOSITORY),
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
        'name' => (object) array(
          'de' => 'Shop Konfiguration',
          'en' => 'Shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop.version',
        'form' => $form,
        'formValues' => null,
        'readonly' => true,
        'sourceType' => 'repo',
        'source' => new SourceItem('rz_shop', FS::joinPath($baseDirectory, 'rz_shop'),
          '/url/to/websiteSettings/rz_shop', SourceItem::SOURCE_REPOSITORY, true, false),
      ),
      'rz_shop_pro' => array(
        'name' => (object) array(
          'de' => 'Pro-Shop Konfiguration',
          'en' => 'Pro shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop_pro.version',
        'form' => $form,
        'formValues' => null,
        'readonly' => true,
        'sourceType' => 'repo',
        'source' => new SourceItem('rz_shop_pro', FS::joinPath($baseDirectory, 'rz_shop_pro'),
          '/url/to/websiteSettings/rz_shop_pro', SourceItem::SOURCE_REPOSITORY, true, false),
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
        if ($attributeName == 'source') {
          $this->assertEquals($expectedValue->toArray(),
            $actualWebsiteSettingsAsArray[$attributeName]->toArray(),
            'Failed asserting that website-settings source  is equal.');
        } else {
          $this->assertEquals($expectedValue, $actualWebsiteSettingsAsArray[$attributeName],
            sprintf("Failed asserting that website-settings property '%s' is equal.", $attributeName));
        }
      }
    }
  }
}
 