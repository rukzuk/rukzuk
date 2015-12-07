<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class ExistsSnippetTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_existsSnippetShouldCheckDbSnippetFirst()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(true));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->never())
      ->method('existsSnippet');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->existsSnippet($source, $templateSnippetId);

    // ASSERT
    $this->assertTrue($actualResult);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_existsSnippetShouldCheckDbSnippetFirstAndFsAfter()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(false));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(true));

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->existsSnippet($source, $templateSnippetId);

    // ASSERT
    $this->assertTrue($actualResult);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_existsSnippetShouldReturnFalsIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(false));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(false));

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->existsSnippet($source, $templateSnippetId);

    // ASSERT
    $this->assertFalse($actualResult);
  }
}
 