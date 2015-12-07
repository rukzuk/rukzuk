<?php
namespace Cms\Business\Page;

use Cms\Business\Page as PageBusiness,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * create test for Cms\Business\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class CreateTest extends ServiceTestCase
{
  protected $business;

  protected function setUp()
  {
    parent::setUp();

    $this->business = new PageBusiness('Page');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $websiteId = 'SITE-page0000-test-0000-0000-000000000001-SITE';
    $attributes = array(
      'templateid' => 'TPL-page0000-test-0000-create00000000001-TPL',
      'name' => 'PHPUnit Test Page - Create - Name',
      'description' => 'PHPUnit Test Page - Create - Description',
      'navigationtitle' => 'PHPUnit Test Page - Create - Navigationtitle',
      'parentid' => 'root',
      'insertbeforeid' => null
    );
    
    $page = $this->business->create($attributes,$websiteId);
    
    $this->assertEquals($websiteId, $page->getWebsiteId());
    $this->assertEquals($attributes['templateid'], $page->getTemplateId());
    $this->assertEquals($attributes['name'], $page->getName());
    $this->assertEquals($attributes['description'], $page->getDescription());
    $this->assertEquals($attributes['navigationtitle'], $page->getNavigationtitle());
    $this->assertEquals(0, $page->getInnavigation());
    
    $actualGlobals = json_decode($page->getGlobalContent(), true);
    $this->assertInternalType('array', $actualGlobals);
    $this->assertArrayHasKey('headline', $actualGlobals);
    $this->assertInternalType('array', $actualGlobals['headline']);
    $this->assertEquals(1, count($actualGlobals['headline']));
    $this->assertArrayHasKey('headline', $actualGlobals);
    $this->assertArrayHasKey('unitId', $actualGlobals['headline'][0]);
    $this->assertArrayHasKey('templateUnitId', $actualGlobals['headline'][0]);
    $this->assertArrayHasKey('moduleId', $actualGlobals['headline'][0]);
    $this->assertArrayHasKey('value', $actualGlobals['headline'][0]);
    $this->assertEquals('global text', $actualGlobals['headline'][0]['value']);
    
  }
}