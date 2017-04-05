<?php


namespace Cms\Creator\Adapter;

use Cms\Creator\Adapter\DynamicCreator\CreatorStorage;
use Cms\Creator\Adapter\DynamicCreator\PageCreator;
use Cms\Creator\Adapter\DynamicCreator\PreparePage;
use Cms\Creator\Adapter\DynamicCreator\PreparePageResult;
use Cms\Creator\Adapter\DynamicCreator\SiteStructure;
use Cms\Creator\CreatorConfig;
use Cms\Creator\CreatorContext;
use Cms\Creator\CreatorJobConfig;
use Cms\Data\Creator as CreatorData;
use Render\IconHelper\SimpleIconHelper;
use Render\InfoStorage\ColorInfoStorage\ArrayBasedColorInfoStorage;
use Render\InfoStorage\MediaInfoStorage\LiveArrayMediaInfoStorage;
use Render\InfoStorage\ModuleInfoStorage\ArrayBasedModuleInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\ArrayBasedNavigationInfoStorage;
use Render\InfoStorage\NavigationInfoStorage\LiveArrayNavigationInfoStorage;
use Render\PageUrlHelper\SimplePageUrlHelper;
use Seitenbau\Registry as Registry;
use Seitenbau\FileSystem as FS;
use Test\Render\TestMediaUrlHelper;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\Cms\Dao\MockManager;
use Test\Seitenbau\TransactionTestCase;
use Test\Seitenbau\Directory\Helper as DirectoryHelper;


class PageCreatorTestClass extends PageCreator
{
  protected function doPreparePage($pageId)
  {
    $info = array(
      'id' => $pageId,
      'structure' => $this->getSiteStructure()->toArray(),
    );
    $prepare = new PreparePage($this->getCreatorContext(),
      $this->getWebsiteId(), $info, null);
    return $prepare->prepare();
  }
}

class DynamicCreatorTestClass extends DynamicCreator
{
  protected function createPageCreator(CreatorStorage $creatorStorage,
                                       CreatorJobConfig $jobConfig,
                                       SiteStructure $siteStructure)
  {
    return new PageCreatorTestClass($this->getCreatorContext(),
      $this->getCreatorConfig(), $creatorStorage, $siteStructure, $jobConfig);
  }
}


class DynamicCreatorTest extends TransactionTestCase
{
  public $sqlFixtures = array('library_Cms_Creator_DynamicCreator.json');

  protected function setUp()
  {
    MockManager::activateWebsiteMock();
    MockManager::activatePageMock();
    parent::setUp();
    FS::createDirIfNotExists($this->getWorkingDirectory());
    ConfigHelper::disableDefaultPageType();
  }

  protected function tearDown()
  {
    $creatorTempDirectory = Registry::getConfig()->creator->directory;
    $workingDirectory = $this->getWorkingDirectory();
    DirectoryHelper::removeRecursiv($workingDirectory, $creatorTempDirectory);
    parent::tearDown();
  }

  /**
   * @test
   * @group creator
   * @group library
   * @group dev
   */
  public function test_createWebsiteWithPageWithoutUnitsSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000003-SITE';
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();

    // ACT
    $creatorData = $creator->createWebsite($creatorJobConfig);

