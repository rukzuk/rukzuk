<?php


namespace Cms;

use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Cms\Business\Export as ExportBusiness;
use Test\Rukzuk\ConfigHelper;



class ExportTest extends ServiceTestCase
{
  /**
   * @var \Cms\Business\Export
   */
  protected $business;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new ExportBusiness('Export');
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2302
   */
  public function test_exportWebsiteShouldThrowQuotaExceptionAsExpected()
  {
    // ARRANGE
    ConfigHelper::mergeIntoConfig(array('quota' => array('exportAllowed' => false)));

    // ACT
    $this->business->exportWebsite('WEBSITE-ID');
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2302
   */
  public function test_exportModuleShouldThrowQuotaExceptionAsExpected()
  {
    // ARRANGE
    ConfigHelper::mergeIntoConfig(array('quota' => array('exportAllowed' => false)));

    // ACT
    $this->business->export(ExportBusiness::EXPORT_MODE_MODULE, 'WEBSITE-ID', array());
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2302
   */
  public function test_exportTemplatesnippetShouldThrowQuotaExceptionAsExpected()
  {
    // ARRANGE
    ConfigHelper::mergeIntoConfig(array('quota' => array('exportAllowed' => false)));

    // ACT
    $this->business->export(ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET, 'WEBSITE-ID', array());
  }
}
 