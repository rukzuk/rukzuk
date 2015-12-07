<?php


namespace Cms\Service\ContentUpdater;

use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Cms\Service\ContentUpdater as ContentUpdaterService;
use Cms\Service\TemplateSnippet as TemplateSnippetService;
use Seitenbau\FileSystem as FS;

/**
 * Class UpdateDefaultFormValuesOfTemplateSnippetsTest
 *
 * @package Cms\Service\ContentUpdater
 *
 * @group contentUpdater
 */
class UpdateDefaultFormValuesOfTemplateSnippetsTest extends ServiceTestCase
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
  public function test_updateDefaultFormValuesOfTemplate_success()
  {
    // ARRANGE
    $websiteId = 'SITE-update00-defa-ult0-form-values000001-SITE';
    $templateSnippetId = 'TPLS-update00-defa-ult0-form-values000001-TPLS';
    $contentUpdaterService = new ContentUpdaterService('ContentUpdater');
    $templateSnippetService = new TemplateSnippetService('TemplateSnippet');
    $expectedContent = json_decode(FS::readContentFromFile(FS::joinPath($this->jsonFilesDirectory,
      'expected_templatesnippet_content.json')));

    // ACT
    $contentUpdaterService->updateDefaultFormValuesOfTemplateSnippet($websiteId, $templateSnippetId);

    // ASSERT
    $templateSnippet = $templateSnippetService->getById($websiteId, $templateSnippetId);
    $actualContent = json_decode($templateSnippet->getContent());
    $this->assertEquals($expectedContent, $actualContent);
  }
}