<?php
namespace Application\Controller\Import;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\ImportControllerTestCase,
    Seitenbau\FileSystem as FS,
    Cms\Business\Export as ExportBusiness,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock;
use Cms\Data\Modul as DataModule;
use Cms\Service\Import as ImportService;

/**
 * ImportController Modules Import Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class ModulesTest extends ImportControllerTestCase
{
  protected $sqlFixtures = array('application_contoller_Import_ModulesTest.json');

  protected function setUp()
  {
    $this->markTestSkipped(
      'TODO: fix test because of new chunk upload'
    );
    parent::setUp();

    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Modul', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });
  }
  
  protected function tearDown()
  {
    $this->clearFakeUpload();

    ModuleWriteableMock::tearDown();

    parent::tearDown();
  }
  
  /**
   * @test
   * @group  integration
   * @ticket SBCMS-977
   */
  public function importModuleShouldThrowValidationErrorOnNotAllowedType()
  {
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_website_export_not_allowed_type.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);
    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpi5teim';
    $fileInputname = 'import';

    $websiteId = 'SITE-rs13up2c-exm0-4ea8-a477-4ee79e8e62pa-SITE';
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s","allowedType":"%s"}',
      $websiteId,
      $fileInputname,
      ExportBusiness::EXPORT_MODE_MODULE  // Nur Websites duerfen importiert werden
    );
    
    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);
    
    $this->dispatch($requestUri);
    $response = $this->getValidatedErrorResponse();

    $this->assertHeaderContains('Content-Type', 'text/plain');

    $reponseError = $response->getError();
    $this->assertEquals(31, $reponseError[0]->code);
    
    $assertionMessage = "Import files weren't removed as expected";
    $this->assertFileNotExists($expectedImportFile, $assertionMessage);
    $this->assertFileNotExists($importUnzipDirectory, $assertionMessage);
  }  
  /**
   * @test
   * @group integration
   */
  public function moduleImportShouldImportAsExpectedForOnlyNewModuleIds()
  {
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_modules_export_non_existing_modules.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpI2f3im';
    $fileInputname = 'import';

    $alreadyExistingWebsiteId = 'SITE-im13up2c-20da-4ea8-a477-4ee79e8e62mo-SITE';

    $this->assertHasNoMedias($alreadyExistingWebsiteId);

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $alreadyExistingWebsiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);
    $responseBody = $this->getResponseBody();
    $this->assertInternalType('string', $responseBody);
    $this->assertNotNull($responseBody);
    $response = new Response($responseBody);
    $this->assertTrue($response->getSuccess(), $responseBody);

    $this->assertHeaderContains('Content-Type', 'text/plain');

    $this->assertHasNoMedias($alreadyExistingWebsiteId);
    $testImportMediaDirectory = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'media'
      . DIRECTORY_SEPARATOR . $alreadyExistingWebsiteId;
    if (is_dir($testImportMediaDirectory)) {
      $assertionMessage = sprintf(
        "Mediafiles imported (%s)",
        $testImportMediaDirectory
      );
      $mediaTree = DirectoryHelper::getRecursiveAsJson($testImportMediaDirectory);
      $this->assertEquals('[]', $mediaTree, $assertionMessage);
    }

    $expectedNewModulIds = array(
      'MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c0-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c1-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c2-MODUL',
      'MODUL-0rap5eb8-0df3-47e9-exmo-90ae9d96d3c3-MODUL',
    );
    $requestUri = sprintf(
      '/modul/getall/params/{"websiteId":"%s"}',
      $alreadyExistingWebsiteId
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertInternalType('array', $responseData->modules);
    $modules = $responseData->modules;
    $actualNewModulIds = array();
    foreach ($modules as $module)
    {
      $this->assertInstanceOf('stdClass', $module);
      $this->assertObjectHasAttribute('id', $module);
      $actualNewModulIds[] = $module->id;
      
      $expectedImportAssetsTree = file_get_contents(FS::joinPath(
        $testFilesDirectory, 'trees', 'import', 'modules',
        'expected_assets_for_non_existing_modules', $module->id.'.tree'
      ));
      $actualAssetPath = $this->moduleService->getAssetsPath($alreadyExistingWebsiteId, $module->id);
      $this->assertEquals(
        $expectedImportAssetsTree,
        DirectoryHelper::getRecursiveAsJson($actualAssetPath, true),
        "Tree mismatch between import module (".$module->id.") assets directory tree"
          . " and expected assets directory tree"
      );
    }
    sort($expectedNewModulIds);
    sort($actualNewModulIds);

    $this->assertSame($expectedNewModulIds, $actualNewModulIds);

    $this->assertHasNoMedias($alreadyExistingWebsiteId);

    if (strstr($importUnzipDirectory, $testImportDirectory))
    {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory))
      {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile))
      {
        unlink($expectedImportFile);
      }
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider importModuleDataProvider
   */
  public function moduleImportShouldImportExportVersionAsExpected(
          $testImportFilename, $websiteId, $expectedNewModules)
  {
    /*
    print "--------------------------------------\n";
    print "ImportFilename: ".$testImportFilename."\n";
    print "WebsiteId: ".$websiteId."\n";
    print_r($expectedNewModules);
    */
    
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . str_replace('.zip', '', $testImportFilename);
    $fileInputname = 'import';

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);

    $this->assertHeaderContains('Content-Type', 'text/plain');
    $responseBody = $this->getResponseBody();
    $this->assertInternalType('string', $responseBody);
    $this->assertNotNull($responseBody);
    $response = new Response($responseBody);
    $this->assertTrue($response->getSuccess(), $responseBody);

    $expectedNewModuleData = array();
    foreach($expectedNewModules as $moduleId => &$nextNewModule)
    {
      $expectedNewModuleData[$moduleId] = $nextNewModule['data'];
      ksort($expectedNewModuleData[$moduleId]);
    }

    $requestUri = sprintf(
      '/modul/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($requestUri);
    $responseBody = $this->getResponseBody();

    $this->assertInternalType('string', $responseBody);
    $this->assertNotNull($responseBody);
    $response = new Response($responseBody);
    $this->assertTrue($response->getSuccess(), $responseBody);

    $responseData = $response->getData();
    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertInternalType('array', $responseData->modules);
    $modules = $responseData->modules;
    $actualNewModules = array();
    foreach ($modules as $module)
    {
      $this->assertInstanceOf('stdClass', $module);
      $this->assertObjectHasAttribute('id', $module);
      $actualNewModules[$module->id] = get_object_vars($module);
      ksort($actualNewModules[$module->id]);
    }
    
    ksort($expectedNewModuleData);
    ksort($actualNewModules);
    $this->assertEquals($expectedNewModuleData, $actualNewModules);

    foreach ($expectedNewModules as $nextExpectedNewModuleId => $nextExpectedNewModule)
    {
      $testAssetsModuleDirectory = $this->moduleService->getAssetsPath($websiteId, $nextExpectedNewModuleId);
      $testAssetFile = FS::joinPath($testAssetsModuleDirectory, 'file.txt');
    
      $assertionMessage = sprintf(
        "Asset file '%s' existiert nicht",
        $testAssetFile
      );
      $this->assertFileExists($testAssetFile, $assertionMessage);
      
      $this->assertSame(
        $nextExpectedNewModuleId,
        file_get_contents($testAssetFile)
      );

      $dataPath = $this->moduleService->getDataPath($websiteId, $nextExpectedNewModuleId);
      if ($nextExpectedNewModule['isLegacyModule']) {
        $testModuleLegacyCode = $this->getModuleLegacyCode($dataPath);
        $this->assertSame($nextExpectedNewModule['renderer'], $testModuleLegacyCode['renderer']);
        $this->assertSame($nextExpectedNewModule['css'], $testModuleLegacyCode['css']);
        $this->assertSame($nextExpectedNewModule['header'], $testModuleLegacyCode['header']);
      } else {
        $this->assertFileExists(FS::joinPath($dataPath, 'manifest.json'));
        $this->assertFileExists(FS::joinPath($dataPath, 'form.json'));
        $this->assertFileExists(FS::joinPath($dataPath, 'custom.json'));
        $this->assertFileExists(FS::joinPath($dataPath, $nextExpectedNewModuleId.'.php'));
      }
    }

    $this->removeImportFileAndUnzipDirectory($expectedImportFile);
  }


  /**
   * @return array
   */
  public function importModuleDataProvider()
  {
    $websiteId1 = 'SITE-import0m-odul-0ver-ion0-1z1z0z000000-SITE';
    $websiteId2 = 'SITE-import0m-odul-0ver-ion0-1z2z0z000000-SITE';
    $websiteId3 = 'SITE-import0m-odul-0ver-ion0-1z5z0z000000-SITE';
    
    return array(
      // Import Version 1.2.0
      array(
        'test_modules_export_version_1.2.0.zip',
        $websiteId2,
        array(
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000001-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000001-MODUL',
              'name'                    => 'name_1',
              'description'             => 'description_1',
              'version'                 => 'version_1',
              'category'                => 'category_1',
              'icon'                    => 'icon_1',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'root',
              'allowedChildModuleType'  => 'default',
              'reRenderRequired'        => true,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => "<?php namespace Dual\Render; ?>\nrenderer_1",
            'css' => "<?php namespace Dual\Render; ?>\ncss_1",
            'header' => "<?php namespace Dual\Render; ?>\nheader_1",
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000002-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000002-MODUL',
              'name'                    => 'name_2',
              'description'             => 'description_2',
              'version'                 => 'version_2',
              'category'                => 'category_2',
              'icon'                    => 'icon_2',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'default',
              'allowedChildModuleType'  => 'extension',
              'reRenderRequired'        => false,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => "<?php namespace Dual\Render; ?>\nrenderer_2",
            'css' => "<?php namespace Dual\Render; ?>\ncss_2",
            'header' => "<?php namespace Dual\Render; ?>\nheader_2",
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000003-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000003-MODUL',
              'name'                    => 'name_3',
              'description'             => 'description_3',
              'version'                 => 'version_3',
              'category'                => 'category_3',
              'icon'                    => 'icon_3',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'extension',
              'allowedChildModuleType'  => '*',
              'reRenderRequired'        => true,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => "<?php namespace Dual\Render; ?>\nrenderer_3",
            'css' => "<?php namespace Dual\Render; ?>\ncss_3",
            'header' => "<?php namespace Dual\Render; ?>\nheader_3",
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000004-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000004-MODUL',
              'name'                    => 'name_4',
              'description'             => 'description_4',
              'version'                 => 'version_4',
              'category'                => 'category_4',
              'icon'                    => 'icon_4',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'default',
              'allowedChildModuleType'  => 'root',
              'reRenderRequired'        => false,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => "<?php namespace Dual\Render; ?>\nrenderer_4",
            'css' => "<?php namespace Dual\Render; ?>\ncss_4",
            'header' => "<?php namespace Dual\Render; ?>\nheader_4",
            'isLegacyModule' => true,
          ),
        ),
      ),
      // Import Version 1.3.0
      array(
        'test_modules_export_version_1.3.0.zip',
        $websiteId2,
        array(
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000001-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000001-MODUL',
              'name'                    => 'name_1',
              'description'             => 'description_1',
              'version'                 => 'version_1',
              'category'                => 'category_1',
              'icon'                    => 'icon_1',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'root',
              'allowedChildModuleType'  => 'default',
              'reRenderRequired'        => true,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => 'renderer_1',
            'css' => 'css_1',
            'header' => 'header_1',
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000002-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000002-MODUL',
              'name'                    => 'name_2',
              'description'             => 'description_2',
              'version'                 => 'version_2',
              'category'                => 'category_2',
              'icon'                    => 'icon_2',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'default',
              'allowedChildModuleType'  => 'extension',
              'reRenderRequired'        => false,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => 'renderer_2',
            'css' => 'css_2',
            'header' => 'header_2',
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000003-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000003-MODUL',
              'name'                    => 'name_3',
              'description'             => 'description_3',
              'version'                 => 'version_3',
              'category'                => 'category_3',
              'icon'                    => 'icon_3',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'extension',
              'allowedChildModuleType'  => '*',
              'reRenderRequired'        => true,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => 'renderer_3',
            'css' => 'css_3',
            'header' => 'header_3',
            'isLegacyModule' => true,
          ),
          'MODUL-import0m-odul-0ver-ion0-1z2z0z000004-MODUL' => array(
            'data' => array(
              'websiteId'               => $websiteId2,
              'id'                      => 'MODUL-import0m-odul-0ver-ion0-1z2z0z000004-MODUL',
              'name'                    => 'name_4',
              'description'             => 'description_4',
              'version'                 => 'version_4',
              'category'                => 'category_4',
              'icon'                    => 'icon_4',
              'form'                    => array(),
              'formValues'              => array(),
              'moduleType'              => 'default',
              'allowedChildModuleType'  => 'root',
              'reRenderRequired'        => false,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => null,
            ),
            'renderer' => 'renderer_4',
            'css' => 'css_4',
            'header' => 'header_4',
            'isLegacyModule' => true,
          ),
        ),
      ),
      // Import Version 1.5.0
      array(
        'test_modules_export_version_1.5.0.zip',
        $websiteId3,
        array(
          'rz_modul_no_1_with_export_version_1_5_0_import' => array(
            'data' => array(
              'websiteId'               => $websiteId3,
              'id'                      => 'rz_modul_no_1_with_export_version_1_5_0_import',
              'name'                    => 'name_1_5_0',
              'description'             => 'description_1_5_0',
              'version'                 => 'version_1_5_0',
              'category'                => 'category_1_5_0',
              'icon'                    => 'icon_1_5_0',
              'form'                    => array(),
              'formValues'              => (object)array(),
              'moduleType'              => 'default',
              'allowedChildModuleType'  => '*',
              'reRenderRequired'        => true,
              'overwritten'             => false,
              'sourceType'              => DataModule::SOURCE_LOCAL,
              'ghostContainerMode'      => 'force_on',
            ),
            'isLegacyModule' => false,
          ),
        ),
      ),
    );
  }

  /**
   * @test
   * @group integration
   */
  public function moduleImportShouldImportAsExpectedAlsoForDeepFolders()
  {
    $config = Registry::getConfig();

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_modules_export_deep.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpe7f3im';
    $fileInputname = 'import';

    $alreadyExistingWebsiteId = 'SITE-rs13up2c-exmd-4ea8-a477-4ee79e8e62mo-SITE';

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $alreadyExistingWebsiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);

    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $responseBody = $this->getResponseBody();
    $this->assertInternalType('string', $responseBody);
    $this->assertNotNull($responseBody);
    $response = new Response($responseBody);
    $this->assertTrue($response->getSuccess(), $responseBody);

    $expectedNewModulIds = array('MODUL-0c1e62c1-023c-42c7-8628-f2cb4236eb08-MODUL');
    $actualNewModulIds = array();
    
    $modules = $this->moduleService->getAll($alreadyExistingWebsiteId);
    foreach ($modules as $module)
    {
      $moduleId = $module->getId();
      $actualNewModulIds[] = $moduleId;
      
      $expectedImportAssetsTree = file_get_contents(FS::joinPath(
        $testFilesDirectory, 'trees', 'import', 'modules',
        'expected_assets_modules_deep', $moduleId.'.tree'
      ));
      $actualAssetPath = $this->moduleService->getAssetsPath($alreadyExistingWebsiteId, $moduleId);
      $this->assertEquals(
        $expectedImportAssetsTree,
        DirectoryHelper::getRecursiveAsJson($actualAssetPath, true),
        "Tree mismatch between import module (".$moduleId.") assets directory tree"
          . " and expected assets directory tree"
      );
    }
    sort($expectedNewModulIds);
    sort($actualNewModulIds);

    $this->assertSame($expectedNewModulIds, $actualNewModulIds);

    if (strstr($importUnzipDirectory, $testImportDirectory)) {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory)) {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile)) {
        unlink($expectedImportFile);
      }
    }
  }
  /**
   * @test
   * @group integration
   */
  public function moduleImportWithoutAlbumDataAndMediaShouldNotCreateADefaultAlbum()
  {
    $config = Registry::getConfig();
    $noRenameUploadFileArray = array(
      'import' => array(
        'uploadFile' =>
          array('doNotRename' => 1),
      )
    );
    $noRenameUploadFileConfig = new \Zend_Config(
      $noRenameUploadFileArray
    );
    $config->merge($noRenameUploadFileConfig);

    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_module_medialess.zip';

    $testImportFile = $testFilesDirectory . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpe7f3im';
    $fileInputname = 'import';

    $websiteId = 'SITE-rs13up2c-exmd-4ea8-a477-nom79e8e62mo-SITE';

    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);

    $this->assertHeaderContains('Content-Type', 'text/plain');

    $request = sprintf(
      '/album/getall/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('albums', $responseData);
    $this->assertInternalType('array', $responseData->albums);
    $albumsOfWebiste = $responseData->albums;

    $assertionMessage = 'There are non expected associated albums';
    $this->assertEmpty($albumsOfWebiste, $assertionMessage);

    if (strstr($importUnzipDirectory, $testImportDirectory)) 
    {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory)) 
      {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile)) 
      {
        unlink($expectedImportFile);
      }
    }
  }

  /**
   * @test
   * @integration
   */
  public function moduleImportShouldRespondWithConflictingModules()
  {
    $websiteId = 'SITE-im12maec-20dr-4ea8-a477-m4e79e8e62c0-SITE';
    
    $config = Registry::getConfig();
    $testImportDirectory = $config->import->directory;
    $testFilesDirectory = $config->test->files->directory;
    $testImportFilename = 'test_export_0_modules_conflict.zip';
    
    $testImportFile = $testFilesDirectory 
      . DIRECTORY_SEPARATOR . 'test_exports'
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $expectedImportFile = $testImportDirectory
      . DIRECTORY_SEPARATOR . $testImportFilename;
    $importUnzipDirectory = str_replace('.zip', '', $expectedImportFile);

    $assertionMessage = sprintf(
      "Import file '%s' existiert nicht",
      $testImportFile
    );
    $this->assertFileExists($testImportFile, $assertionMessage);

    $testImportTmpFile = DIRECTORY_SEPARATOR . 'tmp'
      . DIRECTORY_SEPARATOR . 'phpe7f3im';
    $fileInputname = 'import';
    
    $requestUri = sprintf(
      '/import/file/params/{"websiteid":"%s","fileinputname":"%s"}',
      $websiteId,
      $fileInputname
    );

    $this->assertFakeUpload($fileInputname, $testImportFile, $testImportTmpFile);

    $this->dispatch($requestUri);

    $this->assertHeaderContains('Content-Type', 'text/plain');
    
    $responseBody = $this->getResponseBody();
    $response = new Response($responseBody);
    $this->assertFalse($response->getSuccess(), $responseBody);
    
    $error = $response->getError();
    $this->assertEquals(11, $error[0]->code);
    
    $responseData = $response->getData();
    
    $this->assertObjectHasAttribute('importId', $responseData);
    $this->assertObjectHasAttribute('conflict', $responseData);
    
    $conflictData = $responseData->conflict;
    
    $this->assertObjectHasAttribute('templates', $conflictData);
    $this->assertObjectHasAttribute('modules', $conflictData);
    $this->assertObjectHasAttribute('media', $conflictData);

    $expectedConflictingModules = array();
    
    for ($i = 0; $i < 3; ++$i) {
      $conflictingModule = new \StdClass;
      $conflictingModule->id = 'MODUL-0rap5eb8-0df3-47e9-conf-90ae9d96d3c' . $i . '-MODUL';
      $conflictingModule->oldName = 'module ' . $i;
      $conflictingModule->newName = 'Page_Export_' . $i;
      $expectedConflictingModules[] = $conflictingModule;
    }
    
    $actualConflictingModules = $conflictData->modules;
    usort($actualConflictingModules, function($a, $b)
    {
      return strcmp($a->id, $b->id);
    });
    
    $this->assertTrue(count($actualConflictingModules) === count($expectedConflictingModules));
    $this->assertEquals($expectedConflictingModules, $actualConflictingModules);
    
    $errorData = $response->getError();
    
    $this->assertEquals(11, $errorData[0]->code);
    $errorMessage = \Cms\Error::getMessageByCode(11);
    $this->assertEquals($errorMessage, $errorData[0]->text);
    
    if (file_exists($testImportTmpFile)) {
      unlink($testImportTmpFile);
    }
    
    if (strstr($importUnzipDirectory, $testImportDirectory)) 
    {
      DirectoryHelper::removeRecursiv($importUnzipDirectory, $testImportDirectory);
      if (is_dir($importUnzipDirectory)) 
      {
        rmdir($importUnzipDirectory);
      }
      if (file_exists($expectedImportFile)) 
      {
        unlink($expectedImportFile);
      }
    }
    DirectoryHelper::clearLatchDirectory();
  }

  /**
   * @param string $moduleDataPath
   * @return array
   */
  protected function getModuleLegacyCode($moduleDataPath)
  {
    $codeArray = array();

    $rendererFile = FS::joinPath($moduleDataPath, ImportService::MODULE_LEGACY_FILE_RENDERER);
    if (file_exists($rendererFile)) {
      $codeArray['renderer'] = FS::readContentFromFile($rendererFile);
    } else {
      $codeArray['renderer'] = null;
    }

    $cssFile = FS::joinPath($moduleDataPath, ImportService::MODULE_LEGACY_FILE_CSS);
    if (file_exists($cssFile)) {
      $codeArray['css'] = FS::readContentFromFile($cssFile);
    } else {
      $codeArray['css'] = null;
    }

    $headerFile = FS::joinPath($moduleDataPath, ImportService::MODULE_LEGACY_FILE_HEADER);
    if (file_exists($headerFile)) {
      $codeArray['header'] = FS::readContentFromFile($headerFile);
    } else {
      $codeArray['header'] = null;
    }

    return $codeArray;
  }
}