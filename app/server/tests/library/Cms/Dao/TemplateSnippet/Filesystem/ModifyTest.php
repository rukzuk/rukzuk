<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Cms\Data\TemplateSnippet AS DataTemplateSnippet;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Cms\Exception as CmsException;

class ModifyTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1613
   */
  public function test_deleteByIdsThrowReadOnlyExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'TPLS-global00-test-0000-0001-000000000001-TPLS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $dao->deleteByIds($source, array($snippetId));
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_deleteByIdsThrowNotExistsExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'SNIPPET-ID-NOT-EXISTS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedExceptionData = array(
      'id' => $snippetId,
      'websiteid' => $websiteId,
    );

    // ACT
    try {
      $dao->deleteByIds($source, array($snippetId));
      $actualException = null;
    } catch (CmsException $actualException) {}

    // ASSERT
    $this->assertInstanceOf('\Cms\Exception', $actualException);
    $this->assertEquals(1602, $actualException->getCode());
    $actualExceptionData = $actualException->getData();
    $this->assertEquals($expectedExceptionData, $actualExceptionData);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1613
   */
  public function test_deleteByWebsiteIdThrowReadOnlyExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $dao->deleteByWebsiteId($source, $websiteId);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1606
   */
  public function test_createThrowReadOnlyExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $snippet = new DataTemplateSnippet();

    // ACT
    $dao->create($source, $snippet);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1613
   */
  public function test_updateThrowReadOnlyExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'TPLS-global00-test-0000-0001-000000000001-TPLS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $snippet = new DataTemplateSnippet();
    $snippet->setId($snippetId);

    // ACT
    $dao->update($source, $snippet);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_updateThrowNotExistsExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $snippetId = 'SNIPPET-ID-NOT-EXISTS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedExceptionData = array(
      'id' => $snippetId,
      'websiteid' => $websiteId,
    );
    $snippet = new DataTemplateSnippet();
    $snippet->setId($snippetId);

    // ACT
    try {
      $dao->update($source, $snippet);
      $actualException = null;
    } catch (CmsException $actualException) {}

    // ASSERT
    $this->assertInstanceOf('\Cms\Exception', $actualException);
    $this->assertEquals(1602, $actualException->getCode());
    $actualExceptionData = $actualException->getData();
    $this->assertEquals($expectedExceptionData, $actualExceptionData);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1613
   */
  public function test_copyToNewWebsiteThrowReadOnlyExceptionAsExpected()
  {
    // ARRANGE
    $dao = $this->getFilesystemDao();
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $sourceFrom = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets_to'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $sourceTo = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $dao->copyToNewWebsite($sourceFrom, $sourceTo);
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 