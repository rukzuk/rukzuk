<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Cms\Exception as CmsException;

class GetByIdsTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdsReturnSnippetsAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedSnippetIds = array(
      'TPLS-global00-test-0000-0001-000000000001-TPLS',
      'TPLS-global00-test-0000-0001-000000000003-TPLS',
    );

    // ACT
    $allSnippets = $dao->getByIds($source, $expectedSnippetIds);

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    foreach($allSnippets as $actualSnippet) {
      $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
      $this->assertContains($actualSnippet->getId(), $expectedSnippetIds);
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdsThrowExceptionIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedExceptionData = array(
      'id' => 'SNIPPET-ID-NOT-EXISTS',
      'websiteid' => 'WEBSITE-ID',
    );

    // ACT
    try {
      $dao->getByIds($source, array('TPLS-global00-test-0000-0001-000000000001-TPLS',
        'SNIPPET-ID-NOT-EXISTS', 'TPLS-global00-test-0000-0001-000000000003-TPLS'));
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
   */
  public function test_getByIdsReturnEmptyArrayIfNoSnippetIdGiven()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);

    // ACT
    $allSnippets = $dao->getByIds($source, array());

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $this->assertCount(0, $allSnippets);
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 