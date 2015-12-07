<?php
namespace Cms\Request;

/**
 * ExportWebsiteTest
 *
 * @package      Cms
 * @subpackage   Request
 */
class ExportWebsiteTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function completePropertyDefaultsToTrueWhenNotOverwrittenInRequest()
  {
    $request = new \Zend_Controller_Request_HttpTestCase();
    $request->setControllerName('export');
    $request->setActionName('website');
    $request->setParams(array(
        'controller' => 'export',
        'action' => 'website',
        'params' => '{"websiteid":"SITE-ae6e702f-10ac-4e1e-exwc-307e4b8765db-SITE","name":"test_export_0_website_complete"}',
        'module' => 'default'
      )
    );
    
    $exportWebsiteRequest = new \Cms\Request\Export\Website($request);
    $this->assertInternalType('boolean', $exportWebsiteRequest->getComplete());
    $this->assertSame(true, $exportWebsiteRequest->getComplete());
  }
  /**
   * @test
   * @group library
   */
  public function completePropertyIsOverwrittenWhenSetInRequest()
  {
    $request = new \Zend_Controller_Request_HttpTestCase();
    $request->setControllerName('export');
    $request->setActionName('website');
    $request->setParams(array(
        'controller' => 'export',
        'action' => 'website',
        'params' => '{"websiteid":"SITE-ae6e702f-10ac-4e1e-exwc-307e4b8765db-SITE","name":"test_export_0_website_complete","complete":false}',
        'module' => 'default'
      )
    );
    
    $exportWebsiteRequest = new \Cms\Request\Export\Website($request);
    
    $this->assertInternalType('boolean', $exportWebsiteRequest->getComplete());
    $this->assertSame(false, $exportWebsiteRequest->getComplete());
  }
}