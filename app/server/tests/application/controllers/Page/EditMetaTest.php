<?php


namespace Application\Controller\Page;

use Seitenbau\Registry;
use Test\Seitenbau\ControllerTestCase;

/**
 * PageController EditMeta Test
 *
 * @package Application\Controller\Page
 */

class EditMetaTest extends ControllerTestCase
{
  protected $actionEndpoint = 'page/editmeta';

  protected function tearDown()
  {
    $this->deactivateGroupCheck();
    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function test_editMeta_success()
  {
    // ARRANGE
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $websiteId = 'SITE-1964e89c-0001-sued-a651-fc42dc78fe50-SITE';
    $pageId = 'PAGE-03565eb8-0001-47e9-sued-90ae9d96d3c2-PAGE';
    $expectedPage = array(
      'id' => $pageId,
      'websiteId' => $websiteId,
      'name' => 'editMeta new name',
      'description' => 'editMeta new description',
      'inNavigation' => true,
      'navigationTitle' => 'editMeta new Title',
      'date' => 1302183631,
      'pageAttributes' => (object) array(
        'newKey' => 'newValue',
      ),
    );
    $params = array_merge($expectedPage, array(
      'runId' => $runId,
      'id' => $pageId,
      'websiteId' => $websiteId,
      'name' => 'editMeta new name',
      'description' => 'editMeta new description',
      'inNavigation' => true,
      'navigationTitle' => 'editMeta new Title',
      'date' => 1302183631,
      'pageAttributes' => (object) array(
        'newKey' => 'newValue',
      ),
    ));

    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $this->getValidatedSuccessResponse();

    // check page
    $this->dispatchWithParams('page/getbyid', array(
      'websiteid' => $websiteId,
      'id' => $pageId,
    ));
    $getByIdResponse = $this->getValidatedSuccessResponse();
    $pageAsArray = (array)$getByIdResponse->getData();
    foreach($expectedPage as $attributeName => $expectedValue) {
      $this->assertEquals($expectedValue, $pageAsArray[$attributeName]);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function test_editMeta_mustHaveValidParams($params, $expectedErrorParams)
  {
    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $this->getValidatedErrorResponse(true, $expectedErrorParams);
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $textLongerThat255Chars = 'sometextlongerthan255characterssometextlonger'
      . 'than255characterssometextlongerthan255characterssometextlongerthan255'
      . 'characterssometextlongerthan255characterssometextlongerthan255charact'
      . 'erssometextlongerthan255characterssometextlongerthan255characterssome'
      . 'textlongerthan255characters';


    return array(
      array(
        array(
          'runId' => null,
          'websiteId' => null,
          'id' => null,
          'name' => '',
          'pageAttributes' => '"not_a_json"',
        ),
        array(
          'runid' => 3,
          'websiteid' => 3,
          'id' => 3,
          'name' => 3,
          'pageattributes' => 3,
        )
      ),
      array(
        array(
          'runId' => $runId,
          'websiteid' => '123456',
          'id' => $textLongerThat255Chars,
          'name' => $textLongerThat255Chars,
          'pageAttributes' => 10,
        ),
        array(
          'websiteid' => 3,
          'id' => 3,
          'name' => 3,
          'pageattributes' => 3,
        )
      ),
    );
  }
}