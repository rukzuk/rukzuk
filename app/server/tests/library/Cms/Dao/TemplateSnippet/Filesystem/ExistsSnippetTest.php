<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Cms\Exception as CmsException;

class ExistsSnippetTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_existsSnippetReturnTrueIfSnippetExists()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'TPLS-global00-test-0000-0001-000000000001-TPLS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $exists = $dao->existsSnippet($source, $snippetId);

    // ASSERT
    $this->assertTrue($exists);
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_existsSnippetReturnFalseIfSnippetNotExists()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'SNIPPET-ID-NOT-EXISTS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $exists = $dao->existsSnippet($source, $snippetId);

    // ASSERT
    $this->assertFalse($exists);
  }


  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 