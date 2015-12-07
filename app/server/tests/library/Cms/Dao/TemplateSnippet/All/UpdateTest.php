<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class UpdateTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_updateOnlyCallDoctrineDaoUpdate()
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
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet->getId()))
      ->will($this->returnValue(true));
    $doctrineDao->expects($this->once())
      ->method('update')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet))
      ->will($this->returnValue($expectedSnippet));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet->getId()))
      ->will($this->returnValue(true));
    $filesystemDao->expects($this->never())
      ->method('update');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->update($source, $expectedSnippet);

    // ASSERT
    $this->assertEquals($expectedSnippet, $actualResult);
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_updateShouldCreateSnippetInDbIfExistsOnFs()
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
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet->getId()))
      ->will($this->returnValue(false));
    $doctrineDao->expects($this->once())
      ->method('create')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet))
      ->will($this->returnValue($expectedSnippet));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($expectedSnippet->getId()))
      ->will($this->returnValue(true));
    $filesystemDao->expects($this->never())
      ->method('update');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->update($source, $expectedSnippet);

    // ASSERT
    $this->assertEquals($expectedSnippet, $actualResult);
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
  public function test_updateShouldThrowExpectedExceptionIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $snippets = $this->createDataSnippets(1, '_doctrine');
    $expectedSnippet = $snippets[0];
    $allDao = $this->getAllDao();

    // ACT
    $allDao->update($source, $expectedSnippet);
  }
}
 