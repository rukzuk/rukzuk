<?php


namespace Cms;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Business\Export\TestableExportBusiness;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Cms\Service\TemplateSnippet as ServiceTemplateSnippet;


class TemplateSnippetExportTest extends ServiceTestCase
{
  protected $sqlFixtures = array('library_Cms_Business_TemplateSnippetExportTest.json');

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
  public function test_exportTemplateSnippetShouldOnlyExportLocalSnippets()
  {
    // ARRANGE
    $websiteId = 'SITE-export0t-empl-ates-nipp-et0test00001-SITE';
    $exportFileName = 'templateSnippetExport';
    $allSnippets = $this->getAllTemplateSnippets($websiteId);
    $expectedExportedSnippetIds = array();
    foreach($allSnippets as $snippet) {
      if ($snippet->getSourceType() == SourceItem::SOURCE_LOCAL) {
        $expectedExportedSnippetIds[] = $snippet->getId();
      }
    }
    // check if min. one global snippet exists
    $this->assertLessThan(count($allSnippets), count($expectedExportedSnippetIds));

    // ACT
    $this->business->exportWebsite($websiteId, $exportFileName, true, false);

    // ASSERT
    $createdZipFiles = $this->business->phpunit_getCreatedZipFiles();
    $this->assertCount(1, $createdZipFiles);
    $exportedSnippetIds = $this->getTemplateSnippetIdsFromExportFile($createdZipFiles[0]);
    sort($exportedSnippetIds);
    sort($expectedExportedSnippetIds);
    $this->assertEquals($expectedExportedSnippetIds,$exportedSnippetIds);
  }

  /**
   * @param string $zipFilePath
   *
   * @return array
   */
  protected function getTemplateSnippetIdsFromExportFile($zipFilePath)
  {
    $exportedSnippetIds = array();
    $exportZip = new \ZipArchive();
    $exportZip->open($zipFilePath);
    for( $i = 0; $i < $exportZip->numFiles; $i++ ){
      $stat = $exportZip->statIndex( $i );
      if (preg_match('/([^\/]+?)\/templateSnippet.json/', $stat['name'], $matches)) {
        $exportedSnippetIds[] = $matches[1];
      }
    }
    return array_unique($exportedSnippetIds);
  }

  /**
   * @param string $websiteId
   *
   * @return Data\TemplateSnippet[]
   */
  protected function getAllTemplateSnippets($websiteId)
  {
    $templateSnippetService = new ServiceTemplateSnippet('TemplateSnippet');
    return $templateSnippetService->getAll($websiteId);
  }
}
 