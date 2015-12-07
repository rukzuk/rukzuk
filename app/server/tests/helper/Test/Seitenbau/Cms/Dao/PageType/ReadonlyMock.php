<?php


namespace Test\Seitenbau\Cms\Dao\PageType;

use Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;

class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyPageTypeMock';
  
  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }
}