<?php


namespace Cms;

use \Test\Rukzuk\AbstractTestCase;
use \Seitenbau\Registry;
use \Seitenbau\FileSystem as FS;
use \Cms\Reparser;
use \Cms\Data\Modul as ModulData;
use \Dual\Render\CMS as CmsConst;


class Reparser_Test_Mock extends Reparser
{
  static protected $phpunit_moduleBusiness;
  static protected $phpunit_pageUnitIds = array();

  public static function getModuleBusiness()
  {
    return static::$phpunit_moduleBusiness;
  }

  protected static function createNewPageUnitId($templateUnit)
  {
    $templateUnitId = $templateUnit['id'];
    if (!isset(static::$phpunit_pageUnitIds[$templateUnitId])) {
      static::$phpunit_pageUnitIds[$templateUnitId] = 0;
    }
    static::$phpunit_pageUnitIds[$templateUnitId]++;
    return $templateUnitId . '::' . static::$phpunit_pageUnitIds[$templateUnitId];
  }

  public static function phpunit_setModuleBusiness($moduleBusiness)
  {
    static::$phpunit_moduleBusiness = $moduleBusiness;
  }

  public static function phpunit_resetPageUnitIds()
  {
    static::$phpunit_pageUnitIds = array();
  }
}


class ReparseContentTest extends AbstractTestCase
{
  protected $jsonTestFilePath;
  protected $websiteId = 'THE-WEBSITE-ID';

  protected function setUp()
  {
    parent::setUp();
    Reparser_Test_Mock::phpunit_resetPageUnitIds();
  }

