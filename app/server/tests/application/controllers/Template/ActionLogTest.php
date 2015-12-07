<?php
namespace Application\Controller\Template;

use Cms\Business\Template as TemplateBusiness,
    Test\Seitenbau\ActionlogControllerTestCase as ActionlogControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Seitenbau\Registry as Registry;

/**
 * ActionLogTest fuer Template
 *
 * @package      Test 
 * @subpackage   Controller
 */

class ActionLogTest extends ActionlogControllerTestCase
{ 
  /**
   * @test
   * @group integration
   */
  public function deleteTemplateShouldBeLogged()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $templateId = 'TPL-4mrap53m-2bf9-4g1h-a49b-4a93in3fbdel-TPL';
    
    $request = sprintf(
      '/template/delete/params/{"runid":"%s","id":"%s","websiteid":"%s"}',
      $runId,
      $templateId, 
      $websiteId
    );
    
    $userlogin = 'log.template@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();
    
    $this->dispatch($request);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertActionLogEntry(
      $websiteId, 
      $templateId,
      $userlogin,
      TemplateBusiness::TEMPLATE_DELETE_ACTION
    );
  }
  
  /**
   * @test
   * @group integration
   */
  public function editTemplateShouldBeLogged()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
    $idOfTemplateToUpdate = 'TPL-4mrap53m-2al2-4g1f-a49b-4a93in3f70pd-TPL';
    $nameBeforeUpdate = 'Integration_Edit_Template_Original';
    
    $request = sprintf(
      '/template/edit/params/{"runid":"%s","id":"%s","name":"%s_Edit","websiteid":"%s","content":[]}',
      $runId,
      $idOfTemplateToUpdate,
      $nameBeforeUpdate,
      $websiteId
    );
    
    $userlogin = 'log.template@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);
    
    $this->activateGroupCheck();

    $this->assertSuccessfulLock($runId, $idOfTemplateToUpdate,
                                $websiteId, 'template');

    $deactivated = false;
    
    $this->dispatch($request);
    
    $this->deactivateGroupCheck();
    
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    
    $this->assertActionLogEntry(
      $websiteId, 
      $idOfTemplateToUpdate,
      $userlogin,
      TemplateBusiness::TEMPLATE_EDIT_ACTION
    );

    $this->assertSuccessfulUnlock($runId, $idOfTemplateToUpdate,
                                $websiteId, 'template');
  }
  
  /**
   * @test
   * @group integration
   */
  public function createTemplateShouldBeLogged()
  {
    // ARRANGE
    $actionEndpoint = 'template/create';
    $params = array(
      'websiteid' => 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE',
      'name' => 'Template_Create_Via_Integration_Test',
      'content' => array(),
      'pagetype' => 'this_is_the_page_type'
    );

    $username = 'log.template@sbcms.de';
    $password = 'TEST09';

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($username, $password);
    $this->dispatchWithParams($actionEndpoint, $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertInstanceOf('stdClass', $responseData);
    $this->assertObjectHasAttribute('id', $responseData);
    
    $this->assertActionLogEntry(
      $params['websiteid'],
      $responseData->id,
      $username,
      TemplateBusiness::TEMPLATE_CREATE_ACTION,
      array(
        'pageType' => $params['pagetype'],
      )
    );
  }
}