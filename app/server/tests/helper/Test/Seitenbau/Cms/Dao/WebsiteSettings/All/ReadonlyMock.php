<?php


namespace Test\Seitenbau\Cms\Dao\WebsiteSettings\All;

use Cms\Dao\WebsiteSettings\Source as WebsiteSettingsSource;
use Cms\Data\WebsiteSettings as DataWebsiteSettings;
use Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;

class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyWebsiteSettingsMock';
  
  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }

  public function create(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function update(WebsiteSettingsSource $source, DataWebsiteSettings $websiteSettings)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function deleteByWebsiteId(WebsiteSettingsSource $source)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }
}