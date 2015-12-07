<?php
namespace Test\Seitenbau\Cms\Dao;

use \Cms\Dao\Factory as DaoFactory;
use Test\Seitenbau\Cms\Dao\Module\ReadonlyMock as ModuleReadonlyMock;
use Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
use Test\Seitenbau\Cms\Dao\Website\ReadonlyMock as WebsiteReadonlyMock;
use Test\Seitenbau\Cms\Dao\Website\WriteableMock as WebsiteWriteableMock;
use Test\Seitenbau\Cms\Dao\Page\ReadonlyMock as PageReadonlyMock;
use Test\Seitenbau\Cms\Dao\Page\WriteableMock as PageWriteableMock;
use Test\Seitenbau\Cms\Dao\Package\ReadonlyMock as PackageReadonlyMock;
use Test\Seitenbau\Cms\Dao\Package\WriteableMock as PackageWriteableMock;
use Test\Seitenbau\Cms\Dao\WebsiteSettings\All\ReadonlyMock as WebsiteSettingsReadonlyMock;
use Test\Seitenbau\Cms\Dao\WebsiteSettings\All\WriteableMock as WebsiteSettingsWriteableMock;
use Test\Seitenbau\Cms\Dao\PageType\ReadonlyMock as PageTypeReadonlyMock;
use Test\Seitenbau\Cms\Dao\PageType\WriteableMock as PageTypeWriteableMock;

/**
 * mock manager for dao
 *
 * @package      Test
 * @subpackage   Dao
 */
class MockManager
{
  static private $defaultMocks = array(
    'Module' => 'readable',
    'Package' => 'readable',
    'WebsiteSettings' => 'readable',
    'PageType' => 'readable',
  );

  static private $activatedMocks = array();

  static public function setUp()
  {
    foreach (self::getActiveMocks() as $mockName => $mockState) {
      switch ($mockName.'-'.$mockState) {
        case 'Module-readable':
          ModuleReadonlyMock::setUp();
          self::setDaoCreate('Modul', function ($daoName, $daoType) {
            return new ModuleReadonlyMock();
          });
          break;
        case 'Module-writeable':
          ModuleWriteableMock::setUp();
          self::setDaoCreate('Modul', function ($daoName, $daoType) {
            return new ModuleWriteableMock();
          });
          break;
        case 'Website-readable':
          WebsiteReadonlyMock::setUp();
          self::setDaoCreate('Website', function ($daoName, $daoType) {
            return new WebsiteReadonlyMock();
          });
          break;
        case 'Website-writeable':
          WebsiteWriteableMock::setUp();
          self::setDaoCreate('Website', function ($daoName, $daoType) {
            return new WebsiteWriteableMock();
          });
          break;
        case 'Page-readable':
          PageReadonlyMock::setUp();
          self::setDaoCreate('Page', function ($daoName, $daoType) {
            return new PageReadonlyMock();
          });
          break;
        case 'Page-writeable':
          PageWriteableMock::setUp();
          self::setDaoCreate('Page', function ($daoName, $daoType) {
            return new PageWriteableMock();
          });
          break;
        case 'Package-readable':
          PackageReadonlyMock::setUp();
          self::setDaoCreate('Package', function ($daoName, $daoType) {
            return new PackageReadonlyMock();
          });
          break;
        case 'Package-writeable':
          PackageWriteableMock::setUp();
          self::setDaoCreate('Package', function ($daoName, $daoType) {
            return new PackageWriteableMock();
          });
          break;
        case 'WebsiteSettings-readable':
          WebsiteSettingsReadonlyMock::setUp();
          self::setDaoCreate('WebsiteSettings', function ($daoName, $daoType) {
            return new WebsiteSettingsReadonlyMock();
          });
          break;
        case 'WebsiteSettings-writeable':
          WebsiteSettingsWriteableMock::setUp();
          self::setDaoCreate('WebsiteSettings', function ($daoName, $daoType) {
            return new WebsiteSettingsWriteableMock();
          });
          break;
        case 'PageType-readable':
          PageTypeReadonlyMock::setUp();
          self::setDaoCreate('PageType', function ($daoName, $daoType) {
            return new PageTypeReadonlyMock();
          });
          break;
        case 'PageType-writeable':
          PageTypeWriteableMock::setUp();
          self::setDaoCreate('PageType', function ($daoName, $daoType) {
            return new PageTypeWriteableMock();
          });
          break;
      }
    }
  }

  static public function tearDown()
  {
    DaoFactory::reset();

    foreach (self::getActiveMocks() as $mockName => $mockData) {
      $mockState = isset($mockData['state']) ? $mockData['state'] : $mockData;
      switch ($mockName.'-'.$mockState) {
        case 'Module-readable':
          ModuleReadonlyMock::tearDown();
          break;
        case 'Module-writeable':
          ModuleWriteableMock::tearDown();
          break;
        case 'Website-readable':
          WebsiteReadonlyMock::tearDown();
          break;
        case 'Website-writeable':
          WebsiteWriteableMock::tearDown();
          break;
        case 'Page-readable':
          PageReadonlyMock::tearDown();
          break;
        case 'Page-writeable':
          PageWriteableMock::tearDown();
          break;
        case 'Package-readable':
          PackageReadonlyMock::tearDown();
          break;
        case 'Package-writeable':
          PackageWriteableMock::tearDown();
          break;
        case 'WebsiteSettings-readable':
          WebsiteSettingsReadonlyMock::tearDown();
          break;
        case 'WebsiteSettings-writeable':
          WebsiteSettingsWriteableMock::tearDown();
          break;
        case 'PageType-readable':
          PageTypeReadonlyMock::tearDown();
          break;
        case 'PageType-writeable':
          PageTypeWriteableMock::tearDown();
          break;
      }
    }
    self::$activatedMocks = array();
  }

  /**
   * activates the module mock
   *
   * @param bool  $writeable
   */
  static public function activateModuleMock($writeable = false)
  {
    self::$activatedMocks['Module'] = ($writeable ? 'writeable' : 'readable');
  }

  /**
   * activates the website mock
   *
   * @param bool  $writeable
   */
  static public function activateWebsiteMock($writeable = false)
  {
    self::$activatedMocks['Website'] = ($writeable ? 'writeable' : 'readable');
  }

  /**
   * activates the page mock
   *
   * @param bool  $writeable
   */
  static public function activatePageMock($writeable = false)
  {
    self::$activatedMocks['Page'] = ($writeable ? 'writeable' : 'readable');
  }

  /**
   * activates the page mock
   *
   * @param bool  $writeable
   */
  static public function activateWebsiteSettingsMock($writeable = false)
  {
    self::$activatedMocks['WebsiteSettings'] = ($writeable ? 'writeable' : 'readable');
  }

  /**
   * activates the package mock
   *
   * @param bool  $writeable
   */
  static public function activatePackageMock($writeable = false)
  {
    self::$activatedMocks['Package'] = ($writeable ? 'writeable' : 'readable');
  }

  /**
   * sets the dao creation callback for the given dao
   *
   * @param   string   $daoName
   * @param   Callable $createCallback
   */
  static public function setDaoCreate($daoName, $createCallback)
  {
    DaoFactory::setDaoCreate($daoName, $createCallback);
  }

  /**
   * @return array
   */
  protected static function getActiveMocks()
  {
    return array_merge(self::$defaultMocks, self::$activatedMocks);
  }
}