<?php


namespace Cms\Dao\TemplateSnippet\All;

use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;

class CopyToNewWebsiteTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_copyToNewWebsiteOnlyCallDoctrineDao()
  {
    // ARRANGE
    $sourceFrom = $this->getTemplateSnippetSource('WEBSITE-ID-FROM', array());
    $sourceTo = $this->getTemplateSnippetSource('WEBSITE-ID-TO', array());
    $ids = array('SNIPPET-ID-1', 'SNIPPET-ID-2');

    $doctrineDao = $this->getDoctrineDaoMock();
    $doctrineDao->expects($this->once())
      ->method('copyToNewWebsite')
      ->with($this->equalTo($sourceFrom), $this->equalTo($sourceTo), $this->equalTo($ids))
      ->will($this->returnValue(true));

    $filesystemDao = $this->getFilesystemDaoMock();
    $filesystemDao->expects($this->never())
      ->method('copyToNewWebsite');

    $allDao = $this->getAllDao($doctrineDao, $filesystemDao);

    // ACT
    $actualResult = $allDao->copyToNewWebsite($sourceFrom, $sourceTo, $ids);

    // ASSERT
    $this->assertTrue($actualResult);
  }
}
 