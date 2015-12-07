<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Cms\Exception as CmsException;

class GetByIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdReturnSnippetAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $snippetId = 'TPLS-global00-test-0000-0001-000000000003-TPLS';
    $expectedSnippet = array(
      'id' => $snippetId,
      'name' => 'global_test_snippet_name_1-3',
      'description' => 'global_test_snippet_name_1-3',
      'category' => 'Inhalt',
      'readonly' => true,
      'sourcetype' => 'repo',
      'baselayout' => true,
      'pagetypes' => array('page'),
      'previewimageurl' => '/url/to/templateSnippet/'.$snippetId.'/assets/templateSnippet.svg',
      'content' => json_encode(array(array(
        'id' => 'MUNIT-00000000-0000-0000-0000-000000000003-MUNIT',
        'moduleId' => 'rz_image',
        'name' => 'rz_image_unit',
        'formValues' => array('imgHeight' => '70%'),
        'expanded' => true,
        'children' => array(),
        'description' => 'my_description_search_string',
        'ghostContainer' => false,
        'visibleFormGroups' => array('4ae8ae60-50d9-4a88-a924-cf96cec69941'),
      ))),
    );

    // ACT
    $actualSnippet = $dao->getById($source, $snippetId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
    $actualSnippetData = $actualSnippet->toArray();
    foreach($expectedSnippet as $attributeName => $expectedValue) {
      $this->assertEquals($expectedSnippet[$attributeName], $actualSnippetData[$attributeName]);
    }
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByIdThrowExceptionIfSnippetNotExists()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedExceptionData = array(
      'id' => 'SNIPPET-ID-NOT-EXISTS',
      'websiteid' => $websiteId,
    );

    // ACT
    try {
      $dao->getById($source, 'SNIPPET-ID-NOT-EXISTS');
      $actualException = null;
    } catch (CmsException $actualException) {}

    // ASSERT
    $this->assertInstanceOf('\Cms\Exception', $actualException);
    $this->assertEquals(1602, $actualException->getCode());
    $actualExceptionData = $actualException->getData();
    $this->assertEquals($expectedExceptionData, $actualExceptionData);
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 