<?php
namespace Cms\Business\Page;

use Cms\Business\Page as PageBusiness,
    Seitenbau\Registry,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer UpdateGlobalVars Funktionalitaet Cms\Business\Page
 *
 * @package      Cms
 * @subpackage   Business\Page
 */

class UpdateGlobalVarsTest extends ServiceTestCase
{
  protected $business;

  protected $websiteId = 'SITE-update01-page-test-vars-global000001-SITE';

  protected $pageId = 'PAGE-update01-page-test-vars-global000001-PAGE';

  protected $deleteFiles = array();
  
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
    $page = $this->business->updateGlobalVars($this->pageId, $this->websiteId);
    $globalContent = $page->getGlobalContent();
    $globalContent = (is_string($globalContent))
                      ? \Seitenbau\Json::decode($globalContent)
                      : $globalContent;

    // Nur muss exakt ein globaler Wert in diesem Test vorhanden sein
    $this->assertTrue( (count($globalContent['headline']) == 1) );

    $this->assertInternalType('array', $globalContent);
    $this->assertInternalType('array', $globalContent['headline']);
    $this->assertInternalType('array', $globalContent['headline'][0]);
    $this->assertSame($globalContent['headline'][0]['value'], 'Globaler Text');
  }
}