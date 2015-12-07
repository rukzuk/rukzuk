<?php

namespace Cms\Dao\Page;

use Test\Cms\Dao\Page\AbstractDaoTestCase;

class PageDaoTest extends AbstractDaoTestCase
{
  protected $sqlFixtures = array('library_Cms_Dao_Page_PageDaoTest.json');

  /**
   * @test
   * @group library
   */
  public function test_getIdsByWebsiteIdSuccess()
  {
    // ARRANGE
    $dao = $this->createPageDao();
    $websiteId = 'SITE-dao0page-doct-rine-test-000000000001-SITE';
    $expectedPageIds = array(
      'PAGE-dao0page-doct-rine-test-100000000001-PAGE',
      'PAGE-dao0page-doct-rine-test-200000000001-PAGE',
      'PAGE-dao0page-doct-rine-test-300000000001-PAGE',
      'PAGE-dao0page-doct-rine-test-310000000001-PAGE',
    );
    sort($expectedPageIds);

    // ACT
    $actualPageIds = $dao->getIdsByWebsiteId($websiteId);
    sort($actualPageIds);

    // ASSERT
    $this->assertEquals($expectedPageIds, $actualPageIds);
  }

  /**
   * @test
   * @group library
   */
  public function test_getIdsByWebsiteIdSuccessEvenIfNoPageExistInWebsite()
  {
    // ARRANGE
    $dao = $this->createPageDao();
    $websiteId = 'SITE-dao0page-doct-rine-test-000000000002-SITE';

    // ACT
    $actualPageIds = $dao->getIdsByWebsiteId($websiteId);

    // ASSERT
    $this->assertInternalType('array', $actualPageIds);
    $this->assertEmpty($actualPageIds);
  }

  /**
   * @test
   * @group library
   */
  public function test_create_shouldCreateTemplateContentChecksumAsExpected()
  {
    //
    // ARRANGE
    //
    $dao = $this->createPageDao(true);
    $websiteId = 'SITE-dao0page-doct-rine-test-000000000002-SITE';
    $newAttributes = array(
      'name' => 'Dao\Template\CreateTest\createShouldUpdateUsedModuleIds',
      'templatecontent' => '[{"test":"'.time().'","moduleId":"usedmoduleid1","children":[{"moduleId":"usedmoduleid2"}]}]',
      'content' => '[]',
    );
    $expectedTemplateContentChecksum = md5($newAttributes['templatecontent']);

    //
    // ACT
    //
    $page = $dao->create($websiteId, $newAttributes);

    //
    // ASSERT
    //
    $this->assertSame($expectedTemplateContentChecksum, $page->getTemplatecontentchecksum());
  }

  /**
   * @test
   * @group library
   */
  public function test_update_shouldCreateTemplateContentChecksumAsExpected()
  {
    //
    // ARRANGE
    //
    $dao = $this->createPageDao(true);
    $websiteId = 'SITE-dao0page-doct-rine-test-000000000001-SITE';
    $pageId = 'PAGE-dao0page-doct-rine-test-100000000001-PAGE';
    $newAttributes = array(
      'templatecontent' => '[{"test":"'.time().'","moduleId":"usedmoduleid1","children":[{"moduleId":"usedmoduleid2"}]}]',
      'content' => '[]',
    );
    $expectedTemplateContentChecksum = md5($newAttributes['templatecontent']);
    $page = $dao->getById($pageId, $websiteId);
    $templateContentChecksumBeforeUpdate = $page->getTemplatecontentchecksum();

    //
    // ACT
    //
    $page = $dao->update($pageId, $websiteId, $newAttributes);

    //
    // ASSERT
    //
    $this->assertSame($expectedTemplateContentChecksum, $page->getTemplatecontentchecksum());
    $this->assertNotSame($templateContentChecksumBeforeUpdate, $page->getTemplatecontentchecksum());
  }
}
 