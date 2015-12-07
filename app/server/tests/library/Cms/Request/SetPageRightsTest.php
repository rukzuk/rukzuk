<?php
namespace Cms\Request;

/**
 * SetPageRightsTest
 *
 * @package      Cms
 * @subpackage   Request
 */
class SetPageRightsTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function allRightsPropertyDefaultsToFalseWhenNotOverwrittenOrSetInRequest()
  {
    $request = new \Zend_Controller_Request_HttpTestCase();
    $request->setControllerName('group');
    $request->setActionName('setpagerights');
    $request->setParams(array(
        'controller' => 'group',
        'action' => 'setpagerights',
        'params' => '{"id":"GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg7-GROUP","websiteId":"SITE-ae6e702f-10ac-4e1e-exwc-307e4b8765db-SITE","rights":"[]"}',
        'module' => 'default'
      )
    );
    
    $setPageRightsRequest = new \Cms\Request\Group\SetPageRights($request);
    
    $this->assertInternalType('boolean', $setPageRightsRequest->getAllRights());
    $this->assertSame(false, $setPageRightsRequest->getAllRights());
  }
}