    // ASSERT: NO EXCEPTION OCCURRED
  }

  /**
   * @test
   * @group creator
   * @group library
   * @group dev
   */
  public function test_createWebsiteWithoutTemplatesAndPagesSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000002-SITE';
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();

    // ACT
    $creatorData = $creator->createWebsite($creatorJobConfig);

    // ASSERT: NO EXCEPTION OCCURRED
  }

  /**
   * @test
   * @group                 creator
   * @group                 library
   * @group                 dev
   *
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2210
   */
  public function test_createWebsiteThrowExceptionIfExceptionOccurredAtPreparePage()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000004-SITE';
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();

    // ACT
    $creatorData = $creator->createWebsite($creatorJobConfig);
  }

  /**
   * @test
   * @group creator
   * @group library
   * @group dev
   */
  public function test_createWebsiteCreatesLegacyWebsiteSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000005-SITE';
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();

    // ACT
    $startTime = time();
    $creatorData = $creator->createWebsite($creatorJobConfig);

    // ASSERT
    $this->checkDefaultLiveDirectoriesAndFiles($creatorData,
      $websiteId, $startTime, true);

    $this->checkMediaAndAlbumInformation($creatorData,
      array(), array());

    $this->checkWebsiteSettingsInformation($creatorData, array());

    $this->checkColorInformation($creatorData, array());

    $this->checkResolutionInformation($creatorData, array());

    $this->checkModuleInformation($creatorData,
      $this->getExpectedLegacyModuleArray());

    $expectedNavigationArray = $this->getExpectedLegacyNavigationArray();
    $this->checkNavigationInformation($creatorData, $expectedNavigationArray);
    $this->checkPageStructureFiles($creatorData, $expectedNavigationArray,
      array());
  }

  /**
   * @return array
   */
  protected function getExpectedLegacyModuleArray()
  {
    $assetWebPath = 'assetWebPath/modules/';
    $assetBasePath = 'assetPath' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    $moduleBasePath = 'moduleBasePath' . DIRECTORY_SEPARATOR;
    return array( // ToDo: add expected legacy module info
    );
  }

  /**
   * @return array
   */
  protected function getExpectedLegacyNavigationArray()
  {
    return array(
      'PAGE-d08a73cc-0ea0-0000-1000-000000000005-PAGE' => array(
        'name' => 'Legacy Page',
        'description' => 'this is the Legacy Page description',
        'navigationTitle' => 'this is the Legacy Page navigation title',
        'date' => 1395052400,
        'inNavigation' => true,
        'url' => '',
        'parentIds' => array(),
        'childrenIds' => array(),
        'globals' => array(),
        'mediaId' => '',
        'pageType' => 'page',
        'pageAttributes' => array('foo' => 'bar'),
      ),
    );
  }

  /**
   * @test
   * @group creator
   * @group library
   * @group dev
   */
  public function test_createWebsite_usedMediaAndAlbumIdsCollectedAsExpected()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000006-SITE';
    $config = Registry::getConfig();
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();
    $expectedAlbumInformation = array(
      'ALBUM-dynamic0-crea-tor0-test-100000000006-ALBUM' => array(
        'MDB-dynamic0-crea-tor0-test-100000000006-MDB',
        'MDB-dynamic0-crea-tor0-test-200000000006-MDB',
      ),
      'ALBUM-dynamic0-crea-tor0-test-400000000006-ALBUM' => array(
        'MDB-dynamic0-crea-tor0-test-700000000006-MDB',
      ),
    );
    $expectedMediaInformation = array(
      'MDB-dynamic0-crea-tor0-test-100000000006-MDB' => array(
        'name' => 'logo.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'logo.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-200000000006-MDB' => array(
        'name' => 'logo.pdf',
        'size' => '32889',
        'lastModified' => '1395056370',
        'file' => 'logo.pdf',
        'iconFile' => 'icon_pdf.png',
      ),
      'MDB-dynamic0-crea-tor0-test-300000000006-MDB' => array(
        'name' => 'mdb3.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb3.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-400000000006-MDB' => array(
        'name' => 'mdb4.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb4.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      /*
       * MDB-dynamic0-crea-tor0-test-500000000006-MDB => is not used
       */
      'MDB-dynamic0-crea-tor0-test-600000000006-MDB' => array(
        'name' => 'mdb6.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb6.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-700000000006-MDB' => array(
        'name' => 'mdb7.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb7.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
    ) ;

    // ACT
    $startTime = time();
    $creatorData = $creator->createWebsite($creatorJobConfig);

    // ASSERT
    $this->checkDefaultLiveDirectoriesAndFiles($creatorData,
      $websiteId, $startTime, false);
    $this->checkMediaAndAlbumInformation($creatorData, $expectedMediaInformation,
      $expectedAlbumInformation);
  }

  /**
   * @test
   * @group creator
   * @group library
   * @group dev
   */
  public function test_createWebsiteSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-dynamic0-crea-tor0-test-000000000001-SITE';
    $config = Registry::getConfig();
    $creatorJobConfig = $this->getCreatorJobConfig($websiteId);
    $creator = $this->getDynamicCreator();

    // ACT
    $startTime = time();
    $creatorData = $creator->createWebsite($creatorJobConfig);

    // ASSERT
    $this->checkDefaultLiveDirectoriesAndFiles($creatorData,
      $websiteId, $startTime, false);

    $this->checkMediaAndAlbumInformation($creatorData,
      $this->getExpectedMediaArray(), array(
        'ALBUM-dynamic0-crea-tor0-test-200000000001-ALBUM' => array(
          'MDB-dynamic0-crea-tor0-test-500000000001-MDB',
          'MDB-dynamic0-crea-tor0-test-600000000001-MDB')));

    $this->checkWebsiteSettingsInformation($creatorData, array(
      'rz_website_settings_test' => array(
        'FOO' => 'BAR',
        'myArray' => array('foo', 'bar'),
        'myObject' => array('bar' => 'foo'),
      ),
      'rz_shop' => array(),
      'rz_shop_pro' => array(),
    ));

    $this->checkColorInformation($creatorData, array(
      'dynamic0-crea-tor0-test-100000000001' => 'rgba(255,0,0,1)',
    ));

    $this->checkResolutionInformation($creatorData, array(
      'enabled' => true,
      'data' => array(
        array('id' => 'res1', 'width' => 980, 'name' => 'Tablet'))
    ));

    $this->checkModuleInformation($creatorData,
      $this->getExpectedModuleArray());

    $expectedNavigationArray = $this->getExpectedNavigationArray();
    $expectedContentArrays = $this->getExpectedPageContentArray();
    $this->checkNavigationInformation($creatorData, $expectedNavigationArray);
    $this->checkPageStructureFiles($creatorData, $expectedNavigationArray,
      $expectedContentArrays);
  }

  /**
   * @return array
   */
  protected function getExpectedMediaArray()
  {
    return array(
      'MDB-dynamic0-crea-tor0-test-100000000001-MDB' => array(
        'name' => 'logo.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'logo.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-200000000001-MDB' => array(
        'name' => 'logo.pdf',
        'size' => '32889',
        'lastModified' => '1395056370',
        'file' => 'logo.pdf',
        'iconFile' => 'icon_pdf.png',
      ),
      'MDB-dynamic0-crea-tor0-test-300000000001-MDB' => array(
        'name' => 'mdb3.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb3.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-500000000001-MDB' => array(
        'name' => 'mdb5.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb5.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
      'MDB-dynamic0-crea-tor0-test-600000000001-MDB' => array(
        'name' => 'mdb6.jpg',
        'size' => '7736',
        'lastModified' => '1395056370',
        'file' => 'mdb6.jpg',
        'iconFile' => 'icon_jpg.png',
      ),
    );
  }

  /**
   * @return array
   */
  protected function getExpectedModuleArray()
  {
    $assetWebPath = 'assetWebPath/modules/';
    $assetBasePath = 'assetPath' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    $moduleBasePath = 'moduleBasePath' . DIRECTORY_SEPARATOR;
    return array(
      'rz_tests_creator_root_module' => array(
        'assetUrl' => $assetWebPath . 'rz_tests_creator_root_module',
        'assetPath' => $assetBasePath . 'rz_tests_creator_root_module',
        'mainClassFilePath' => $moduleBasePath . 'rz_tests_creator_root_module/rz_tests_creator_root_module.php',
        'mainClassName' => '\\Rukzuk\\Modules\\rz_tests_creator_root_module',
        'codePath' => $moduleBasePath . 'rz_tests_creator_root_module',
        'defaultFormValues' => array(),
        'customData' => array(),
        'manifest' => array(
          'moduleType' => 'root',
          'apiType' => 'RootAPIv1',
          'config' => array(),
        )
      ),
      'rz_tests_creator_default_module' => array(
        'assetUrl' => $assetWebPath . 'rz_tests_creator_default_module',
        'assetPath' => $assetBasePath . 'rz_tests_creator_default_module',
        'mainClassFilePath' => $moduleBasePath . 'rz_tests_creator_default_module/rz_tests_creator_default_module.php',
        'mainClassName' => '\\Rukzuk\\Modules\\rz_tests_creator_default_module',
        'codePath' => $moduleBasePath . 'rz_tests_creator_default_module',
        'defaultFormValues' => array(),
        'customData' => array(),
        'manifest' => array(
          'moduleType' => 'default',
          'apiType' => 'APIv1',
          'config' => array(),
        )
      ),
      'rz_tests_creator_extension_module' => array(
        'assetUrl' => $assetWebPath . 'rz_tests_creator_extension_module',
        'assetPath' => $assetBasePath . 'rz_tests_creator_extension_module',
        'mainClassFilePath' => $moduleBasePath . 'rz_tests_creator_extension_module/rz_tests_creator_extension_module.php',
        'mainClassName' => '\\Rukzuk\\Modules\\rz_tests_creator_extension_module',
        'codePath' => $moduleBasePath . 'rz_tests_creator_extension_module',
        'defaultFormValues' => array(),
        'customData' => array(),
        'manifest' => array(
          'moduleType' => 'extension',
          'apiType' => 'APIv1',
          'config' => array(),
        )
      ),
      'rz_tests_global_package_module' => array(
        'assetUrl' => $assetWebPath . 'rz_tests_global_package_module',
        'assetPath' => $assetBasePath . 'rz_tests_global_package_module',
        'mainClassFilePath' => $moduleBasePath . 'rz_tests_global_package_module/rz_tests_global_package_module.php',
        'mainClassName' => '\\Rukzuk\\Modules\\rz_tests_global_package_module',
        'codePath' => $moduleBasePath . 'rz_tests_global_package_module',
        'defaultFormValues' => array(),
        'customData' => array(),
        'manifest' => array(
          'moduleType' => 'default',
          'apiType' => 'APIv1',
          'config' => array(),
        )
      ),
      'rz_tests_local_package_module' => array(
        'assetUrl' => $assetWebPath . 'rz_tests_local_package_module',
        'assetPath' => $assetBasePath . 'rz_tests_local_package_module',
        'mainClassFilePath' => $moduleBasePath . 'rz_tests_local_package_module/rz_tests_local_package_module.php',
        'mainClassName' => '\\Rukzuk\\Modules\\rz_tests_local_package_module',
        'codePath' => $moduleBasePath . 'rz_tests_local_package_module',
        'defaultFormValues' => array(),
        'customData' => array(),
        'manifest' => array(
          'moduleType' => 'default',
          'apiType' => 'APIv1',
          'config' => array(),
        )
      ),
    );
  }

  /**
   * @return array
   */
  protected function getExpectedNavigationArray()
  {
    return array(
      'PAGE-dynamic0-crea-tor0-test-100000000001-PAGE' => array(
        'name' => 'Homepage',
        'description' => '',
        'navigationTitle' => '',
        'date' => 1394799810,
        'inNavigation' => true,
        'url' => '',
        'parentIds' => array(),
        'childrenIds' => array(),
        'globals' => array(),
        'mediaId' => 'MDB-dynamic0-crea-tor0-test-300000000001-MDB',
        'pageType' => 'home',
        'pageAttributes' => array(
          'foo' => 'bar',
          'myArray' => array('foo', 'bar'),
          'myObject' => array(
            'foo' => 'bar'
          ),
        ),
      ),
      'PAGE-dynamic0-crea-tor0-test-200000000001-PAGE' => array(
        'name' => 'Level 1 same name',
        'description' => '',
        'navigationTitle' => '',
        'date' => 1394799820,
        'inNavigation' => true,
        'url' => 'Level-1-same-name/',
        'parentIds' => array(),
        'childrenIds' => array(),
        'globals' => array(),
        'mediaId' => '',
        'pageType' => 'page',
        'pageAttributes' => array(),
      ),
      'PAGE-dynamic0-crea-tor0-test-300000000001-PAGE' => array(
        'name' => 'Level 1 same name',
        'description' => '',
        'navigationTitle' => '',
        'date' => 1394799830,
        'inNavigation' => true,
        'url' => 'Level-1-same-name-1/',
        'parentIds' => array(),
        'childrenIds' => array(
          'PAGE-dynamic0-crea-tor0-test-310000000001-PAGE',
        ),
        'globals' => array(),
        'mediaId' => '',
        'pageType' => 'page',
        'pageAttributes' => array(),
      ),
      'PAGE-dynamic0-crea-tor0-test-310000000001-PAGE' => array(
        'name' => 'Level 2',
        'description' => '',
        'navigationTitle' => 'Level 2 this is the navigation title',
        'date' => 1394799831,
        'inNavigation' => false,
        'url' => 'Level-1-same-name-1/Level-2/',
        'parentIds' => array(
          'PAGE-dynamic0-crea-tor0-test-300000000001-PAGE',
        ),
        'childrenIds' => array(),
        'globals' => array(),
        'mediaId' => '',
        'pageType' => 'page',
        'pageAttributes' => array(),
      ),
    );
  }

  /**
   * @return array
   */
  protected function getExpectedPageContentArray()
  {
    $pageContentArray = array();
    $pageContentArray['PAGE-dynamic0-crea-tor0-test-100000000001-PAGE'] = array(
      'moduleId' => 'rz_tests_creator_root_module',
      'name' => '',
      'formValues' => array(
        'image' => 'MDB-dynamic0-crea-tor0-test-100000000001-MDB',
        'download' => 'MDB-dynamic0-crea-tor0-test-200000000001-MDB',
      ),
      'templateUnitId' => 'MUNIT-00000000-0000-0000-0000-000000000001-MUNIT',
      'id' => 'MUNIT-00000000-0000-0000-0001-000000000000-MUNIT',
      'ghostContainer' => false,
      'htmlClass' => '',
      'children' => array(array(
        'moduleId' => 'rz_tests_creator_default_module',
        'name' => '',
        'formValues' => array(
          'image' => 'MDB-dynamic0-crea-tor0-test-100000000001-MDB',
          'download' => 'MDB-dynamic0-crea-tor0-test-200000000001-MDB',
          'moduleValue' => 'this is the default module value',
        ),
        'templateUnitId' => 'MUNIT-00000000-0000-0000-0000-000000000002-MUNIT',
        'id' => 'MUNIT-00000000-0000-0000-0002-000000000000-MUNIT',
        'ghostContainer' => false,
        'htmlClass' => '',
        'children' => array(array(
          'moduleId' => 'rz_tests_creator_extension_module',
          'name' => '',
          'formValues' => array(
            'image' => 'MDB-dynamic0-crea-tor0-test-100000000001-MDB',
            'download' => 'MDB-dynamic0-crea-tor0-test-200000000001-MDB',
          ),
          'templateUnitId' => 'MUNIT-00000000-0000-0000-0000-000000000000-MUNIT',
          'id' => 'MUNIT-00000000-0000-0000-0003-000000000000-MUNIT',
          'ghostContainer' => false,
          'htmlClass' => '',
        )),
      )),
    );
    return $pageContentArray;
  }

  /**
   * @param CreatorData $creatorData
   * @param string      $websiteId
   * @param int         $startTime
   * @param bool        $isLegacyWebsite
   */
  protected function checkDefaultLiveDirectoriesAndFiles(CreatorData $creatorData,
                                                         $websiteId, $startTime,
                                                         $isLegacyWebsite)
  {
    $this->assertLiveBaseDirectoriesExist($creatorData);
    $this->assertMetaFilesHasRightFormat($creatorData);
    $this->assertInfoFilesHasRightFormat($creatorData, $websiteId, $startTime);
    $this->checkServerDirectory($creatorData, $isLegacyWebsite);
    $this->checkDataDirectory($creatorData);
  }

  /**
   * @param CreatorData $creatorData
   */
  protected function assertLiveBaseDirectoriesExist(CreatorData $creatorData)
  {
    $baseDirectory = $creatorData->getBaseDirectory();
    $this->assertFileExists($baseDirectory);
    $websiteDirectory = $this->getWebsiteDirectory($creatorData);
    $this->assertFileExists($websiteDirectory);
    $metaDirectory = FS::joinPath($baseDirectory, $creatorData->getMetaSubDirectory());
    $this->assertFileExists($metaDirectory);
    $infoDirectory = FS::joinPath($baseDirectory, $creatorData->getInfoFilesSubDirectory());
    $this->assertFileExists($infoDirectory);
  }

  /**
   * @param CreatorData $creatorData
   */
  protected function assertMetaFilesHasRightFormat(CreatorData $creatorData)
  {
    $metaDirectory = FS::joinPath($creatorData->getBaseDirectory(),
      $creatorData->getMetaSubDirectory());

    $expectedCacheList = "files/media/files/cache";
    $cacheListFilePath = FS::joinPath($metaDirectory, 'cache.txt');
    $actualCacheList = file_get_contents($cacheListFilePath);
    $this->assertEquals($expectedCacheList, $actualCacheList);

    $expectedWritableList = "files/media/files/cache";
    $writableListFilePath = FS::joinPath($metaDirectory, 'writeable.txt');
    $actualWritableList = file_get_contents($writableListFilePath);
    $this->assertEquals($expectedWritableList, $actualWritableList);
  }

  /**
   * @param CreatorData $creatorData
   * @param string      $websiteId
   * @param int         $startTime
   */
  protected function assertInfoFilesHasRightFormat(CreatorData $creatorData,
                                                   $websiteId, $startTime)
  {
    $infoDirectory = FS::joinPath($creatorData->getBaseDirectory(),
      $creatorData->getInfoFilesSubDirectory());

    $creatorInfoFilePath = FS::joinPath($infoDirectory, 'creator.json');
    $creatorInfo = json_decode(file_get_contents($creatorInfoFilePath), true);
    $this->assertInternalType('array', $creatorInfo);
    $this->assertArrayHasKey('timestamp', $creatorInfo);
    $this->assertGreaterThanOrEqual($startTime, $creatorInfo['timestamp']);
    $this->assertArrayHasKey('creator', $creatorInfo);
    $this->assertInternalType('array', $creatorInfo['creator']);
    $this->assertArrayHasKey('name', $creatorInfo['creator']);
    $this->assertEquals(DynamicCreator::CRATOR_NAME, $creatorInfo['creator']['name']);
    $this->assertArrayHasKey('version', $creatorInfo['creator']);
    $this->assertEquals(DynamicCreator::CRATOR_VERSION, $creatorInfo['creator']['version']);
    $this->assertArrayHasKey('website', $creatorInfo);
    $this->assertInternalType('array', $creatorInfo['website']);
    $this->assertArrayHasKey('id', $creatorInfo['website']);
    $this->assertEquals($websiteId, $creatorInfo['website']['id']);
  }

  /**
   * @param CreatorData $creatorData
   * @param boolean     $isLegacyWebsite
   */
  protected function checkServerDirectory(CreatorData $creatorData,
                                          $isLegacyWebsite)
  {
    $serverDirectory = $this->getServerDirectory($creatorData);
    $this->assertFileExists($serverDirectory);
    $this->assertFileExists(FS::joinPath($serverDirectory, 'bootstrap.php'));
    $this->assertFileExists(FS::joinPath($serverDirectory, 'constants.php'));
    $libraryDirectory = FS::joinPath($serverDirectory, 'library');
    $this->assertFileExists($libraryDirectory);
    $libraryRenderDirectory = FS::joinPath($libraryDirectory, 'Render');
    $this->assertFileExists($libraryRenderDirectory);
    $this->assertRenderFilesExists($libraryRenderDirectory);
    $this->assertFileExists(FS::joinPath($libraryDirectory, 'Seitenbau'));
    $dualLibraryDirectory = FS::joinPath($libraryDirectory, 'Dual');
    if (!$isLegacyWebsite) {
      $this->assertFileNotExists($dualLibraryDirectory);
    } else {
      // ToDo: check needed legacy files
      $this->assertFileExists($dualLibraryDirectory);
    }
  }

  /**
   * @param CreatorData $creatorData
   */
  protected function checkDataDirectory(CreatorData $creatorData)
  {
    $dataDirectory = $this->getDataDirectory($creatorData);
    $this->assertFileExists($dataDirectory);
    $this->assertFileExists(FS::joinPath($dataDirectory, 'modules'));
    $this->assertFileExists(FS::joinPath($dataDirectory, 'pages'));
  }

  /**
   * @param string $directory
   */
  protected function assertRenderFilesExists($directory)
  {
    // ToDo: complete needed render file list
    $files = array(
      'LiveRenderer.php', 'LiveMediaContext.php', 'LiveMediaCdn.php',
    );
    foreach ($files as $file) {
      $this->assertFileExists(FS::joinPath($directory, $file));
    }
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedMediaArray
   * @param array       $expectedAlbumArray
   */
  protected function checkMediaAndAlbumInformation(CreatorData $creatorData,
                                                   array $expectedMediaArray,
                                                   array $expectedAlbumArray)
  {
    $mediaFilesDirectory = $this->getMediaFilesDirectory($creatorData);
    $mediaIconsDirectory = $this->getMediaIconsDirectory($creatorData);
    $infoStorage = $this->getMediaInfoStorage($creatorData);
    foreach ($expectedAlbumArray as $albumId => $expectedMediaIds) {
      $actualMediaIds = $infoStorage->getMediaIdsByAlbumIds($albumId);
      sort($expectedMediaIds);
      sort($actualMediaIds);
      $this->assertEquals($expectedMediaIds, $actualMediaIds);
    }
    foreach ($expectedMediaArray as $mediaId => $expectedMedia) {
      $actualMedia = $infoStorage->getItem($mediaId);
      $this->assertEquals($mediaId, $actualMedia->getId());
      $this->assertEquals($expectedMedia['name'], $actualMedia->getName());
      $this->assertEquals($expectedMedia['size'], $actualMedia->getSize());
      $this->assertEquals($expectedMedia['lastModified'],
        $actualMedia->getLastModified());
      $mediaFilePath = FS::joinPath($mediaFilesDirectory, $expectedMedia['file']);
      $this->assertEquals($mediaFilePath, $actualMedia->getFilePath());
      $iconFilePath = FS::joinPath($mediaIconsDirectory, $expectedMedia['iconFile']);
      $this->assertEquals($iconFilePath, $actualMedia->getIconFilePath());
    }
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return \Render\InfoStorage\MediaInfoStorage\IMediaInfoStorage
   */
  protected function getMediaInfoStorage(CreatorData $creatorData)
  {
    $mediaFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'media.php');
    $this->assertFileExists($mediaFilePath);
    $mediaArray = include($mediaFilePath);
    $albumFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'album.php');
    $this->assertFileExists($albumFilePath);
    $albumArray = include($albumFilePath);

    $mediaFilesDirectory = $this->getMediaFilesDirectory($creatorData);
    $mediaIconsDirectory = $this->getMediaIconsDirectory($creatorData);
    $iconHelper = new SimpleIconHelper($mediaIconsDirectory, 'fallback.icon.png');
    $mediaUrlHelper = new TestMediaUrlHelper('phpunit');
    return new LiveArrayMediaInfoStorage($mediaFilesDirectory, $mediaArray,
      $mediaUrlHelper, $iconHelper, $albumArray);
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedWebsiteSettingsArray
   */
  protected function checkWebsiteSettingsInformation(CreatorData $creatorData,
                                                     array $expectedWebsiteSettingsArray)
  {
    $websiteSettingsFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'websitesettings.php');
    $this->assertFileExists($websiteSettingsFilePath);
    $actualWebsiteSettingsArray = include($websiteSettingsFilePath);
    $this->assertEquals($expectedWebsiteSettingsArray, $actualWebsiteSettingsArray);
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedAlbumArray
   */
  protected function checkColorInformation(CreatorData $creatorData,
                                           array $colors)
  {
    $colorFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'colors.php');
    $this->assertFileExists($colorFilePath);
    $colorArray = include($colorFilePath);
    $this->assertInternalType('array', $colorArray);
    $colorInfoStorage = new ArrayBasedColorInfoStorage($colorArray);
    $expectedColorIds = array_keys($colors);
    sort($expectedColorIds);
    $actualColorIds = $colorInfoStorage->getColorIds();
    sort($actualColorIds);
    $this->assertEquals($expectedColorIds, $actualColorIds);
    foreach ($colors as $colorId => $expactedColorValue) {
      $actualColor = $colorInfoStorage->getColor($colorId);
      $this->assertEquals($expactedColorValue, $actualColor);
    }
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedResolutionsArray
   */
  protected function checkResolutionInformation(CreatorData $creatorData,
                                                array $expectedResolutionsArray)
  {
    $resolutionsFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'resolutions.php');
    $this->assertFileExists($resolutionsFilePath);
    $actualResolutionsArray = include($resolutionsFilePath);
    $this->assertEquals($expectedResolutionsArray, $actualResolutionsArray);
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedModuleArray
   */
  protected function checkModuleInformation(CreatorData $creatorData,
                                            array $expectedModuleArray)
  {
    $moduleFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'modules.php');
    $this->assertFileExists($moduleFilePath);
    $moduleArray = include($moduleFilePath);
    $this->assertInternalType('array', $moduleArray);
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleArray,
      'moduleBasePath', 'assetPath', 'assetWebPath');
    foreach ($expectedModuleArray as $moduleId => $expactedModuleValues) {
      $this->assertEquals($expactedModuleValues['mainClassFilePath'],
        $moduleInfoStorage->getModuleMainClassFilePath($moduleId));
      $this->assertEquals($expactedModuleValues['mainClassName'],
        $moduleInfoStorage->getModuleClassName($moduleId));
      $this->assertEquals($expactedModuleValues['manifest']['apiType'],
        $moduleInfoStorage->getModuleApiType($moduleId));
      $this->assertEquals($expactedModuleValues['codePath'],
        $moduleInfoStorage->getModuleCodePath($moduleId));
      $this->assertEquals($expactedModuleValues['assetPath'],
        $moduleInfoStorage->getModuleAssetPath($moduleId));
      $this->assertEquals($expactedModuleValues['assetUrl'],
        $moduleInfoStorage->getModuleAssetUrl($moduleId));
      $this->assertEquals($expactedModuleValues['defaultFormValues'],
        $moduleInfoStorage->getModuleDefaultFromValues($moduleId));
      $this->assertEquals($expactedModuleValues['customData'],
        $moduleInfoStorage->getModuleCustomData($moduleId));
      $this->assertEquals($expactedModuleValues['manifest']['config'],
        $moduleInfoStorage->getModuleConfig($moduleId));

      $actualManifest = $moduleInfoStorage->getModuleManifest($moduleId);
      foreach ($expactedModuleValues['manifest'] as $key => $expactedValue) {
        $this->assertEquals($expactedValue, $actualManifest[$key]);
      }
    }
  }

  /**
   * @param $creatorData
   * @param $expectedNavigationArray
   */
  protected function checkNavigationInformation(CreatorData $creatorData,
                                                array $expectedNavigationArray)
  {
    $urlFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'urls.php');
    $this->assertFileExists($urlFilePath);
    $urlArray = include($urlFilePath);
    $navigationFilePath = FS::joinPath($this->getDataDirectory($creatorData),
      'navigation.php');
    $this->assertFileExists($navigationFilePath);
    $navigationArray = include($navigationFilePath);
    $this->assertInternalType('array', $navigationArray);
    $urlHelper = new SimplePageUrlHelper($urlArray, '', '', '');
    $basePageDataDirectory = $this->getPageDataDirectory($creatorData);
    $navigationInfoStorage = new LiveArrayNavigationInfoStorage(
      $basePageDataDirectory, $navigationArray, null, $urlHelper);
    foreach ($expectedNavigationArray as $pageId => $expectedPageValues) {
      $this->assertEquals($expectedPageValues['parentIds'],
        $navigationInfoStorage->getParentIds($pageId));
      $this->assertEquals($expectedPageValues['childrenIds'],
        $navigationInfoStorage->getChildrenIds($pageId));
      $this->assertTrue($navigationInfoStorage->itemExists($pageId));
      $this->assertEquals($expectedPageValues['url'],
        $navigationInfoStorage->getPageUrl($pageId, array(), false));
      $item = $navigationInfoStorage->getItem($pageId);
      $this->assertEquals($pageId, $item->getPageId());
      $this->assertEquals($expectedPageValues['name'],
        $item->getTitle());
      $this->assertEquals($expectedPageValues['description'],
        $item->getDescription());
      $this->assertEquals($expectedPageValues['navigationTitle'],
        $item->getNavigationTitle());
      $this->assertEquals($expectedPageValues['date'],
        $item->getDate());
      $this->assertEquals($expectedPageValues['inNavigation'],
        $item->showInNavigation());
      $this->assertEquals($expectedPageValues['parentIds'],
        $item->getParentIds($pageId));
      $this->assertEquals($expectedPageValues['childrenIds'],
        $item->getChildrenIds($pageId));
      $this->assertEquals($expectedPageValues['globals'],
        $navigationInfoStorage->getPageGlobals($pageId));
      $this->assertEquals($expectedPageValues['mediaId'],
        $item->getMediaId());
      $this->assertEquals($expectedPageValues['pageType'],
        $item->getPageType($pageId));
      $this->assertEquals($expectedPageValues['pageAttributes'],
        $navigationInfoStorage->getPageAttributes($pageId));
    }
    $this->assertFalse($navigationInfoStorage->itemExists('none-existing-page-id'));
  }

  /**
   * @param CreatorData $creatorData
   * @param array       $expectedNavigationArray
   * @param array       $expectedContentArrays
   */
  protected function checkPageStructureFiles(CreatorData $creatorData,
                                             array $expectedNavigationArray,
                                             array $expectedContentArrays)
  {
    $websiteDirectory = $this->getWebsiteDirectory($creatorData);
    $cssFileDirectory = FS::joinPath($this->getFilesDirectory($creatorData), 'css');
    $basePageDataDirectory = $this->getPageDataDirectory($creatorData);
    foreach ($expectedNavigationArray as $pageId => $page) {
      $pageDataDirectory = FS::joinPath($basePageDataDirectory, $pageId);

      $pageMetaFilePath = FS::joinPath($pageDataDirectory, 'meta.php');
      $this->assertFileExists($pageMetaFilePath);
      $pageMeta = include($pageMetaFilePath);
      $this->assertInternalType('array', $pageMeta);

      $pageGlobalFilePath = FS::joinPath($pageDataDirectory, 'global.php');
      $this->assertFileExists($pageGlobalFilePath);
      $pageGlobal = include($pageGlobalFilePath);
      $this->assertInternalType('array', $pageGlobal);

      $pageAttributesFilePath = FS::joinPath($pageDataDirectory, 'attributes.php');
      $this->assertFileExists($pageAttributesFilePath);
      $pageAttributes = include($pageAttributesFilePath);
      $this->assertInternalType('array', $pageAttributes);

      $pageArrayFilePath = FS::joinPath($pageDataDirectory, 'contentarray.php');
      $this->assertFileExists($pageArrayFilePath);
      $pageContentArray = include($pageArrayFilePath);
      $this->assertInternalType('array', $pageContentArray);
      if (isset($expectedContentArrays[$pageId])) {
        $this->assertEquals($expectedContentArrays[$pageId], $pageContentArray);
      }

      $this->assertArrayHasKey('css', $pageMeta);
      $this->assertInternalType('array', $pageMeta['css']);
      $this->assertArrayHasKey('file', $pageMeta['css']);
      $pageCssFilePath = FS::joinPath($cssFileDirectory, $pageMeta['css']['file']);
      $this->assertFileExists($pageCssFilePath);
    }
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getMediaFilesDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getFilesDirectory($creatorData),
      'media', 'files');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getMediaIconsDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getFilesDirectory($creatorData),
      'media', 'icons');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getServerDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getSystemDirectory($creatorData), 'server');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getPageDataDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getDataDirectory($creatorData), 'pages');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getDataDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getSystemDirectory($creatorData), 'data');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getSystemDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getFilesDirectory($creatorData), 'system');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getFilesDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($this->getWebsiteDirectory($creatorData), 'files');
  }

  /**
   * @param CreatorData $creatorData
   *
   * @return string
   */
  protected function getWebsiteDirectory(CreatorData $creatorData)
  {
    return FS::joinPath($creatorData->getBaseDirectory(),
      $creatorData->getWebsiteSubDirectory());
  }

  /**
   * @return DynamicCreator
   */
  protected function getDynamicCreator()
  {
    $creatorContext = $this->getCreatorContext();
    $creatorConfig = $this->getCreatorConfig();
    return new DynamicCreatorTestClass($creatorContext, $creatorConfig);
  }

  /**
   * @return CreatorContext
   */
  protected function getCreatorContext()
  {
    $websiteBusiness = new \Cms\Business\Website('Website');
    $websiteSettingsBusiness = new \Cms\Business\WebsiteSettings('WebsiteSettings');
    $moduleBusiness = new \Cms\Business\Modul('Modul');
    $pageBusiness = new \Cms\Business\Page('Page');
    $pageTypeBusiness = new \Cms\Business\PageType('PageType');
    $mediaBusiness = new \Cms\Business\Media('Media');
    $ticketBusiness = new \Cms\Business\Ticket('Ticket');
    $templateBusiness = new \Cms\Business\Template('Template');
    return new CreatorContext($websiteBusiness, $websiteSettingsBusiness,
      $moduleBusiness, $pageBusiness, $pageTypeBusiness, $mediaBusiness,
      $ticketBusiness, $templateBusiness);
  }

  /**
   * @return CreatorConfig
   */
  protected function getCreatorConfig()
  {
    if (Registry::getConfig()->creator->dynamic) {
      $creatorConfig = Registry::getConfig()->creator->dynamic->toArray();
    } else {
      $creatorConfig = array();
    }
    return new CreatorConfig($this->getWorkingDirectory(), $creatorConfig);
  }

  /**
   * @return string
   */
  protected function getWorkingDirectory()
  {
    $creatorTempDirectory = Registry::getConfig()->creator->directory;
    return FS::joinPath($creatorTempDirectory, 'DynamicCreatorTest');
  }

  /**
   * @param string $websiteId
   *
   * @return \Cms\Creator\CreatorJobConfig
   */
  protected function getCreatorJobConfig($websiteId)
  {
    $publishConfig = array();
    return new CreatorJobConfig($websiteId, $publishConfig);
  }
}
 