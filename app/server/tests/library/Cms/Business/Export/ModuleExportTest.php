<?php
namespace Cms\Business;


use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Test\Cms\Business\Export\TestableExportBusiness;

/**
 * @package      Cms
 * @subpackage   Business\Export
 */
class ModuleExportTest extends ServiceTestCase
{
  const EXPORT_FILE_EXTENSION = \Cms\Business\Export::EXPORT_FILE_EXTENSION;

  /**
   * @var \Test\Cms\Business\Export\TestableExportBusiness
   */
  protected $business;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new TestableExportBusiness('Export');
  }
  protected function tearDown()
  {
    $this->business->phpunitTearDown();

    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function exportModule_CheckCdnUriSuccess()
  {
    $business = $this->business;
    $exportCdnUri = $business->export(TestableExportBusiness::EXPORT_MODE_MODULE,
      'SITE-11890b8a-d18f-4a24-8252-63f2aaf86a13-SITE', array('MODUL-42200254-956d-483d-847f-d3f296418306-MODUL'),
      'testcase.'.self::EXPORT_FILE_EXTENSION);
    $name = 'testcase '.self::EXPORT_FILE_EXTENSION;
    $this->assertStringEndsWith('/service/cdn/export/params/{"name":"'.$name.'"}', $exportCdnUri);
  }

  /**
   * @test
   * @group library
   */
  public function exportModule_ModuleNotExists()
  {
      $business = $this->business;
      $notExistingModuleId = 'MODUL-NONONO-MODUL';
      $this->assertException(
              function() use (&$business, $notExistingModuleId) {
                  $business->export(TestableExportBusiness::EXPORT_MODE_MODULE,
                    'SITE-11890b8a-d18f-4a24-8252-63f2aaf86a13-SITE', array($notExistingModuleId),
                    'testcase.'.self::EXPORT_FILE_EXTENSION);
              },
              array(),
              'Cms\Exception',
              function($actualException, &$message) use ($notExistingModuleId) {
                  $expected = 102;
                  if ($actualException->getCode() != $expected) {
                      $message = 'Failed asserting that exception code contains '.$actualException->getCode().'. Expected code '.$expected.'.';
                      return false;
                  }
                  $actualMessage = $actualException->getMessage();
                  $exceptedMessage = \Cms\Error::getMessageByCode($expected, array(
                          'id' => $notExistingModuleId,
                  ));
                  if ($exceptedMessage != $actualMessage) {
                      $message = 'Failed asserting that exception message "'.$actualMessage.'" contains "'.$exceptedMessage.'".';
                      return false;
                  }
                  return true;
              }
      );
  }

  /**
   * @test
   * @group library
   */
  public function exportModule_ZipFile_json()
  {
      $business = $this->business;
      $websiteId = 'SITE-11890b8a-d18f-4a24-8252-63f2aaf86a13-SITE';
      $module = 'MODUL-42200254-956d-483d-847f-d3f296418306-MODUL';

      $business->initExport($websiteId, $business::EXPORT_MODE_MODULE,
        "testcase.".self::EXPORT_FILE_EXTENSION);
      $zipfilepath = $business->exportModule($websiteId, array($module), true);
      $this->assertStringEndsWith('.'.self::EXPORT_FILE_EXTENSION, $zipfilepath);
      $zipfile = new \ZipArchive();
      $this->assertTrue($zipfile->open($zipfilepath));

      $manifestFile = $zipfile->getFromName("modules/".$module."/module/moduleManifest.json");

      $this->assertNotEmpty($manifestFile);

      $zipfile->close();
  }
}