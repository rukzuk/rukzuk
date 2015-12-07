<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Cms\Data\TemplateSnippet AS DataTemplateSnippet;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Cms\Exception as CmsException;

class SearchInContentTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider searchInContentReturnsExpectedSnippets_provider
   */
  public function test_SearchInContentReturnsExpectedSnippets($needle, $expectedIds)
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $foundSnippets = $dao->searchInContent($source, $needle);

    // ASSERT
    $this->assertCount(count($expectedIds), $foundSnippets);
    foreach($foundSnippets as $actualSnippet) {
      $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
      $this->assertContains($actualSnippet->getId(), $expectedIds);
    }
  }

  /**
   * @return array
   */
  public function searchInContentReturnsExpectedSnippets_provider()
  {
    return array(
      array(
        'MDB-00000000-0000-0000-0000-000000000001-MDB',
        array(
          'TPLS-global00-test-0000-0001-000000000001-TPLS',
        ),
      ),
      array(
        'my_description_search_string',
        array(
          'TPLS-global00-test-0000-0001-000000000001-TPLS',
          'TPLS-global00-test-0000-0001-000000000003-TPLS',
        ),
      ),
    );
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_searchInContentReturnsEmptyListIfNeedleNotFound()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $foundSnippets = $dao->searchInContent($source, 'THIS-STRING-IS-NOT-FOUND');

    // ASSERT
    $this->assertCount(0, $foundSnippets);
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 