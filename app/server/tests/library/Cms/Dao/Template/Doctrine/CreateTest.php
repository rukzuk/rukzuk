<?php
namespace Cms\Dao\Template\Doctrine;

use Cms\Dao\Template\Doctrine as TemplateDao,
    Test\Seitenbau\TransactionTestCase;

/**
 * Create Test
 *
 * @package      Test
 * @subpackage   Dao
 */
class CreateTest extends TransactionTestCase
{
  /**
   * @var \Cms\Dao\Template\Doctrine
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
  public function createShouldUpdateUsedModuleIds()
  {
    //
    // ARRANGE
    //
    $websiteId = 'SITE-56c01626-da62-4446-a79a-22b5cb86955f-SITE';
    $newAttributes = array(
      'name' => 'Dao\Template\CreateTest\createShouldUpdateUsedModuleIds',
      'content' => '[{"test":"entry","moduleId":"usedmoduleid1","children":[{"moduleId":"usedmoduleid2"}]}]'
    );
    $expectedUsedModuleIdsAfterCreate = array('usedmoduleid1', 'usedmoduleid2');
    sort($expectedUsedModuleIdsAfterCreate);
    
    //
    // ACT
    //
    $template = $this->dao->create($websiteId, $newAttributes);
    $usedModuleIdsAfterCreate = $template->getUsedmoduleids();
    sort($usedModuleIdsAfterCreate);
    
    //
    // ASSERT
    //
    $this->assertInternalType('array', $usedModuleIdsAfterCreate);
    $this->assertEquals(2, count($usedModuleIdsAfterCreate));
    $this->assertEquals($expectedUsedModuleIdsAfterCreate, $usedModuleIdsAfterCreate);
  }

  /**
   * @test
   * @group library
   */
  public function createShouldCreateChecksumAsExpected()
  {
    //
    // ARRANGE
    //
    $websiteId = 'SITE-56c01626-da62-4446-a79a-22b5cb86955f-SITE';
    $newAttributes = array(
      'name' => 'Dao\Template\CreateTest\createShouldUpdateUsedModuleIds',
      'content' => '[{"test":"entry","moduleId":"usedmoduleid1","children":[{"moduleId":"usedmoduleid2"}]}]'
    );
    $expectedContentChecksum = md5($newAttributes['content']);

    //
    // ACT
    //
    $template = $this->dao->create($websiteId, $newAttributes);

    //
    // ASSERT
    //
    $this->assertSame($expectedContentChecksum, $template->getContentchecksum());
  }
}