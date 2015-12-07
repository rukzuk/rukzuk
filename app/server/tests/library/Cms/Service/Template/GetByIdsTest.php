<?php
namespace Cms\Service\Template;

use Cms\Service\Template as TemplateService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetByIdsTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetByIdsTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Template
   */
  private $service;

  public function setUp()
  {
    parent::setUp();
    
    $this->service = new TemplateService('Template');
  }

  /**
   * @test
   * @group library
   */
  public function getByIdsShouldReturnExpectedTemplates()
  {
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $numberOfTempaltes = 2;
    
    $templateIds = $this->createTemplatesForWebsite($websiteId, $numberOfTempaltes);
    
    $getByIdsTemplates = $this->service->getByIds($templateIds, $websiteId);

    $this->assertInternalType('array', $getByIdsTemplates);
    $this->assertSame($numberOfTempaltes, count($getByIdsTemplates), 
      'Es wurden mehr Templates zurueckgegeben, als erwartet werden');
    
    foreach ($getByIdsTemplates as $getByIdsTemplate)
    {
      $this->assertInstanceOf('Cms\Data\Template', $getByIdsTemplate);
      $this->assertContains($getByIdsTemplate->getId(), $templateIds);
      $key = array_search($getByIdsTemplate->getId(), $templateIds);
      unset($templateIds[$key]);
    }
    
    $this->assertSame(0, count($templateIds), 
      'Es wurden nicht alle erwarteten Templates zurueckgegeben');
  }
  
  /**
   * Legt fuer eine angegebene Website ein oder mehrere Templates an
   * 
   * Die Template-IDs werden zurueckgegbene
   * 
   * @param string $websiteId
   * @param int $number 
   * @return  array
   */
  private function createTemplatesForWebsite($websiteId, $number = 1)
  {
    $templateIds = array();
    
    $uniqName = uniqid(__METHOD__, true);
    $createdCounter = 0;
    while ($createdCounter < $number)
    {
      $createdCounter++;
      $create = array(
        'name' => $uniqName . '_'.$createdCounter,
        'content' => array(array('id' => 'TEST_CONTENT_GET_BY_ID_'.$createdCounter)),
      );
      $template = $this->service->create($websiteId, $create);
      $templateIds[] = $template->getId();
    }
        
    return $templateIds;
  }
}