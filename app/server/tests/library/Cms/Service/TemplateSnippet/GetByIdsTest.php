<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * TemplateSnippet Service GetByIdsTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetByIdsTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\TemplateSnippet
   */
  private $service;

  public function setUp()
  {
    parent::setUp();
    
    $this->service = new TemplateSnippetService('TemplateSnippet');
  }

  /**
   * @test
   * @group library
   */
  public function getByIdsShouldReturnExpectedTemplateSnippets()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $numberOfTemplateSnippets = 2;
    
    $templateSnippetIds = $this->createTemplateSnippetsForWebsite($websiteId, $numberOfTemplateSnippets);
    
    $getByIdsTemplateSnippets = $this->service->getByIds($websiteId, $templateSnippetIds);
  
    $this->assertInternalType('array', $getByIdsTemplateSnippets);
    $this->assertSame($numberOfTemplateSnippets, count($getByIdsTemplateSnippets), 
      'Es wurden mehr oder weniger TemplateSnippets zurueckgegeben, als erwartet werden');
    
    foreach ($getByIdsTemplateSnippets as $getByIdsTemplateSnippet)
    {
      $this->assertInstanceOf('Cms\Data\TemplateSnippet', $getByIdsTemplateSnippet);
      $this->assertContains($getByIdsTemplateSnippet->getId(), $templateSnippetIds);
      $key = array_search($getByIdsTemplateSnippet->getId(), $templateSnippetIds);
      unset($templateSnippetIds[$key]);
    }
    
    $this->assertSame(0, count($templateSnippetIds), 
      'Es wurden nicht alle erwarteten TemplateSnippets zurueckgegeben');
  }
  
  /**
   * Legt fuer eine angegebene Website ein oder mehrere TemplateSnippets an
   * 
   * Die TemplateSnippet-IDs werden zurueckgegbene
   * 
   * @param string $websiteId
   * @param int $number 
   * @return  array
   */
  private function createTemplateSnippetsForWebsite($websiteId, $number = 1)
  {
    $templateSnippetIds = array();
    
    $createdCounter = 0;
    while ($createdCounter < $number)
    {
      $createdCounter++;
      $create = array(
        'name' => __METHOD__ . '_' . rand(),
        'content' => '[]',
      );
      $templateSnippet = $this->service->create($websiteId, $create);
      $templateSnippetIds[] = $templateSnippet->getId();
    }
    
    return $templateSnippetIds;
  }
}