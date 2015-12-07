<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;

class CreateTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_createOnlyCallDoctrineDao()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $snippets = $this->createDataSnippets(1, '_doctrine');
    $expectedSnippet = $snippets[0];

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('create')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet))
      ->will($this->returnValue($expectedSnippet));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->never())
      ->method('create');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->create($source, $expectedSnippet);

    // ASSERT
    $this->assertEquals($expectedSnippet, $actualResult);
  }
}
 