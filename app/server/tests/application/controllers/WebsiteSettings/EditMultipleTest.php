<?php


namespace Application\Controller\WebsiteSettings;

use Seitenbau\Registry;
use Test\Seitenbau\ControllerTestCase;
use Test\Seitenbau\Cms\Dao\MockManager as MockManager;

/**
 * WebsitesettingsController EditMultipleTest Test
 *
 * @package Application\Controller\WebsiteSettings
 *
 * @group websiteSettings
 */

class EditMultipleTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json', 'WebsiteSettingsController.json');

  protected $actionEndpoint = 'websitesettings/editmultiple';

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);

    parent::setUp();
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
  public function editMultipleSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-website0-sett-ings-cont-roller000001-SITE';

    $expectedWebsiteSettings = array(
      'rz_shop' => array(
        'id' => 'rz_shop',
        'websiteId' => $websiteId,
        'formValues' => (object) array(
          'theNewValueKey' => array(
            (object) array('params' => 'value1'),
            (object) array('params' => 'value2'),
          ),
        ),
      ),
      'rz_website_settings_test' => array(
        'id' => 'rz_website_settings_test',
        'websiteId' => $websiteId,
        'formValues' => (object) array('BAR' => 'FOO'),
      ),
    );


    $params = array(
      'websiteid' => $websiteId,
      'websitesettings' => array(
        'rz_shop' => array(
          'formValues' => $expectedWebsiteSettings['rz_shop']['formValues']
        ),
        'rz_website_settings_test' => array(
          'formValues' => $expectedWebsiteSettings['rz_website_settings_test']['formValues']
        ),
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
    $this->assertGreaterThanOrEqual(count($expectedWebsiteSettings), count($allWebsiteSettings));
    foreach($allWebsiteSettings as $actualWebsiteSettings) {
      $actualWebsiteSettingsAsArray = (array) $actualWebsiteSettings;
      $this->assertArrayHasKey('id', $actualWebsiteSettingsAsArray);
      $settingsId = $actualWebsiteSettingsAsArray['id'];
      if (!array_key_exists($settingsId, $expectedWebsiteSettings)) {
        continue;
      }
      foreach ($expectedWebsiteSettings[$settingsId] as $key => $expectedValue) {
        $this->assertEquals($expectedValue, $actualWebsiteSettingsAsArray[$key]);
      }
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function editMultipleMustHaveValidParams($params, $invalidFieldNames, $errorCount)
  {
    // ACT
    $this->dispatchWithParams($this->actionEndpoint, $params);

    // ASSERT
    $response = $this->getValidatedErrorResponse();
    $this->assertCount($errorCount, $response->error);
    foreach ($response->error as $error) {
      $this->assertContains($error->param->field, $invalidFieldNames);
    }
    $this->assertGreaterThanOrEqual(count($invalidFieldNames), $response->error);
  }

  /**
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function editMultipleReturnAccessDenied($username, $password)
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
  public function editMultipleSuccessIfUserAllowed($username, $password)
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
      array('access_rights_1@sbcms.de', 'seitenbau'), // without group
      array('access_rights_3@sbcms.de', 'seitenbau'), // with group no template rights
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
    );
  }

  /**
   * @return array
   */
  public function invalidParamsProvider()
  {
    $websiteId = 'SITE-website0-sett-ings-cont-roller000001-SITE';
    return array(
      array(
        array(),
        array('websiteid', 'websitesettings'),
        1,
      ),
      array(
        array('websiteid' => ''),
        array('websiteid', 'websitesettings'),
        1,
      ),
      array(
        array(
          'websiteid' => $websiteId,
          'websitesettings' => array(array()),
        ),
        array('websitesettings'),
        2,
      ),
      array(
        array(
          'websiteid' => $websiteId,
          'websitesettings' => array(
            'rz_shop' => array('noObject')
          ),
        ),
        array('websitesettings'),
        1,
      ),
    );
  }
}