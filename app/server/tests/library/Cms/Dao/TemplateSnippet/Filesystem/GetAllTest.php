<?php


namespace Cms\Dao\TemplateSnippet\Filesystem;

use Cms\Dao\Base\SourceItem;
use Test\Cms\Dao\TemplateSnippet\AbstractDaoTestCase;
use Cms\Dao\TemplateSnippet\Filesystem as DaoFilesystem;
use Seitenbau\FileSystem as FS;

class GetAllTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsSnippetAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedSnippets = array(
      'TPLS-global00-test-0000-0001-000000000001-TPLS' => array(
        'name' => 'global_test_snippet_name_1-1',
        'description' => 'global_test_snippet_name_1-1',
        'category' => 'Inhalt',
        'readonly' => true,
        'sourcetype' => 'repo',
        'baselayout' => false,
        'pagetypes' => array(),
        'previewimageurl' => null,
      ),
      'TPLS-global00-test-0000-0001-000000000002-TPLS' => array(
        'name' => 'global_test_snippet_name_1-2',
        'description' => 'global_test_snippet_name_1-2',
        'category' => 'Inhalt',
        'readonly' => true,
        'sourcetype' => 'repo',
        'baselayout' => false,
        'pagetypes' => array('page', 'rz_shop_product'),
        'previewimageurl' => null,
      ),
      'TPLS-global00-test-0000-0001-000000000003-TPLS' => array(
        'name' => 'global_test_snippet_name_1-3',
        'description' => 'global_test_snippet_name_1-3',
        'category' => 'Inhalt',
        'readonly' => true,
        'sourcetype' => 'repo',
        'baselayout' => true,
        'pagetypes' => array('page'),
        'previewimageurl' => '/url/to/templateSnippet/TPLS-global00-test-0000-0001-000000000003-TPLS/assets/templateSnippet.svg',
      ),
    );

    // ACT
    $allSnippets = $dao->getAll($source, 'ASC');

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $this->assertCount(3, $allSnippets);
    foreach ($allSnippets as $actualSnippet) {
      $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
      $expectedSnippet = $expectedSnippets[$actualSnippet->getId()];
      $actualSnippetData = $actualSnippet->toArray();
      foreach ($expectedSnippet as $attributeName => $expectedValue) {
        $this->assertEquals($expectedSnippet[$attributeName], $actualSnippetData[$attributeName]);
      }

      $this->assertArrayHasKey($actualSnippet->getId(), $expectedSnippets);
      $this->assertEquals($expectedSnippet['name'], $actualSnippet->getName());
      $this->assertEquals($expectedSnippet['description'], $actualSnippet->getDescription());
      $this->assertEquals($expectedSnippet['category'], $actualSnippet->getCategory());
      $this->assertTrue($actualSnippet->isReadonly());
      $this->assertEquals($actualSnippet::SOURCE_REPOSITORY, $actualSnippet->getSourceType());
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsSnippetOrderedAscAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedSnippetsIdsSortedAsc = array(
      'TPLS-global00-test-0000-0001-000000000001-TPLS',
      'TPLS-global00-test-0000-0001-000000000002-TPLS',
      'TPLS-global00-test-0000-0001-000000000003-TPLS',
    );

    // ACT
    $allSnippets = $dao->getAll($source, 'ASC');

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $sortPos = 0;
    foreach ($allSnippets as $actualSnippet) {
      $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
      $this->assertEquals($expectedSnippetsIdsSortedAsc[$sortPos++], $actualSnippet->getId());
    }
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getAllReturnsSnippetOrderedDescAsExpected()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $dao = $this->getFilesystemDao();
    $sourceItems = $this->getSourceItemsFromDirectory($this->getBaseDirectory('snippets'),
      '/url/to/templateSnippet', SourceItem::SOURCE_REPOSITORY, true);
    $source = $this->getTemplateSnippetSource($websiteId, $sourceItems);
    $expectedSnippetsIdsSortedDesc = array(
      'TPLS-global00-test-0000-0001-000000000003-TPLS',
      'TPLS-global00-test-0000-0001-000000000002-TPLS',
      'TPLS-global00-test-0000-0001-000000000001-TPLS',
    );

    // ACT
    $allSnippets = $dao->getAll($source, 'DESC');

    // ASSERT
    $this->assertInternalType('array', $allSnippets);
    $actualSnippetsIdsSortedDesc = array();
    foreach ($allSnippets as $actualSnippet) {
      $this->assertInstanceOf('\Cms\Data\TemplateSnippet', $actualSnippet);
      $actualSnippetsIdsSortedDesc[] = $actualSnippet->getId();
    }
    $this->assertEquals($expectedSnippetsIdsSortedDesc, $actualSnippetsIdsSortedDesc);
  }

  /**
   * @return \Cms\Dao\TemplateSnippet\Filesystem
   */
  protected function getFilesystemDao()
  {
    return new DaoFilesystem();
  }
}
 