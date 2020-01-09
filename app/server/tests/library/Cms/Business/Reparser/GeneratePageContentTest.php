<?php
namespace Cms\Business\Reparse;

use Cms\Business,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer GenerateContent Funktionalitaet Cms\Business\Reparser
 *
 * @package      Cms
 * @subpackage   Business\Reparser
 */

class GeneratePageContentTest extends ServiceTestCase
{
  protected $reparseBusiness;

  protected $templateBusiness;

  protected $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';

  protected function setUp()
  {
    parent::setUp();
    
    $this->reparseBusiness = new Business\Reparse('Reparse');
    $this->templateBusiness = new Business\Template('Template');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $templateId = 'TPL-42b452d6-vi2d-4b56-b52c-aeaa49e541c9-TPL';
    $websiteId = 'SITE-5sz2bve3-1cfg-4836-b847-1ab0571b1e6d-SITE';
    
    $template = $this->templateBusiness->getById($templateId, $websiteId);
    $templateContent = \Seitenbau\Json::decode($template->getContent());
    
    \Cms\ExceptionStack::reset();
    $pageContent = $this->reparseBusiness->generateNewPageContent($template);
    $this->assertEquals(0, count(\Cms\ExceptionStack::getExceptions()), 'Exception(s) occured');
    
    $this->assertNotSame($templateContent, $pageContent);

    $this->checkGeneratedContent($templateContent, $pageContent);
  }

  private function checkGeneratedContent(array $templateContent, array $pageContent)
  {
    foreach ($templateContent as $key => $tUnit)
    {
      $this->assertNotSame($tUnit['id'], $pageContent[$key]['id']);
      $this->assertSame($tUnit['name'], $pageContent[$key]['name']);
      $this->assertSame($tUnit['id'], $pageContent[$key]['templateUnitId']);
      if (isset($tUnit['children'])
          && is_array($tUnit['children'])
          && count($tUnit['children']) > 0)
      {
        if (isset($tUnit['ghostContainer']) && $tUnit['ghostContainer'] == true)
        {
          $this->assertGreaterThan(0, $pageContent[$key]['ghostChildren']);
          $this->checkGeneratedContent(
            $tUnit['children'],
            $pageContent[$key]['ghostChildren']
          );
        }
        else
        {
          $this->assertGreaterThan(0, $pageContent[$key]['children']);
          $this->checkGeneratedContent(
            $tUnit['children'],
            $pageContent[$key]['children']
          );
        }
      }
    }
  }
}