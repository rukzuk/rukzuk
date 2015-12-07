<?php


namespace Cms\Dao\TemplateSnippet\All;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class GetByIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdShouldReturnDbSnippetFirst()
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
      ->method('getById')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue($doctrineSnippets[0]));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(false));
    $filesystemDao->expects($this->never())
      ->method('getById');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualSnippets = $allDao->getById($source, $templateSnippetId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippets);
    $this->assertEquals($doctrineSnippets[0]->getId(), $actualSnippets->getId());
    $this->assertEquals(DataTemplateSnippet::SOURCE_LOCAL, $actualSnippets->getSourceType());
    $this->assertFalse($actualSnippets->isOverwritten());
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdShouldReturnDbSnippetFirstAndSetOverwrittenToTrueIfFsSnippetExists()
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
      ->method('getById')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue($doctrineSnippets[0]));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(true));
    $filesystemDao->expects($this->never())
      ->method('getById');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualSnippets = $allDao->getById($source, $templateSnippetId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippets);
    $this->assertEquals($doctrineSnippets[0]->getId(), $actualSnippets->getId());
    $this->assertEquals(DataTemplateSnippet::SOURCE_LOCAL, $actualSnippets->getSourceType());
    $this->assertTrue($actualSnippets->isOverwritten());
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdShouldReturnFsSnippetIfDbSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    $filesystemSnippets = $this->createDataSnippets(1, '_filesystem', array(
      'websiteid' => $websiteId,
      'id' => $templateSnippetId,
      'sourcetype' => DataTemplateSnippet::SOURCE_REPOSITORY,
      'readonly' => true,
    ));

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('existsSnippet')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue(false));
    $doctrineDao->expects($this->never())
      ->method('getById');

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->never())
      ->method('existsSnippet');
    $filesystemDao->expects($this->once())
      ->method('getById')
      ->with($this->equalTo($source), $this->equalTo($templateSnippetId))
      ->will($this->returnValue($filesystemSnippets[0]));

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualSnippets = $allDao->getById($source, $templateSnippetId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippets);
    $this->assertEquals($filesystemSnippets[0]->getId(), $actualSnippets->getId());
    $this->assertEquals(DataTemplateSnippet::SOURCE_REPOSITORY, $actualSnippets->getSourceType());
    $this->assertFalse($actualSnippets->isOverwritten());
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
  public function test_getByIdShouldThrowExpectedExceptionIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $templateSnippetId = 'THE-SNIPPET-ID';
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $allDao = $this->getAllDao();

    // ACT
    $allDao->getById($source, $templateSnippetId);
  }
}
 