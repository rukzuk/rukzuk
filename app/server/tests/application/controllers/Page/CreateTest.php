<?php
namespace Application\Controller\Page;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * PageController Create Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class CreateTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function test_create_success()
  {
    // ARRANGE
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $attributes = array(
      // Insert-Variablen beim Neuanlegen
      'websiteId' => $websiteId,
      'templateId' => 'TPL-3b249276-c62d-4b56-b52c-aeaa49e541c9-TPL',
      'name' => 'insert new page test',
      'description' => 'description example long: '.str_repeat('0123456789', 100),
      'date' => 1297071975,
      'inNavigation' => false,
      'navigationTitle' => 'test page',
      'pageType' => 'the_page_type_id',
    );
    $expectedPage = array_merge($attributes, array(
      'pageAttributes' => new \stdClass(),
    ));
    $params = array_merge($attributes, array(
      'parentId' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'insertBeforeId' => '',
    ));

    // ACT
    $this->dispatchWithParams('page/create', $params);

    // ASSERT
    $responseData = $this->getValidatedSuccessResponse()->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $newPageId = $responseData->id;

    // check created page
    $this->dispatchWithParams('page/getbyid', array(
      'websiteid' => $websiteId,
      'id' => $newPageId,
    ));
    $getByIdResponse = $this->getValidatedSuccessResponse();
    $newPageAsArray = (array)$getByIdResponse->getData();
    $this->assertInternalType('array', $newPageAsArray['content']);
    foreach($expectedPage as $attributeName => $expectedValue) {
      $this->assertEquals($expectedValue, $newPageAsArray[$attributeName],
        sprintf("Failed asserting that property '%s' is equal.", $attributeName));
    }
  }

  /**
   * @test
   * @group integration
   */
  public function test_create_newPageWithEmptyTemplateContentSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $attributes = array(
      'websiteId' => $websiteId,
      'templateId' => 'TPL-f1c950d2-011c-4ac6-bb90-708c7146c4c7-TPL',
      'name' => 'insert new page with empty template content test',
      'pageType' => 'the_page_type_id',
    );
    $expectedPage = array_merge($attributes, array(
      'content' => array(),
      'pageAttributes' => new \stdClass(),
    ));
    $params = array_merge($attributes, array(
      'parentId' => 'root',
    ));

    // ACT
    $this->dispatchWithParams('page/create', $params);

    // ASSERT
    $responseData = $this->getValidatedSuccessResponse()->getData();
    $this->assertObjectHasAttribute('id', $responseData);
    $newPageId = $responseData->id;

    // check created page
    $this->dispatchWithParams('page/getbyid', array(
      'websiteid' => $websiteId,
      'id' => $newPageId,
    ));
    $getByIdResponse = $this->getValidatedSuccessResponse();
    $newPageAsArray = (array)$getByIdResponse->getData();
    foreach($expectedPage as $attributeName => $expectedValue) {
      $this->assertEquals($expectedValue, $newPageAsArray[$attributeName]);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function test_create_newPageToNonExistTemplateReturnError()
  {
    // ARRANGE
    $params = array(
      'websiteid' => 'SITE-1964e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'parentid' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'templateId' => 'TPL-769989ef-b5fd-4918-a178-68dfef1e370d-TPL',
      'name' => 'new page to non exists template',
      'pageType' => 'the_page_type_id',
    );

    // ACT
    $this->dispatchWithParams('page/create', $params);

    // ASSERT
    $this->getValidatedErrorResponse();
  }

  /**
   * @test
   * @group integration
   */
  public function test_create_newPageToNonExistWebsiteReturnError()
  {
    // ARRANGE
    $params = array(
      'websiteid' => 'SITE-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx-SITE',
      'parentid' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE',
      'templateId' => 'TPL-769989ef-b5fd-4918-a178-68dfef1e370d-TPL',
      'name' => 'new page to non exists template',
      'pageType' => 'the_page_type_id',
    );

    // ACT
    $this->dispatchWithParams('page/create', $params);

    // ASSERT
    $this->getValidatedErrorResponse();
  }
  /**
   * @test
   * @group integration
   */
  public function test_create_shouldBeAllowedWhenAuthenticatedUserHasAllPagesPrivilege()
  {
    // ARRANGE
    $params = array(
      'websiteid' => 'SITE-cp565eb8-0363-47e9-afac-90ae9d96auth-SITE',
      'parentid' => 'PAGE-np565eb8-cp00-47e9-afac-90ae9d96auth-PAGE',
      'templateId' => 'TPL-cp00fzef-b5fd-4918-a178-68dfef13auth-TPL',
      'name' => 'new_page_created_with_all_page_privilege',
      'pageType' => 'the_page_type_id',
    );
    $userName = 'create.page@sbcms.de';
    $userPassword = 'TEST09';

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->dispatchWithParams('page/create', $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $responseData = $this->getValidatedSuccessResponse()->getData();
    $this->assertObjectHasAttribute('id', $responseData);
  }

  /**
   * @test
   * @group integration
   */
  public function test_create_shouldBeRejectedIfParentPageHasNoCreateChildrenPrivilege()
  {
    // ARRANGE
    $params = array(
      'websiteid' => 'SITE-cp565eb8-0363-47e9-afac-91ae9d96auth-SITE',
      'parentid' => 'PAGE-np565eb8-cp04-47e9-afac-91ae9d96auth-PAGE',
      'templateId' => 'TPL-cp01fzef-b5fd-4918-a178-68dfef13auth-TPL',
      'name' => 'new_page_rejected_parent_page_create_privileges',
      'pageType' => 'the_page_type_id',
    );
    $userName = 'create1.page@sbcms.de';
    $userPassword = 'TEST09';

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->dispatchWithParams('page/create', $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $responseError = $this->getValidatedErrorResponse(true)->getError();
    $this->assertSame(7, $responseError[0]->code);
  }

  /**
   * @test
   * @group integration
   */
  public function test_create_shouldOnlyBeAllowedIfParentPageHasCreateChildrenPrivilege()
  {
    $params = array(
      'websiteid' => 'SITE-cp565eb8-0363-47e9-afac-91ae9d96auth-SITE',
      'parentid' => 'PAGE-np565eb8-cp00-47e9-afac-91ae9d96auth-PAGE',
      'templateId' => 'TPL-cp01fzef-b5fd-4918-a178-68dfef13auth-TPL',
      'name' => 'new_page_created_parent_page_create_privileges',
      'pageType' => 'the_page_type_id',
    );
    $userName = 'create1.page@sbcms.de';
    $userPassword = 'TEST09';

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($userName, $userPassword);
    $this->dispatchWithParams('page/create', $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $responseData = $this->getValidatedSuccessResponse()->getData();
    $this->assertObjectHasAttribute('id', $responseData);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function test_create_invalidParamsReturnError($params, $expectedErrorParams)
  {
    // ACT
    $this->dispatchWithParams('page/create', $params);

    // ASSERT
    $response = $this->getValidatedErrorResponse(true, $expectedErrorParams);
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    $gueltigeUnits = json_encode(array(
      'id' => '123546987',
      'name' => 'unit name',
      'description' => '',
      'moduleId' => '',
      'formValues' => '',
      'deletable' => false,
      'readonly' => true,
      'ghostContainer' => '',
      'visibleFormGroups' => '',
      'expanded' => '',
      'templateUnitId' => '',
      'ghostChildren' => array(),
      'children' => array(),
    ));
    $textLongerThat255Chars = 'sometextlongerthan255characterssometextlonger'
      . 'than255characterssometextlongerthan255characterssometextlongerthan255'
      . 'characterssometextlongerthan255characterssometextlongerthan255charact'
      . 'erssometextlongerthan255characterssometextlongerthan255characterssome'
      . 'textlongerthan255characters';


    return array(
      array(
        array(
          'websiteId' => null,
          'parentId' => null,
          'templateId' => null,
          'name' => null,
          'content' => '"abc"',
          'pageType' => null,
          'pageAttributes' => '"not_a_json"',
        ),
        array(
          'websiteid' => 3,
          'parentId' => 3,
          'templateId' => 3,
          'name' => 3,
          'content' => 3,
          'pagetype' => 3,
          'pageattributes' => 3,
        )
      ),
      array(
        array(
          'websiteid' => '123456',
          'parentid' => '123456',
          'templateid' => '123456',
          'name' => '',
          'content' => $gueltigeUnits,
          'pagetype' => $textLongerThat255Chars,
          'pageAttributes' => 10,
        ),
        array(
          'websiteid' => 3,
          'parentId' => 3,
          'templateId' => 3,
          'name' => 3,
          'content' => 3,
          'pagetype' => 3,
          'pageattributes' => 3,
        )
      ),
    );
  }
}