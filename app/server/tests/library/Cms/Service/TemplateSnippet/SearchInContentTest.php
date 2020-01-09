<?php
namespace Cms\Service\TemplateSnippet;

use Cms\Service\TemplateSnippet as TemplateSnippetService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * TemplateSnippet Service SearchInContentTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class SearchInContentTest extends ServiceTestCase
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
  public function searchInContentShouldReturnExpectedTemplateSnippets()
  {
    $websiteId = 'SITE-template-snip-pet0-test-000000000001-SITE';
    $numberOfTemplateSnippets = 2;
    
    $contentWithTextpart = \Seitenbau\Json::encode(array(
        (object) array(
          'id' => 'MUNIT-00000000-0000-0000-0000-000000000001-MUNIT',
          'name' => 'TEST_FINDTHISTEXT_TEST',
          'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL',
    )));
    $contentWithoutTextpart = \Seitenbau\Json::encode(array(
        (object) array(
          'id' => 'MUNIT-00000000-0000-0000-0000-000000000001-MUNIT',
          'name' => 'TEST_NOTFOUND_TEST',
          'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000001-MODUL',
    )));
    
    $templateSnippetIdsWithTextPart = $this->createTemplateSnippetsForWebsite(
        $websiteId, $numberOfTemplateSnippets, $contentWithTextpart);
    $templateSnippetIdsWithoutTextPart = $this->createTemplateSnippetsForWebsite(
        $websiteId, $numberOfTemplateSnippets, $contentWithoutTextpart);
    
    $foundTemplateSnippets = $this->service->searchInContent($websiteId, 'FINDTHISTEXT');
  
    $this->assertInternalType('array', $foundTemplateSnippets);
    $this->assertSame($numberOfTemplateSnippets, count($foundTemplateSnippets), 
      'Es wurden mehr oder weniger TemplateSnippets zurueckgegeben, als erwartet werden');
    
    foreach ($foundTemplateSnippets as $templateSnippet)
    {
      $this->assertInstanceOf('Cms\Data\TemplateSnippet', $templateSnippet);
      $this->assertContains($templateSnippet->getId(), $templateSnippetIdsWithTextPart);
      $key = array_search($templateSnippet->getId(), $templateSnippetIdsWithTextPart);
      unset($templateSnippetIdsWithTextPart[$key]);
    }
    
    $this->assertSame(0, count($templateSnippetIdsWithTextPart), 
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
  private function createTemplateSnippetsForWebsite($websiteId, $number = 1, $content = '[]')
  {
    $templateSnippetIds = array();
    
    $createdCounter = 0;
    while ($createdCounter < $number)
    {
      $createdCounter++;
      $create = array(
        'name' => __METHOD__ . '_' . rand(),
        'content' => $content,
      );
      $templateSnippet = $this->service->create($websiteId, $create);
      $templateSnippetIds[] = $templateSnippet->getId();
    }
    
    return $templateSnippetIds;
  }
}