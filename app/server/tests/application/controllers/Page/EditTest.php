<?php
namespace Application\Controller\Page;

use Test\Rukzuk\UnitHelper;
use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response,
    Seitenbau\Registry as Registry;

/**
 * PageController Edit Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditTest extends ControllerTestCase
{
  private $testDirExpectedResultFiles;

  private $testOutputDir;

  protected function setUp()
  {
    parent::setUp();

    $this->activateGroupCheck();

    $config = Registry::getConfig();
    $this->testDirExpectedResultFiles = $config->test->response->render->directory;
    $this->testOutputDir = $config->test->output->response->render->directory;
  }

  protected function tearDown()
  {
    $this->deactivateGroupCheck();
    
    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function editPageShouldBeAllowedWhenAuthenticatedUserIsSuperuser()
  {
    $paramsEdit = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE',
      'content' => array()
    );

    $userName = 'edit.page.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->assertSuccessfulLock($paramsEdit['runId'], $paramsEdit['id'],
                                $paramsEdit['websiteId'], 'page');

    $paramsAsJson = json_encode($paramsEdit);
    $this->dispatch('page/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->assertSuccessfulUnlock($paramsEdit['runId'], $paramsEdit['id'],
                                  $paramsEdit['websiteId'], 'page');

  }
  /**
   * @test
   * @group integration
   */
  public function editPageShouldBeAllowedWhenAuthenticatedUserHasAllPagesPrivileges()
  {
    $paramsEdit = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE',
      'content' => array()
    );
    $paramsAsJson = json_encode($paramsEdit);

    $userName = 'edit.page.allrights@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->assertSuccessfulLock($paramsEdit['runId'], $paramsEdit['id'],
                                $paramsEdit['websiteId'], 'page');

    $this->dispatch('page/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->assertSuccessfulUnlock($paramsEdit['runId'], $paramsEdit['id'],
                                  $paramsEdit['websiteId'], 'page');

  }
  /**
   * @test
   * @group integration
   */
  public function editPageShouldBeAllowedWhenAuthenticatedUserHasEditPagePrivilegesOnPage()
  {
    $paramsEdit = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE',
      'content' => array()
    );
    $paramsAsJson = json_encode($paramsEdit);

    $userName = 'edit.page.edit@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->assertSuccessfulLock($paramsEdit['runId'], $paramsEdit['id'],
                                $paramsEdit['websiteId'], 'page');

    $this->dispatch('page/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $this->assertSuccessfulUnlock($paramsEdit['runId'], $paramsEdit['id'],
                                  $paramsEdit['websiteId'], 'page');

  }
  /**
   * @test
   * @group integration
   */
  public function editPageShouldBeRejectedWhenAuthenticatedUserHasNoPagesPrivileges()
  {
    $paramsEdit = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE',
      'content' => array()
    );
    $paramsAsJson = json_encode($paramsEdit);

    $userName = 'edit.page.no.page.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userName, $userPassword);

    // Lock der Page bereits in der DB eingetragen

    $this->dispatch('page/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code);
    $this->assertNull($response->getData());
  }

  /**
   * @test
   * @group integration
   */
  public function successAllEdit()
  {
    // ARRANGE
    $userName = 'controller.page.edit@sbcms.de';
    $userPassword = 'TEST01';
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $pageId = 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE';
    $expectedContent = array(UnitHelper::getValidPageUnit());

    // ACT
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->assertSuccessfulLock($runId, $pageId, $websiteId, 'page');
    $this->dispatchWithParams('page/edit', array(
      'runId' => $runId,
      'id' => $pageId,
      'websiteId' => $websiteId,
      'content' => $expectedContent,
    ));

    // ASSERT
    $this->getValidatedSuccessResponse();
    $this->assertSuccessfulUnlock($runId, $pageId, $websiteId, 'page');
    $this->dispatchWithParams('page/getbyid', array(
      'id' => $pageId,
      'websiteid' => $websiteId,
    ));
    $responseData = $this->getValidatedSuccessResponse();
    $this->assertEquals($expectedContent, $responseData->data->content);
  }

  /**
   * @test
   * @group integration
   */
  public function successNothingToEdit()
  {
    // ARRANGE
    $userName = 'controller.page.edit@sbcms.de';
    $userPassword = 'TEST01';
    $paramsNew = array(
      'runId' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'id' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'websiteId' => 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'name' => 'new_page_name',
      'description' => 'new_page_description',
      'inNavigation' => true,
      'navigationTitle' => 'new_page_navigation_title',
      'date' => 1302183631,
      'pageType' => 'new_page_type',
      'pageAttributes' => (object) array(
        'newKey' => 'newValue',
      ),
    );
    $paramsGetById = array(
      'id' => $paramsNew['id'],
      'websiteid' => $paramsNew['websiteId'],
    );

    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->dispatchWithParams('page/getbyid', $paramsGetById);
    $responseObjectBeforeUpdate = $this->getValidatedSuccessResponse();

    // ACT
    $this->assertSuccessfulLock($paramsNew['runId'], $paramsNew['id'],
                                  $paramsNew['websiteId'], 'page');
    $this->dispatchWithParams('page/edit', $paramsNew);
    $this->getValidatedSuccessResponse();
    $this->assertSuccessfulUnlock($paramsNew['runId'], $paramsNew['id'],
                                  $paramsNew['websiteId'], 'page');

    // ASSERT
    $this->dispatchWithParams('page/getbyid', $paramsGetById);
    $responseObjectAfterUpdate = $this->getValidatedSuccessResponse();
    $this->assertEquals($responseObjectBeforeUpdate->data->name, $responseObjectAfterUpdate->data->name);
    $this->assertEquals($responseObjectBeforeUpdate->data->description, $responseObjectAfterUpdate->data->description);
    $this->assertEquals($responseObjectBeforeUpdate->data->inNavigation, $responseObjectAfterUpdate->data->inNavigation);
    $this->assertEquals($responseObjectBeforeUpdate->data->navigationTitle, $responseObjectAfterUpdate->data->navigationTitle);
    $this->assertEquals($responseObjectBeforeUpdate->data->date, $responseObjectAfterUpdate->data->date);
    $this->assertEquals($responseObjectBeforeUpdate->data->content, $responseObjectAfterUpdate->data->content);
  }

  /**
   * @test
   * @group integration
   */
  public function checkRequiredParams()
  {
    $userName = 'controller.page.edit@sbcms.de';
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $this->dispatch('page/edit/');
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();

    $invalidParams = array();
    foreach ($errors as $error)
    {
      $this->assertSame(3, $error->code);
      $this->assertObjectHasAttribute('field', $error->param);
      $invalidParams[] = $error->param->field;
    }

    $requiredParams = array('runid', 'websiteid', 'id');

    foreach ($requiredParams as $requiredParam)
    {
      $this->assertContains($requiredParam, $invalidParams);
      $requiredParamKey = array_search($requiredParam, $invalidParams);
      unset($invalidParams[$requiredParamKey]);
    }

    $this->assertSame(0, count($invalidParams), '"' . implode(', ', $invalidParams) . '" darf/duerfen keine Pflichtparamter sein');
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($invalidParams)
  {
    $userName = 'controller.page.edit@sbcms.de';
    $userPassword = 'TEST01';
    $this->assertSuccessfulLogin($userName, $userPassword);

    $params = json_encode($invalidParams);
    $requestUri = sprintf('/page/edit/params/%s', $params);
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();

    $errorFields = array();
    foreach ($errors as $error)
    {
      $errorFields[] = $error->param->field;
    }
    $expectedErrorFields = array_keys($invalidParams);

    sort($errorFields);
    sort($expectedErrorFields);

    $this->assertSame($expectedErrorFields, $errorFields);
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {

    return array(
      array(
          array('runid' => 123,
                'id' => 123,
                'websiteid' => 'abc',
                'content' => 'not_a_array_and_object'))
    );
  }
}