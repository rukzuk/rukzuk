<?php
namespace Application\Controller\Modul;

use Test\Seitenbau\ModuleControllerTestCase;
use Test\Seitenbau\Cms\Response as Response;
use Test\Seitenbau\Cms\Dao\Module\ReadonlyMock as ModuleReadonlyMock;
use Cms\Data\Modul as DataModule;

/**
 * ModulController GetAllTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetAllTest extends ModuleControllerTestCase
{
  const BACKUP_CONFIG = true;

  public $sqlFixtures = array('application_contoller_modul_getAll_with_global_module.json');

  protected $serviceUrl = '/modul/getall/params/%s';
  protected $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';

  protected $websiteId = 'SITE-ae6e702f-10ac-4e1e-951f-307e4b8765db-SITE';
  protected $websiteIdWithLocalAndGlobalModules = 'SITE-module00-cont-roll-er00-000000000001-SITE';


  /**
   * @test
   * @group integration
   */
  public function getAllShouldReturnAtLeastOneModule()
  {
    $this->dispatch(
      sprintf('/modul/getall/params/{"websiteid":"%s"}', $this->websiteId));
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertInternalType('array', $responseData->modules);
    $this->assertGreaterThan(0, count($responseData->modules));
  }

  /**
   * @test
   * @group integration
   */
  public function getAllMustHaveParamWebsiteId()
  {
    // Pflichtparameter
    $params = array('websiteid' => '');

    $this->dispatch('/modul/getAll');
    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    foreach ($responseObject->error as $error)
    {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function expectedModuleAttributesInResponse()
  {
    $this->dispatch(
      sprintf('/modul/getall/params/{"websiteid":"%s"}', $this->websiteId));
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('modules', $responseData);
    $this->assertInternalType('array', $responseData->modules);
    $this->assertGreaterThan(0, count($responseData->modules));

    foreach ($responseData->modules as $module)
    {
      $this->assertInstanceOf('stdClass', $module);
      $this->assertObjectHasAttribute('id', $module);
      $this->assertInternalType('string', $module->id);
      $this->assertObjectHasAttribute('websiteId', $module);
      $this->assertInternalType('string', $module->websiteId);
      $this->assertObjectHasAttribute('name', $module);
      if (!is_null($module->name))
      {
        $this->assertInternalType('string', $module->name);
      }
      $this->assertObjectHasAttribute('description', $module);
      if (!is_null($module->description))
      {
        $this->assertInternalType('string', $module->description);
      }
      $this->assertObjectHasAttribute('version', $module);
      if (!is_null($module->version))
      {
        $this->assertInternalType('string', $module->version);
      }
      $this->assertObjectHasAttribute('category', $module);
      if (!is_null($module->category))
      {
        $this->assertInternalType('string', $module->category);
      }
      $this->assertObjectHasAttribute('icon', $module);
      if (!is_null($module->icon))
      {
        $this->assertInternalType('string', $module->icon);
      }
      $this->assertObjectHasAttribute('form', $module);
      if (!is_null($module->form))
      {
        $this->assertInternalType('array', $module->form);
      }
      $this->assertObjectHasAttribute('formValues', $module);
      $this->assertObjectHasAttribute('moduleType', $module);
      if (!is_null($module->moduleType))
      {
        $this->assertInternalType('string', $module->moduleType);
      }
      $this->assertObjectHasAttribute('allowedChildModuleType', $module);
      if (!is_null($module->allowedChildModuleType))
      {
        $this->assertInternalType('string', $module->allowedChildModuleType);
      }
      $this->assertObjectHasAttribute('reRenderRequired', $module);
      if (!is_null($module->reRenderRequired))
      {
        $this->assertInternalType('boolean', $module->reRenderRequired);
      }
      $this->assertObjectHasAttribute('overwritten', $module);
      $this->assertFalse($module->overwritten);
      $this->assertObjectHasAttribute('sourceType', $module);
      $this->assertEquals(\Cms\Data\Modul::SOURCE_LOCAL, $module->sourceType);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider test_getAll_returnsExpectedModulesIncludedGlobalModulesIfEnabledProvider
   */
  public function test_getAll_returnsExpectedModulesIncludedGlobalModulesIfEnabled(
                      $enableGlobalSets, $runId, $websiteId, $expectedModules)
  {
    // ARRANGE
    if ($enableGlobalSets) {
      $this->enableGlobalSets();
    } else {
      $this->disableGlobalSets();
    }
    $expectedModuleIds = array_keys($expectedModules);
    sort($expectedModuleIds);

    // ACT
    $this->dispatch(sprintf($this->serviceUrl, json_encode(array(
      'runId' => $runId,
      'websiteId' => $websiteId,
    ))));

    // ASSERT
    $response = $this->getValidatedSuccessResponse();
    $data = $response->getData();
    $this->assertInstanceOf('stdClass', $data);
    $this->assertObjectHasAttribute('modules', $data);
    $this->assertInternalType('array', $data->modules);
    foreach ($data->modules as $module) {
      $this->assertInstanceOf('stdClass', $module);
      $this->assertObjectHasAttribute('id', $module);
      $this->assertInternalType('string', $module->id);
      $this->assertContains($module->id, $expectedModuleIds);
      $this->assertObjectHasAttribute('websiteId', $module);
      $this->assertInternalType('string', $module->websiteId);
      $this->assertEquals($websiteId, $module->websiteId);
      foreach ($expectedModules[$module->id] as $attribute => $expectedValue) {
        $message = 'Failed asserting attribute '.$attribute.' of module '.$module->id.' returned as expected';
        $this->assertObjectHasAttribute($attribute, $module, $message);
        $this->assertEquals($expectedValue, $module->$attribute, $message);
      }
      $actualModuleIds[] = $module->id;
    }
    sort($actualModuleIds);
    $this->assertEquals($expectedModuleIds, $actualModuleIds);

  }


  /**
   * @return array
   */
  public function test_getAll_returnsExpectedModulesIncludedGlobalModulesIfEnabledProvider()
  {
    $runId = $this->runId;
    $websiteId = $this->websiteIdWithLocalAndGlobalModules;
    return array(array(
      false, $runId, $websiteId, array(
        'rz_tests_module_only_local' => array(
          'sourceType'  => DataModule::SOURCE_LOCAL,
          'overwritten' => false,
        ),
        'rz_tests_module_local_and_global' => array(
          'sourceType'  => DataModule::SOURCE_LOCAL,
          'overwritten' => false,
        ),
      )
    ), array(
      true, $runId, $websiteId, array(
        'rz_tests_module_only_local' => array(
          'sourceType'  => DataModule::SOURCE_LOCAL,
          'overwritten' => false,
        ),
        'rz_tests_module_local_and_global' => array(
          'sourceType'  => DataModule::SOURCE_LOCAL,
          'overwritten' => true,
        ),
        'rz_tests_module_only_global' => array(
          'sourceType'  => DataModule::SOURCE_REPOSITORY,
          'overwritten' => false,
        ),
        'rz_tests_global_root_module_v3' => array(
          'sourceType'  => DataModule::SOURCE_REPOSITORY,
          'overwritten' => false,
        ),
        'rz_tests_global_extension_module_v3' => array(
          'sourceType'  => DataModule::SOURCE_REPOSITORY,
          'overwritten' => false,
        ),
        'rz_tests_global_default_module_v3' => array(
          'sourceType'  => DataModule::SOURCE_REPOSITORY,
          'overwritten' => false,
        ),
      )
    ));
  }
}