  /**
   * Checks, if the page content for a new page ist created as expected
   *
   * @test
   */
  public function test_creating_new_page_content()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      null,
      null,
      'templateContent.json',
      FS::joinPath('NewPage', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_NEW,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, if the new template units successfully inserted into page content
   *
   * @test
   */
  public function test_added_new_units_to_template()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath('NewUnits', 'orgPageContent.json'),
      FS::joinPath('NewUnits', 'orgPageTemplateContent.json'),
      'templateContent.json',
      FS::joinPath('NewUnits', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, if removed template units will also be removed from page content
   *
   * @test
   */
  public function test_removed_units_from_template()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath('RemovedUnits', 'orgPageContent.json'),
      FS::joinPath('RemovedUnits', 'orgPageTemplateContent.json'),
      'templateContent.json',
      FS::joinPath('RemovedUnits', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, if changed unit values will be reparsed successful
   *
   * @test
   */
  public function test_reparse_units()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath('ReparseUnits', 'orgPageContent.json'),
      FS::joinPath('ReparseUnits', 'orgPageTemplateContent.json'),
      FS::joinPath('ReparseUnits', 'templateContent.json'),
      FS::joinPath('ReparseUnits', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, that page value NULL, won't be overridden with template value (bug RZ-1316)
   *
   * @test
   */
  public function test_reparse_units_with_page_values_changed_to_null()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath('Bug-RZ-1316', 'orgPageContent.json'),
      FS::joinPath('Bug-RZ-1316', 'orgPageTemplateContent.json'),
      FS::joinPath('Bug-RZ-1316', 'templateContent.json'),
      FS::joinPath('Bug-RZ-1316', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, moved units will be reparsed successful
   *
   * @test
   */
  public function test_moved_units()
  {
    // ARRANGE / ACT / ASSERT
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath('MoveUnits', 'orgPageContent.json'),
      FS::joinPath('MoveUnits', 'orgPageTemplateContent.json'),
      FS::joinPath('MoveUnits', 'templateContent.json'),
      FS::joinPath('MoveUnits', 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, moved units into ghost container will be reparsed successful
   *
   * @test
   */
  public function test_moved_units_into_ghost_container()
  {
    $this->markTestSkipped('Skipped tests, because moved units should not be removed');
    return;

    // ARRANGE / ACT / ASSERT
    $subDir = 'MoveUnitIntoGhostContainer';
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath($subDir, 'orgPageContent.json'),
      FS::joinPath($subDir, 'orgPageTemplateContent.json'),
      FS::joinPath($subDir, 'templateContent.json'),
      FS::joinPath($subDir, 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Checks, moved units out of ghost container
   *
   * @test
   */
  public function test_moved_units_out_of_ghost_container()
  {
    // ARRANGE / ACT / ASSERT
    $subDir = 'MoveUnitOutOfGhostContainer';
    $this->assertReparseContentSuccess(
      $this->websiteId,
      FS::joinPath($subDir, 'orgPageContent.json'),
      FS::joinPath($subDir, 'orgPageTemplateContent.json'),
      FS::joinPath($subDir, 'templateContent.json'),
      FS::joinPath($subDir, 'reparsedPageContent.json'),
      Reparser_Test_Mock::TYPE_REPARSE,
      $this->createModules($this->websiteId)
    );
  }

  /**
   * Call the Renderer and check the reparsed page content
   */
  protected function assertReparseContentSuccess($websiteId, $pageContentJsonFile,
                                                 $pageTemplateContentJsonFile,
                                                 $templateContentJsonFile,
                                                 $expectedReparsedPageContentJsonFile,
                                                 $reparseType, $modules)
  {
    // ARRANGE
    $pageContentAsJson = $this->getTestFileContent($pageContentJsonFile);
    $pageTemplateContentAsJson = $this->getTestFileContent($pageTemplateContentJsonFile);
    $templateContentAsJson = $this->getTestFileContent($templateContentJsonFile);
    $expectedReparsedPageContentAsJson = $this->getTestFileContent($expectedReparsedPageContentJsonFile);
    $expectedReparsedPageContent = json_decode($expectedReparsedPageContentAsJson, true);

    $moduleMock = $this->getModuleBusinessMock(array($websiteId => $modules));
    Reparser_Test_Mock::phpunit_setModuleBusiness($moduleMock);

    // ACT
    $actualNewPageContent = Reparser_Test_Mock::reparseContent($websiteId, $pageContentAsJson,
      $pageTemplateContentAsJson, $templateContentAsJson, $reparseType);

    // ASSERT
    $this->assertEquals($expectedReparsedPageContent, $actualNewPageContent, '', 0, 9999);
  }

  /**
   * @param string $websiteId
   * @param string $moduleId
   * @param array  $attributes
   *
   * @return ModulData
   */
  protected function createModuleDataObject($websiteId, $moduleId, array $attributes = array())
  {
    $module = new ModulData();
    $module->setWebsiteid($websiteId);
    $module->setId($moduleId);

    if (array_key_exists('name', $attributes)) {
      $module->setName($attributes['name']);
    }
    if (array_key_exists('moduleType', $attributes)) {
      $module->setModuletype($attributes['moduleType']);
    }
    if (array_key_exists('form', $attributes)) {
      $module->setForm($attributes['form']);
    }
    if (array_key_exists('formValues', $attributes)) {
      $module->setFormvalues($attributes['formValues']);
    }
    return $module;
  }

  /**
   * @param array $modules
   *
   * @return \Cms\Business\Modul|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function getModuleBusinessMock(array $modules = array())
  {
    $className = '\\Cms\\Business\\Modul';
    $mock = $this->getMockBuilder($className)->disableOriginalConstructor()
      ->setMethods(array('getById'))
      ->getMock();
    $mock->expects($this->any())->method('getById')
      ->will($this->returnCallback(function ($moduleId, $websiteId) use ($modules) {
        if (!isset($modules[$websiteId])) {
          throw new \RuntimeException('Module "' . $websiteId . '/' . $moduleId . '" not exists');
        }
        foreach ($modules[$websiteId] as $module) {
          if ($moduleId == $module->getId()) {
            return $module;
          }
        }
        throw new \RuntimeException('Module "' . $websiteId . '/' . $moduleId . '" not exists');
      }));
    return $mock;
  }

  /**
   * @param string|null $contentFile
   *
   * @return string
   */
  protected function getTestFileContent($contentFile)
  {
    if (empty($contentFile)) {
      return '';
    }

    if (!isset($this->jsonTestFilePath)) {
      $this->jsonTestFilePath = Registry::getConfig()->test->reparser->storage->directory;
    }
    return file_get_contents(FS::joinPath($this->jsonTestFilePath, $contentFile));
  }

  /**
   * @param $websiteId
   *
   * @return array
   */
  protected function createModules($websiteId)
  {
    $modules = array(
      $this->createModuleDataObject($websiteId, 'rz_root', array()),
      $this->createModuleDataObject($websiteId, 'rz_image', array()),
      $this->createModuleDataObject($websiteId, 'rz_test_unit', array()),
      $this->createModuleDataObject($websiteId, 'rz_style_padding_margin', array(
        'moduleType' => CmsConst::MODULE_TYPE_EXTENSION
      )),
    );
    return $modules;
  }
}

/**
 *
 * orgPageContent:
 * "page_value_changed_to_null-template_values_not_changed" : {
 * "foo-8": null,
 * "bar-8": "foo"
 * },
 * "page_value_changed_to_null-template_values_also_changed" : {
 * "foo-9": "bar",
 * "bar-9": null
 * }


 *
 *
 * orgPageTemplateContent:
 * "page_value_changed_to_null-template_values_not_changed" : {
 * "foo-8": "bar",
 * "bar-8": "foo"
 * },
 * "page_value_changed_to_null-template_values_also_changed" : {
 * "foo-9": "bar",
 * "bar-9": "foo"
 * }


 *
 *
 * templateContent:
 * "page_value_changed_to_null-template_values_not_changed" : {
 * "foo-8": "bar",
 * "bar-8": "foo"
 * },
 * "page_value_changed_to_null-template_values_also_changed" : {
 * "foo-9": "bar",
 * "bar-9": "new_foo-template"
 * }


 *
 *
 * reparsedPageContent:
 * "page_value_changed_to_null-template_values_not_changed" : {
 * "foo-8": null,
 * "bar-8": "foo"
 * },
 * "page_value_changed_to_null-template_values_also_changed" : {
 * "foo-9": "bar",
 * "bar-9": null
 * }
 *
 *
 * ------------------------------------------------------
 *
 * Check if template value removed, page value (if not changed) should also be removed
 *
 */
