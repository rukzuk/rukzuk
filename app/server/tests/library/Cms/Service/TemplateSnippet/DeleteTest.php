<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * TemplateSnippet Service DeleteTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class DeleteTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\TemplateSnippet
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new TemplateSnippetService('TemplateSnippet');
  }

  /**
   * @test
   * @group library
   */
  public function deleteShouldDeleteExpectedTemplateSnippet()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';

    $create = array(
      'name' => 'TEMPLATE_SNIPPET_NAME_'.__METHOD__,
      'description' => 'TEMPLATE_SNIPPET_DESCRIPTION_'.__METHOD__,
      'category' => 'TEMPLATE_SNIPPET_CATEGORY_'.__METHOD__,
      'content' => '[]',
    );
    $createdSnippet = $this->service->create($websiteId, $create);
    $snippetIdToDelete = $createdSnippet->getId();
    
    $existsSnippetToDelete = false;
    $beforeDeleteTemplates = $this->service->getAll($websiteId);
    foreach($beforeDeleteTemplates as $snippet) {
      if ($snippetIdToDelete == $snippet->getId()) {
        $existsSnippetToDelete = true;
        break;
      }
    }
    $this->assertTrue($existsSnippetToDelete, sprintf(
      'The TemplateSnippet (%d) for the delete test does not exists.', $snippetIdToDelete
    ));

    $this->service->delete($websiteId, $snippetIdToDelete);

    $afterDeleteTemplates = $this->service->getAll($websiteId);

    $this->assertSame(
      (count($beforeDeleteTemplates) - 1),
      count($afterDeleteTemplates)
    );
    
    foreach($afterDeleteTemplates as $snippet) {
      if ($snippetIdToDelete == $snippet->getId()) {
        $this->fail(sprintf(
          'Deleted TemplateSnippet "%s" exists.', $snippetIdToDelete
        ));
      }
    }
  }
}