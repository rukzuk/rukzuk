<?php
namespace Cms\Business\Page;

use Cms\Business\Page as PageBusiness,
    Seitenbau\Registry,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer Update Funktionalitaet Cms\Service\Page
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class UpdateTest extends ServiceTestCase
{
  protected $business;

  protected $testEntry;

  protected $websiteId = 'SITE-s3dfghju-4s5x-dnxj-2ns1-fc42dc78fe50-SITE';

  protected $deleteFiles = array();
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new PageBusiness('Page');

    $attributes = array(
      'templateid' => 'TPL-create01-page-test-87jd-ju87cj2m361s-TPL',
      'name' => 'PHPUnit Test Page - Update',
      'parentid' => 'root',
      'insertbeforeid' => null
    );
    $this->testEntry = $this->business->create($attributes, $this->websiteId);
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $attributes = array(
      'name' => 'new name'
    );
    
    $this->assertNotSame($attributes['name'], $this->testEntry->getName());

    $this->business->update($this->testEntry->getId(), $this->websiteId, $attributes);
    $this->deleteFiles[] = $this->testEntry->getId();

    $page = $this->business->getById($this->testEntry->getId(), $this->websiteId);

    $this->assertSame($attributes['name'], $page->getName());
  }
}