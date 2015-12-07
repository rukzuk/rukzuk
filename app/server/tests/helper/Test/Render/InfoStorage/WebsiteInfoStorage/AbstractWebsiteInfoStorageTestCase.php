<?php


namespace Test\Render\InfoStorage\WebsiteInfoStorage;


use Test\Render\InfoStorage\AbstractInfoStorageTestCase;

/**
 * Class AbstractWebsiteInfoStorageTestCase
 *
 * @package Test\Render\WebsiteInfoStorage\ColorInfoStorage
 */
abstract class AbstractWebsiteInfoStorageTestCase extends AbstractInfoStorageTestCase
{
  /**
   * @param string $websiteId
   * @param array  $websiteSettings
   *
   * @return \Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage
   */
  abstract protected function getWebsiteInfoStorage($websiteId, array $websiteSettings);

  /**
   * @test
   * @group        rendering
   * @group        small
   * @group        dev
   * @dataProvider provider_test_getWebsiteSettingsReturnsDataAsExpected
   */
  public function test_getWebsiteSettingsReturnsDataAsExpected($allWebsiteSettings,
                                                               $websiteSettingsId,
                                                               $expectedWebsiteSettings)
  {
    // ARRANGE
    $infoStorage = $this->getWebsiteInfoStorage('WEBSITE_ID', $allWebsiteSettings);

    // ACT
    $actualWebsiteSettings = $infoStorage->getWebsiteSettings($websiteSettingsId);

    // ASSERT
    $this->assertEquals($expectedWebsiteSettings, $actualWebsiteSettings);
  }

  /**
   * @return array
   */
  public function provider_test_getWebsiteSettingsReturnsDataAsExpected()
  {
    $allWebsiteSettings = $this->getAllWebsiteSettings();
    return array(
      array(
        $allWebsiteSettings,
        'rz_shop',
        json_decode(json_encode($allWebsiteSettings['rz_shop']), true)
      ),
      array(
        $allWebsiteSettings,
        'rz_websitesettings',
        json_decode(json_encode($allWebsiteSettings['rz_websitesettings']), true)
      ),
    );
  }

  /**
   * @test
   * @group        rendering
   * @group        small
   * @group        dev
   * @dataProvider provider_notExistingWebsiteSettings
   *
   * @expectedException \Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists
   */
  public function test_getWebsiteSettings_throwExceptionForNotExistingSettings($allWebsiteSettings,
                                                                               $websiteSettingsId)
  {
    // ARRANGE
    $infoStorage = $this->getWebsiteInfoStorage('WEBSITE_ID', $allWebsiteSettings);

    // ACT
    $infoStorage->getWebsiteSettings($websiteSettingsId);
  }

  /**
   * @return array
   */
  public function provider_notExistingWebsiteSettings()
  {
    return array(
      array($this->getAllWebsiteSettings(), null),
      array($this->getAllWebsiteSettings(), new \stdClass()),
      array($this->getAllWebsiteSettings(), ''),
      array($this->getAllWebsiteSettings(), 'website_settings_not_exists'),
    );
  }


  /**
   * @return array
   */
  protected function getAllWebsiteSettings()
  {
    return array(
      'rz_shop' => array(
        'FOO' => 'BAR',
        'myArray' => array(),
        'myObject' => (object) array(
          'foo' => 'bar',
        )
      ),
      'rz_websitesettings' => array(
        'bar' => 'foo',
      ),
    );
  }

}