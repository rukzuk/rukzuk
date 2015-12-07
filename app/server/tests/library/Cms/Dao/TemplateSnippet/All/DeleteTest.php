<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class DeleteTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_deleteShouldDeleteDbSnippetFirst()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    $doctrineSnippets = $this->createDataSnippets(1, '_doctrine', array(
      'websiteid' => $websiteId,
      'id' => $templateSnippetId,
      'sourcetype' => DataTemplateSnippet::SOURCE_LOCAL,
      'readonly' => false,
    ));

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(true));
    $doctrineDao->expects($this->once())
      ->method('delete')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(true));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->never())
      ->method('delete');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->delete($source, $templateSnippetId);

    // ASSERT
    $this->assertTrue($actualResult);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Dao\TemplateSnippet\ReadOnlyException
   * @expectedExceptionCode 1613
   */
  public function test_deleteShouldThrowReadonlyExceptionIfFsSnippetIsDeleting()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'TPLS-global00-test-0000-0001-000000000001-TPLS';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $allDao = $this->getAllDao();

    // ACT
    $allDao->delete($source, $templateSnippetId);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 1602
   */
  public function test_deleteShouldThrowNotExistsExceptionIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $allDao = $this->getAllDao();

    // ACT
    $allDao->delete($source, $templateSnippetId);
  }
}
 