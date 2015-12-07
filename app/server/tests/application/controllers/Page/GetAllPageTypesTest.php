<?php


namespace Application\Controller\Page;

use Seitenbau\Registry;
use Test\Seitenbau\ControllerTestCase;

/**
 * PageController GetAllPageTypes Test
 *
 * @package Application\Controller\Page
 *
 * @group pageType
 */

class GetAllPageTypeTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'PageController.json');

  protected $actionEndpoint = 'page/getAllPageTypes';

  protected function tearDown()
  {
    $this->deactivateGroupCheck();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function test_getAllPageTypes_retrieveExpectedData()
  {
    // ARRANGE
    $websiteId = 'SITE-page0con-trol-ler0-0000-000000000001-SITE';
    $params = array('websiteid' => $websiteId);
    $form = array(
      (object) array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );
    $urlToGlobalSet = '/URL/TO/SETS/rukzuk_test';
    $expectedPageTypes = array(
      'page' => array(
        'websiteId' => $websiteId,
        'id' => 'page',
      ),
      'rz_shop_product' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop_product',
        'name' => (object) array(
          'de' => 'Shop Produkt',
          'en' => 'Shop product',
        ),
        'description' => (object) array(
          'de' => 'rz_shop_product.description.de',
          'en' => 'rz_shop_product.description.en',
        ),
        'version' => 'rz_shop_product.version',
        'form' => $form,
        'formValues' => (object) array(
          'price' => 9999,
        ),
        'previewImageUrl' => $urlToGlobalSet .
          '/rz_package_1/pageTypes/rz_shop_product/assets/pageType.svg',
      ),
      'rz_shop_product_pro' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_shop_product_pro',
        'name' => (object) array(
          'de' => 'Shop Pro-Produkt',
          'en' => 'Shop pro product',
        ),
        'description' => null,
        'version' => null,
        'form' => $form,
        'formValues' => new \stdClass(),
        'previewImageUrl' => null,
      ),
      'rz_blog_post' => array(
        'websiteId' => $websiteId,
        'id' => 'rz_blog_post',
        'name' => (object) array(
          'de' => 'Blog Artikel',
          'en' => 'Blog post',
        ),
        'description' => (object) array(
          'de' => 'rz_blog_post.description',
        ),
        'version' => null,
        'form' => $form,
        'formValues' => new \stdClass(),
        'previewImageUrl' => null,
      ),
    );


    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('pageTypes', $responseData);
    $allPageTypes = $responseData->pageTypes;
    $this->assertInternalType('array', $allPageTypes);
    $this->assertCount(count($expectedPageTypes), $allPageTypes);
    foreach($allPageTypes as $actualPageType) {
      $actualPageTypeAsArray = (array) $actualPageType;
      $this->assertArrayHasKey('id', $actualPageTypeAsArray);
      $expectedPageType = $expectedPageTypes[$actualPageTypeAsArray['id']];
      foreach($expectedPageType as $attributeName => $expectedValue) {
        $this->assertEquals($expectedValue, $actualPageTypeAsArray[$attributeName],
          sprintf("Failed asserting that page type property '%s' is equal.", $attributeName));
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function test_getAllPageTypes_mustHaveParamWebsiteId()
  {
    // ARRANGE
    $params = array('websiteid' => '');

    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    foreach ($response->error as $error) {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function test_getAllPageTypes_retrieveAccessDenied($username, $password)
  {
    // ARRANGE
    $websiteId = 'SITE-page0con-trol-ler0-0000-000000000001-SITE';
    $params = array('websiteid' => $websiteId);

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($username, $password);
    $this->dispatchWithParams($this->actionEndpoint, $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    $errors = $response->getError();
    $this->assertGreaterThan(0, count($errors));
    $this->assertSame(7, $errors[0]->code);
  }

  /**
   * @test
   * @group integration
   * @dataProvider accessAllowedUser
   */
  public function test_getAllPageTypes_retrieveDataIfUserAllowed($username, $password)
  {
    // ARRANGE
    $websiteId = 'SITE-page0con-trol-ler0-0000-000000000001-SITE';
    $params = array('websiteid' => $websiteId);

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($username, $password);
    $this->dispatchWithParams($this->actionEndpoint, $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('pageTypes', $responseData);
    $this->assertInternalType('array', $responseData->pageTypes);
    $this->assertGreaterThan(0, count($responseData->pageTypes));
  }

  /**
   * @return array
   */
  public function accessDeniedUser()
  {
    return array(
      array('access_rights_1@sbcms.de', 'seitenbau'),  // without group
    );
  }

  /**
   * @return array
   */
  public function accessAllowedUser()
  {
    return array(
      array('sbcms@seitenbau.com', 'seitenbau'),  // superuser
      array('access_rights_2@sbcms.de', 'seitenbau'), // with group
      array('access_rights_3@sbcms.de', 'seitenbau'), // with group no template rights
    );
  }
}