<?php
namespace Cms\Dao\Template\Doctrine;

use Cms\Dao\Template\Doctrine as TemplateDao,
    Test\Seitenbau\TransactionTestCase;

/**
 * Update Test
 *
 * @package      Test
 * @subpackage   Dao
 */
class UpdateTest extends TransactionTestCase
{
  /**
   * @var Cms\Dao\Template\Doctrine
   */
  protected $dao;
  
  protected function setUp()
  {
    parent::setUp();

    $this->dao = new TemplateDao();
  }
  
  /**
   * @test
   * @group library
   */
  public function updateShouldMakeMd5ContentChecksum()
  {
    $templateId = 'TPL-eb0c38ed-1603-48ee-a8b8-a1c93c06e763-TPL';
    $websiteId = 'SITE-56c01626-da62-4446-a79a-22b5cb86955f-SITE';
    
    $template = $this->dao->getById($templateId, $websiteId);
    
    $contentChecksumBeforUpdate = $template->getContentchecksum();
    
    $newAttributes = array(
      'content' => '[{"test":"entry"}]'
    );
    
    $this->assertNotSame($contentChecksumBeforUpdate, $newAttributes['content']);
    
    $template = $this->dao->update($templateId, $websiteId, $newAttributes);
    
    $this->assertNotSame($contentChecksumBeforUpdate, $template->getContentchecksum());
    $this->assertSame(md5($newAttributes['content']), $template->getContentchecksum());
  }
  
  /**
   * @test
   * @group library
   */
  public function updateContentShouldUpdateUsedModuleIds()
  {
    //
    // ARRANGE
    //
    $templateId = 'TPL-eb0c38ed-1603-48ee-a8b8-a1c93c06e763-TPL';
    $websiteId = 'SITE-56c01626-da62-4446-a79a-22b5cb86955f-SITE';
    $newAttributes = array(
      'content' => '[{"test":"entry","moduleId":"usedmoduleid1","children":[{"moduleId":"usedmoduleid2"}]}]'
    );
    $expectedUsedModuleIdsAfterUpdate = array('usedmoduleid1', 'usedmoduleid2');
    sort($expectedUsedModuleIdsAfterUpdate);
    
    $template = $this->dao->getById($templateId, $websiteId);
    $contentBeforUpdate = $template->getContent();
    $usedModuleIdsBeforUpdate = $template->getUsedmoduleids();
    $this->assertNotSame($contentBeforUpdate, $newAttributes['content']);
    
    //
    // ACT
    //
    $template = $this->dao->update($templateId, $websiteId, $newAttributes);
    $usedModuleIdsAfterUpdate = $template->getUsedmoduleids();
    sort($usedModuleIdsAfterUpdate);
    
    //
    // ASSERT
    //
    $this->assertNotEquals($usedModuleIdsBeforUpdate, $usedModuleIdsAfterUpdate);
    $this->assertInternalType('array', $usedModuleIdsAfterUpdate);
    $this->assertEquals(2, count($usedModuleIdsAfterUpdate));
    $this->assertEquals($expectedUsedModuleIdsAfterUpdate, $usedModuleIdsAfterUpdate);
  }
}