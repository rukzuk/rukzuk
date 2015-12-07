<?php


namespace Render\InfoStorage\WebsiteInfoStorage;


use Test\Render\InfoStorage\WebsiteInfoStorage\AbstractWebsiteInfoStorageTestCase;

/**
 * Test Class for ArrayBasedColorInfoStorage
 *
 * @package Render\InfoStorage\WebsiteInfoStorage
 */
class ArrayBasedWebsiteInfoStorageTest extends AbstractWebsiteInfoStorageTestCase
{
  /**
   * @param string $websiteId
   * @param array  $websiteSettings
   *
   * @return IWebsiteInfoStorage
   */
  protected function getWebsiteInfoStorage($websiteId, array $websiteSettings)
  {
    $websiteSettings = json_decode(json_encode($websiteSettings), true);
    return new ArrayBasedWebsiteInfoStorage($websiteSettings);
  }
}
 