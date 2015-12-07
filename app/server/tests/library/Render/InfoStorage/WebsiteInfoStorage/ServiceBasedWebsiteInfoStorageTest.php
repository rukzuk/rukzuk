<?php


namespace Render\InfoStorage\WebsiteInfoStorage;


use Test\Render\InfoStorage\WebsiteInfoStorage\AbstractWebsiteInfoStorageTestCase;
use Cms\Render\InfoStorage\WebsiteInfoStorage\ServiceBasedWebsiteInfoStorage;
use Cms\Data\WebsiteSettings as WebsiteSettingsData;
use Seitenbau\FileSystem as FS;

/**
 * Test Class for ServiceBasedWebsiteInfoStorageTest
 *
 * @package Render\InfoStorage\WebsiteInfoStorage
 */
class ServiceBasedWebsiteInfoStorageTest extends AbstractWebsiteInfoStorageTestCase
{
  /**
   * @param string $websiteId
   * @param array  $websiteSettings
   *
   * @return IWebsiteInfoStorage
   */
  protected function getWebsiteInfoStorage($websiteId, array $websiteSettings)
  {
    return $this->getServiceBasedWebsiteInfoStorage($websiteId, $websiteSettings);
  }

  /**
   * @param string $websiteId
   * @param array  $websiteSettings
   *
   * @return ServiceBasedWebsiteInfoStorage
   */
  protected function getServiceBasedWebsiteInfoStorage($websiteId, array $websiteSettings)
  {
    // Create website service  mock
    $websiteServiceStub = $this->getMockBuilder('\Cms\Service\Website')
      ->disableOriginalConstructor()->getMock();

    // Create website settings service mock
    $websiteSettingsServiceStub = $this->getMockBuilder('\Cms\Service\WebsiteSettings')
      ->disableOriginalConstructor()->getMock();
    $websiteSettingsServiceStub->expects($this->any())
      ->method('getById')
      ->will($this->returnCallback(function($wId, $sId) use ($websiteId, $websiteSettings) {
        if ($wId != $websiteId || !is_string($sId) || !array_key_exists($sId, $websiteSettings)) {
          throw new \Exception();
        }
        $data = new WebsiteSettingsData();
        $data->setWebsiteId($wId);
        $data->setId($sId);
        $data->setFormValues($websiteSettings[$sId]);
        return $data;
      }));

    return new ServiceBasedWebsiteInfoStorage($websiteId, $websiteServiceStub,
      $websiteSettingsServiceStub);
  }
}