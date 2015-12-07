<?php
namespace Test\Seitenbau\Cms\Dao\Module;

use Cms\Dao\Base\SourceItem;
use Cms\Dao\Module\Source as ModuleSource;
use Cms\Data\Modul as DataModule;
use Test\Seitenbau\Cms\Dao\ReadonlyMockExcpetion;

/**
 * dao mock for readonly modules
 *
 * @package      Test
 * @subpackage   Dao
 */
class ReadonlyMock extends WriteableMock
{
  const EXCEPTION_MESSAGE = 'ReadonlyModulesMock';
  
  static public function tearDown()
  {
    // deactivating data restoring on tear down
  }

  public function deleteByIds(ModuleSource $moduleSource, array $ids)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function create(ModuleSource $moduleSource, DataModule $module, SourceItem $sourceItem = null)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function deleteByWebsiteId($websiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  protected function save($moduleDirectory, DataModule $module)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function createStorageForWebsite($websiteId)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }

  public function copyToNewWebsite(ModuleSource $moduleSourceFrom, ModuleSource $moduleSourceTo)
  {
    throw new ReadonlyMockExcpetion(self::EXCEPTION_MESSAGE);
  }
}