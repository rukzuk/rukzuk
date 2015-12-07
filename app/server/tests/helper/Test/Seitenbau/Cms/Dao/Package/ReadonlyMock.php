<?php


namespace Test\Seitenbau\Cms\Dao\Package;

use Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;

class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyPackageMock';

  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }

  public function deleteByWebsiteId($websiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }
}