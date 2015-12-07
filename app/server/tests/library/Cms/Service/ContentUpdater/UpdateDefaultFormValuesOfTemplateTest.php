<?php


namespace Cms\Service\ContentUpdater;

use Seitenbau\Registry;
use Test\Seitenbau\ServiceTestCase;
use Cms\Service\ContentUpdater as ContentUpdaterService;
use Cms\Service\Template as TemplateService;
use Seitenbau\FileSystem as FS;

/**
 * Class UpdateDefaultFormValuesOfTemplateTest
 *
 * @package Cms\Service\ContentUpdater
 *
 * @group contentUpdater
 */
class UpdateDefaultFormValuesOfTemplateTest extends ServiceTestCase
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
    $templateId = 'TPL-update00-defa-ult0-form-values000001-TPL';
    $orgTemplateContentChecksum = 'theoriginaltemplatechecksum';
    $contentUpdaterService = new ContentUpdaterService('ContentUpdater');
    $templateService = new TemplateService('Template');
    $expectedContentJson = FS::readContentFromFile(FS::joinPath($this->jsonFilesDirectory,
      'expected_template_content.json'));
    $expectedContent = json_decode($expectedContentJson);

    // ACT
    $contentUpdaterService->updateDefaultFormValuesOfTemplate($websiteId, $templateId);

    // ASSERT
    $template = $templateService->getById($templateId, $websiteId);
    $this->assertNotSame($orgTemplateContentChecksum, $template->getContentchecksum());
    $templateContent = json_decode($template->getContent());
    $this->assertEquals($expectedContent, $templateContent);
  }
}