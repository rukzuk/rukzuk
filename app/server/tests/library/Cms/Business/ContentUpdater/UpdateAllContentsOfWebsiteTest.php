<?php


namespace Cms\Business\ContentUpdater;

use Test\Seitenbau\ServiceTestCase;
use Cms\Business\ContentUpdater as ContentUpdaterBusiness;
use Cms\Service\Template as TemplateService;
use Cms\Service\TemplateSnippet as TemplateSnippetService;
use Cms\Service\Page as PageService;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;

/**
 * Class UpdateAllContentsOfWebsiteTest
 *
 * @package Cms\Business\ContentUpdater
 *
 * @group contentUpdater
 */
class UpdateAllContentsOfWebsiteTest extends ServiceTestCase
{
  protected $sqlFixtures = array('ContentUpdater.json');

  /**
   * @var string
   */
  protected $jsonFilesDirectory;

  protected function setUp()
  {
    parent::setUp();

    $this->jsonFilesDirectory = FS::joinPath(
      Registry::getConfig()->test->contentupdater->storage->directory,
      'UpdateDefaultFormValuesOfTemplateTest');
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_updateAllContentsOfWebsite_success()
  {
    // ARRANGE
    $websiteId = 'SITE-update00-defa-ult0-form-values000001-SITE';
    $templateSnippetId = 'TPLS-update00-defa-ult0-form-values000001-TPLS';
    $templateId = 'TPL-update00-defa-ult0-form-values000001-TPL';
    $pageId = 'PAGE-update00-defa-ult0-form-values000001-PAGE';
    $orgTemplateContentChecksum = 'theoriginaltemplatechecksum';
    $contentUpdaterBusiness = new ContentUpdaterBusiness('ContentUpdater');
    $templateSnippetService = new TemplateSnippetService('TemplateSnippet');
    $templateService = new TemplateService('Template');
    $pageService = new PageService('Page');

    $expectedSnippetContent = json_decode(FS::readContentFromFile(FS::joinPath($this->jsonFilesDirectory,
      'expected_templatesnippet_content.json')));
    $expectedTemplateContent = json_decode(FS::readContentFromFile(FS::joinPath($this->jsonFilesDirectory,
      'expected_template_content.json')));
    $expectedPageContent = json_decode(FS::readContentFromFile(FS::joinPath($this->jsonFilesDirectory,
      'expected_page_1_content.json')));

    // ACT
    $contentUpdaterBusiness->updateAllContentsOfWebsite($websiteId);

    // ASSERT
    $snippet = $templateSnippetService->getById($websiteId, $templateSnippetId);
    $actualSnippetContent = json_decode($snippet->getContent());
    $this->assertEquals($expectedSnippetContent, $actualSnippetContent);

    $template = $templateService->getById($templateId, $websiteId);
    $this->assertNotSame($orgTemplateContentChecksum, $template->getContentchecksum());
    $actualTemplateContent = json_decode($template->getContent());
    $this->assertEquals($expectedTemplateContent, $actualTemplateContent);

    $page = $pageService->getById($pageId, $websiteId);
    $this->assertNotSame($orgTemplateContentChecksum, $page->getTemplatecontentchecksum());
    $actualPageContent = json_decode($page->getContent());
    $this->assertFormValuesOfPageContent($expectedPageContent, $actualPageContent);
  }

  /**
   * @param array $expectedPageContent
   * @param array $actualPageContent
   */
  protected function assertFormValuesOfPageContent($expectedPageContent, $actualPageContent)
  {
    foreach ($expectedPageContent as $pos => $expectedUnit) {
      $currentUnitPath[] = $expectedUnit->id;
      $this->assertArrayHasKey($pos, $actualPageContent);
      $actualUnit = $actualPageContent[$pos];
      $this->assertEquals($expectedUnit->formValues, $actualUnit->formValues, sprintf(
        'Failed asserting that formValues of the unit %s equals', $expectedUnit->id));
      if (property_exists($expectedUnit, 'children') && count($expectedUnit->children) > 0) {
        $this->assertFormValuesOfPageContent($expectedUnit->children, $actualUnit->children);
      }
      if (property_exists($expectedUnit, 'ghostChildren') && count($expectedUnit->ghostChildren) > 0) {
        $this->assertFormValuesOfPageContent($expectedUnit->ghostChildren, $actualUnit->ghostChildren);
      }
    }
  }
}