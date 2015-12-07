<?php


namespace Application\Controller\WebsiteSettings;

use Seitenbau\Registry;
use Test\Seitenbau\ControllerTestCase;

/**
 * WebsitesettingsController GetAll Test
 *
 * @package Application\Controller\WebsiteSettings
 *
 * @group websiteSettings
 */

class GetAllTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'WebsiteSettingsController.json');

  protected $actionEndpoint = 'websitesettings/getAll';

  protected function tearDown()
  {
    $this->deactivateGroupCheck();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function getAllWebsiteSettingsShouldReturnExpectedData()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-cont-roller000001-SITE';
    $params = array('websiteid' => $websiteId);
    $formValue = array(
      (object) array(
        'foo' => 'bar',
        'emtpyStdClass' => new \stdClass(),
        'emptyArray' => array(),
      ),
    );
    $expectedWebsiteSettings = array(
      'rz_shop' => array(
        'id' => 'rz_shop',
        'websiteId' => $websiteId,
        'name' => (object) array(
          'de' => 'Shop Konfiguration',
          'en' => 'Shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop.version',
        'form' => $formValue,
        'formValues' => (object) array(),
      ),
      'rz_shop_pro' => array(
        'id' => 'rz_shop_pro',
        'websiteId' => $websiteId,
        'name' => (object) array(
          'de' => 'Pro-Shop Konfiguration',
          'en' => 'Pro shop configuration',
        ),
        'description' => null,
        'version' => 'rz_shop_pro.version',
        'form' => $formValue,
        'formValues' => (object) array(),
      ),
      'rz_website_settings_test' => array(
        'id' => 'rz_website_settings_test',
        'websiteId' => $websiteId,
        'name' => (object) array(
          'de' => 'Website Konfiguration',
          'en' => 'Website configuration',
        ),
        'description' => null,
        'version' => null,
        'form' => $formValue,
        'formValues' => (object) array(),
      ),
    );

    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websiteSettings', $responseData);
    $allWebsiteSettings = $responseData->websiteSettings;
    $this->assertInternalType('array', $allWebsiteSettings);
    $this->assertCount(count($expectedWebsiteSettings), $allWebsiteSettings);
    foreach($allWebsiteSettings as $actualWebsiteSettings) {
      $actualWebsiteSettingsAsArray = (array) $actualWebsiteSettings;
      $this->assertArrayHasKey('id', $actualWebsiteSettingsAsArray);
      $expectedSettings = $expectedWebsiteSettings[$actualWebsiteSettingsAsArray['id']];
      $this->assertEquals($expectedSettings, $actualWebsiteSettingsAsArray);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getAllMustHaveParamWebsiteId()
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
  public function getAllShouldReturnAccessDenied($username, $password)
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-cont-roller000001-SITE';
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
  public function getAllWebsiteSettingsShouldReturnDataIfUserAllowed($username, $password)
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-cont-roller000001-SITE';
    $params = array('websiteid' => $websiteId);

    // ACT
    $this->activateGroupCheck();
    $this->assertSuccessfulLogin($username, $password);
    $this->dispatchWithParams($this->actionEndpoint, $params);
    $this->deactivateGroupCheck();

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $responseData = $response->getData();
    $this->assertObjectHasAttribute('websiteSettings', $responseData);
    $this->assertInternalType('array', $responseData->websiteSettings);
    $this->assertGreaterThan(0, count($responseData->websiteSettings));
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