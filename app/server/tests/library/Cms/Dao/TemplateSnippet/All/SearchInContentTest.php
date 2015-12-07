<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class SearchInContentTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_searchInContentCalledAllDao()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $doctrineSnippets = $this->createDataSnippets(3, '_doctrine', array(
      'websiteid' => $websiteId,
      'sourcetype' => DataTemplateSnippet::SOURCE_LOCAL,
      'readonly' => false,
    ));
    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('searchInContent')
      ->will($this->returnValue($doctrineSnippets));

    $filesystemSnippets = $this->createDataSnippets(3, '_filesystem', array(
      'websiteid' => $websiteId,
      'sourcetype' => DataTemplateSnippet::SOURCE_REPOSITORY,
      'readonly' => true,
    ));
    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('searchInContent')
      ->will($this->returnValue($filesystemSnippets));

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $allExpectedSnippets = array_merge($filesystemSnippets, $doctrineSnippets);

    // ACT
    $allSnippets = $allDao->searchInContent($source, 'the searched value');

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $this->assertCount(6, $allSnippets);
    $this->assertEquals($allExpectedSnippets, $allSnippets);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_searchInContentReturnDbSnippetIfFsIsOverwritten()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $doctrineSnippets = $this->createDataSnippets(1, '_doctrine', array(
      'websiteid' => $websiteId,
      'id' => $templateSnippetId,
      'sourcetype' => DataTemplateSnippet::SOURCE_LOCAL,
      'readonly' => false,
    ));
    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('searchInContent')
      ->will($this->returnValue($doctrineSnippets));

    $filesystemSnippets = $this->createDataSnippets(1, '_filesystem', array(
      'websiteid' => $websiteId,
      'id' => $templateSnippetId,
      'sourcetype' => DataTemplateSnippet::SOURCE_REPOSITORY,
      'readonly' => true,
    ));
    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('searchInContent')
      ->will($this->returnValue($filesystemSnippets));

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $allExpectedSnippets = $doctrineSnippets;

    // ACT
    $allSnippets = $allDao->searchInContent($source, 'the searched value');

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $this->assertCount(1, $allSnippets);
    $this->assertEquals($allExpectedSnippets, $allSnippets);
    $this->assertTrue($allSnippets[0]->isOverwritten());
  }
}
 