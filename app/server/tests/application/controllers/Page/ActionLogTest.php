<?php
namespace Application\Controller\Page;

use Cms\Business\Page as PageBusiness,
    Test\Seitenbau\ActionlogControllerTestCase as ActionlogControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Seitenbau\Registry as Registry;
/**
 * ActionLogTest fuer Page
 *
 * @package      Test 
 * @subpackage   Controller
 */
class ActionLogTest extends ActionlogControllerTestCase
{ 
  protected function setUp()
  {
    parent::setUp();
  }
  
  /**
   * @test
   * @group integration
   */
  public function movePageShouldBeLogged()
  {
    $params = array(
      'id' => 'PAGE-m01rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'parentId' => 'PAGE-m00rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-mo64e89c-00su-46cd-a651-fc42dc78fe50-SITE'
    );
    
    $paramsAsJson = json_encode($params);
    
    $userlogin = 'move.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();
        
    $this->dispatch('page/move/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();
        
    $response = $this->getResponseBody();
    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
    
    $this->assertActionLogEntry(
      $params['websiteId'], 
      $params['id'],
      $userlogin,
      PageBusiness::PAGE_MOVE_ACTION,
      array(
        'parentId'  => $params['parentId'],
        'beforeId'  => '',
      )
    );
  }
  /**
   * @test
   * @group integration
   */
  public function createPageShouldBeLogged()
  {
    $params = array(
      'websiteId' => 'SITE-cp565eb8-0363-47e9-afac-90ae9d96auth-SITE',
      'parentId' => 'PAGE-np565eb8-cp00-47e9-afac-90ae9d96auth-PAGE',
      'templateId' => 'TPL-cp00fzef-b5fd-4918-a178-68dfef13auth-TPL',
      'name' => 'new_page_created_with_all_page_privilege',
      'pageType' => 'the_page_type_id',
    );
    $paramsAsJson = json_encode($params);
    
    $userlogin = 'create.page@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();
    $deactivated = false;
    
    $this->dispatch('page/create/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();
        
    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $responseData = $response->getData();
    $createdPageId = $responseData->id;
    
    $this->assertActionLogEntry(
      $params['websiteId'], 
      $createdPageId,
      $userlogin,
      PageBusiness::PAGE_CREATE_ACTION,
      array(
        'parentId'  => $params['parentId'],
        'beforeId'  => '',
      )
    );
  }
  /**
   * @test
   * @group integration
   */
  public function editPageShouldBeLogged()
  {
    $paramsEdit = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE',
      'name' => 'edit new name',
      'description' => 'edit new description',
      'inNavigation' => true,
      'navigationTitle' => 'edit new Title',
      'date' => 1302183631,
      'content' => array()
    );
    $paramsAsJson = json_encode($paramsEdit);
    
    $userlogin = 'edit.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->assertSuccessfulLock($paramsEdit['runId'], $paramsEdit['id'],
                                $paramsEdit['websiteId'], 'page');
    
    $deactivated = false;
    
    $this->dispatch('page/edit/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertActionLogEntry(
      $paramsEdit['websiteId'], 
      $paramsEdit['id'],
      $userlogin,
      PageBusiness::PAGE_EDIT_ACTION
    );

    $this->assertSuccessfulUnlock($paramsEdit['runId'], $paramsEdit['id'],
                                  $paramsEdit['websiteId'], 'page');

  }
  /**
   * @test
   * @group integration
   */
  public function deletePageShouldBeLogged()
  {
    $params = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-de45d096-07su-4ab5-895b-92e7a4ba3703-PAGE',
      'websiteId' => 'SITE-de0dfb9f-362a-4b89-a084-53c4696473f8-SITE'
    );
    $paramsAsJson = json_encode($params);
    
    $userlogin = 'delete.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();

    $this->assertSuccessfulLock($params['runId'], $params['id'],
                                $params['websiteId'], 'page');

    
    $this->dispatch('page/delete/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertActionLogEntry(
      $params['websiteId'], 
      $params['id'],
      $userlogin,
      PageBusiness::PAGE_DELETE_ACTION
    );

    $this->assertSuccessfulUnlock($params['runId'], $params['id'],
                                  $params['websiteId'], 'page');

  }
  /**
   * @test
   * @group integration
   */
  public function copyPageShouldBeLogged()
  {
    $params = array(
      'id' => 'PAGE-co0rsfb8-0363-47e9-0sup-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-co64e89c-00af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'copy page test'
    );
    
    $paramsAsJson = json_encode($params);
    
    $userlogin = 'copy.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();
    $deactivated = false;
    
    $this->dispatch('page/copy/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();
    $response = new Response($response);
    
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
        
    $this->assertActionLogEntry(
      $params['websiteId'], 
      $responseData->id,
      $userlogin,
      PageBusiness::PAGE_COPY_ACTION,
      array('fromPageId' => $params['id'])
    );    
  }
}