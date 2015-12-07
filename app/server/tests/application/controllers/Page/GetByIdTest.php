<?php
namespace Application\Controller\Page;

use Seitenbau\Registry;
use Test\Seitenbau\ControllerTestCase;

/**
 * PageController GetById Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetByIdTest extends ControllerTestCase
{
  public $sqlFixtures = array('application_controller_Page_GetByIdTest.json');

  /**
   * @test
   * @group integration
   *
   * @dataProvider provider_test_getById_success
   */
  public function test_getById_success($websiteId, $pageId, $expectedAttributes)
  {
    // ACT
    $this->dispatchWithParams('page/getbyid', array(
      'websiteId' => $websiteId,
      'id' => $pageId,
    ));

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $this->assertEquals($expectedAttributes, (array)$response->data);
  }

  /**
   * Invalide Angabe von Parametern
   *
   * @test
   * @group integration
   */
  public function invalidParams()
  {
    // ARRANGE
    $params = array('id' => 'UNGUELTIGE_ID', 'websiteid' => 'UNGUELTIGE_ID');

    // ACT
    $this->dispatch('page/getbyid/params/' . json_encode($params));

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    foreach ($response->error as $error) {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }

  /**
   * Page existiert nicht
   *
   * @test
   * @group integration
   */
  public function pageNotFound()
  {
    // ARRANGE
    $expectedParams = array(
      'id' => 'PAGE-11111111-1111-1111-1111-111111111111-PAGE',
      'websiteId' => 'SITE-controll-er0p-age0-getb-yid000000001-SITE'
    );

    // ACT
    $this->dispatch('page/getbyid/params/' . json_encode($expectedParams));

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    $errors = $response->error;
    $this->assertNotNull($errors[0]->code);
    $this->assertNotNull($errors[0]->logid);
    $this->assertObjectHasAttribute('param', $errors[0]);
    $this->assertInstanceOf('stdClass', $errors[0]->param);
    $this->assertObjectHasAttribute('websiteid', $errors[0]->param);
    $this->assertEquals($expectedParams['websiteId'], $errors[0]->param->websiteid);
    $this->assertObjectHasAttribute('id', $errors[0]->param);
    $this->assertEquals($expectedParams['id'], $errors[0]->param->id);
  }


  public function provider_test_getById_success()
  {
    $websiteId = 'SITE-controll-er0p-age0-getb-yid000000001-SITE';
    $pageId01 = 'PAGE-controll-er0p-age0-getb-yid010000001-PAGE';
    $pageId02 = 'PAGE-controll-er0p-age0-getb-yid010000002-PAGE';

    return array(
      // saved page data
      array(
        $websiteId,
        $pageId01,
        array(
          'websiteId' => $websiteId,
          'id' => $pageId01,
          'templateId' => 'TPL-controll-er0p-age0-getb-yid010000001-TPL',
          'name' => 'This ist the page name',
          'description' => 'This is the page description',
          'date' => 1314355284,
          'inNavigation' => true,
          'navigationTitle' => 'This is the page navigation title',
          'content' => array(),
          'pageType' => 'the_page_type_id',
          'pageAttributes' => (object) array(
            'foo' => 'bar',
            'myArray' => array(),
            'myObject' => (object) array(),
          ),
          'mediaId' => 'MDB-controll-er0p-age0-getb-yid010000001-MDB',
          'screenshot' => sprintf('%s%s/%s/%s', Registry::getConfig()->server->url,
            Registry::getConfig()->screens->url, Registry::getConfig()->request->parameter,
            urlencode(sprintf('{"websiteid":"%s","type":"page","id":"%s"}', $websiteId, $pageId01))
          ),
        ),
      ),
      // default values
      array(
        $websiteId,
        $pageId02,
        array(
          'websiteId' => $websiteId,
          'id' => $pageId02,
          'templateId' => 'TPL-controll-er0p-age0-getb-yid010000001-TPL',
          'name' => '',
          'description' => '',
          'date' => 0,
          'inNavigation' => false,
          'navigationTitle' => '',
          'content' => null,
          'pageType' => null,
          'pageAttributes' => (object) array(),
          'mediaId' => '',
          'screenshot' => sprintf('%s%s/%s/%s', Registry::getConfig()->server->url,
            Registry::getConfig()->screens->url, Registry::getConfig()->request->parameter,
            urlencode(sprintf('{"websiteid":"%s","type":"page","id":"%s"}', $websiteId, $pageId02))
          ),
        ),
      ),
    );
  }

}