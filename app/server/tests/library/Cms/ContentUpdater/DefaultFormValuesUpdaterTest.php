<?php


namespace Test\Cms\ContentUpdater;

use Cms\ContentUpdater\DefaultFormValuesUpdater;
use Test\Seitenbau\TransactionTestCase;
use Cms\Data\Modul as ModuleData;


class DefaultFormValuesUpdaterTest extends TransactionTestCase
{
  public $sqlFixtures = array();

  /**
   * Checks, that all default form values will be set to unit even if formValues missing at unit
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_UnitFormValuesNotExists_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = $this->getDefaultModuleFormValue();
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);
    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
      )
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$defaultModuleValues,
      )
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * Checks, that all default form values will be set to unit even if formValues is empty at unit
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_UnitFormValuesEmpty_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = $this->getDefaultModuleFormValue();
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);
    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)array(),
      )
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$defaultModuleValues,
      )
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * Checks, that all missing default form values will be added to unit
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_UnitFormValuesExists_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = $this->getDefaultModuleFormValue();
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);

    $orgFormValues = array(
      'string_exists_only_in_unit' => 'the_unit_value',
      'array_exists_only_in_unit' => array('foo', 'bar'),
      'object_exists_only_in_unit' => (object)array('foo' => 'bar'),
      'null_exists_only_in_unit' => null,
      'empty_string_exists_only_in_unit' => '',
      'int_0_exists_only_in_unit' => 0,
      'false_exists_only_in_unit' => false,
    );

    $expectedFormValues = $defaultModuleValues;
    foreach ($orgFormValues as $key => $value) {
      $expectedFormValues[$key] = $value;
    }

    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$orgFormValues,
      )
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$expectedFormValues,
      )
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * Checks, that all unit formValues will be preserved
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_PreserveUnitFormValues_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = $this->getDefaultModuleFormValue();
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);

    $orgFormValues = $defaultModuleValues;
    foreach ($orgFormValues as $key => $value) {
      $orgFormValues[$key] = 'unit_value';
    }

    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$orgFormValues,
      )
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)$orgFormValues,
      )
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * Checks, that all all child units will be updated
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_AlsoUpdatesChildUnitFormValues_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = array(
      'my_key' => 'my_default_value',
    );
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);

    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)array(),
        'children' => array(
          (object)array(
            'id' => 'unit_002',
            'moduleId' => 'module_001',
            'children' => null,
          ),
          (object)array(
            'id' => 'unit_003',
            'moduleId' => 'module_001',
            'formValues' => (object)array(
              'my_key' => 'my_unit_003_value',
            ),
            'ghostChildren' => array(
              (object)array(
                'id' => 'unit_004',
                'moduleId' => 'module_001',
                'formValues' => (object)array(
                  'foo' => 'bar'
                ),
                'children' => array(
                  (object)array(
                    'id' => 'unit_005',
                    'moduleId' => 'module_001',
                    'children' => null,
                    'formValues' => (object)array(
                      'key_from_unit_005' => 'value_from_unit_005'
                    ),
                  ),
                ),
              ),
            )
          )
        )
      )
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'module_001',
        'formValues' => (object)array(
          'my_key' => 'my_default_value'
        ),
        'children' => array(
          (object)array(
            'id' => 'unit_002',
            'moduleId' => 'module_001',
            'children' => null,
            'formValues' => (object)array(
              'my_key' => 'my_default_value'
            ),
          ),
          (object)array(
            'id' => 'unit_003',
            'moduleId' => 'module_001',
            'formValues' => (object)array(
              'my_key' => 'my_unit_003_value',
            ),
            'ghostChildren' => array(
              (object)array(
                'id' => 'unit_004',
                'moduleId' => 'module_001',
                'formValues' => (object)array(
                  'foo' => 'bar',
                  'my_key' => 'my_default_value',
                ),
                'children' => array(
                  (object)array(
                    'id' => 'unit_005',
                    'moduleId' => 'module_001',
                    'children' => null,
                    'formValues' => (object)array(
                      'my_key' => 'my_default_value',
                      'key_from_unit_005' => 'value_from_unit_005',
                    ),
                  ),
                ),
              ),
            )
          )
        )
      )
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * Checks, that updater don't stop at not existing modules
   *
   * @test
   * @group        contentUpdater
   * @group        library
   * @group        dev
   * @group        small
   */
  public function test_updateDefaultFormValues_ModuleNotExistsIsOnlyLogged_success()
  {
    // ARRANGE
    $websiteId = 'THE-WEBSITE-ID';
    $defaultModuleValues = array(
      'my_key' => 'my_default_value',
      'module_key' => 'my_default_module_value',
    );
    $moduleServiceMock = $this->getModuleServiceMock($websiteId, array(
      $this->createModuleData('module_001', $defaultModuleValues),
    ));
    $updater = new DefaultFormValuesUpdater($websiteId, $moduleServiceMock);
    $content = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'not_existing_module',
        'formValues' => (object)array(
          'my_key' => 'my_value_from_unit',
          'the_unit_value' => 'this_value_must_be_preserved',
        ),
        'children' => array(
          (object)array(
            'id' => 'unit_001',
            'moduleId' => 'module_001',
            'formValues' => (object)array(
              'my_key' => 'my_value_from_unit',
              'the_unit_value' => 'this_value_must_be_preserved',
            ),
          ),
        ),
      ),
    );
    $expectedContent = array(
      (object)array(
        'id' => 'unit_001',
        'moduleId' => 'not_existing_module',
        'formValues' => (object)array(
          'my_key' => 'my_value_from_unit',
          'the_unit_value' => 'this_value_must_be_preserved',
        ),
        'children' => array(
          (object)array(
            'id' => 'unit_001',
            'moduleId' => 'module_001',
            'formValues' => (object)array(
              'my_key' => 'my_value_from_unit',
              'the_unit_value' => 'this_value_must_be_preserved',
              'module_key' => 'my_default_module_value',
            ),
          ),
        ),
      ),
    );

    // ACT
    $updater->updateDefaultFormValues($content);

    // ASSERT
    $this->assertEquals($expectedContent, $content);
  }

  /**
   * @return array
   */
  protected function getDefaultModuleFormValue()
  {
    return array(
      'foo' => 'bar',
      'this_is_a_array' => array('foo', 'bar'),
      'this_is_a_object' => (object)array('foo' => 'bar'),
      'this_is_null' => null,
      'this_is_empty_string' => '',
      'this_is_empty_array' => array(),
      'this_is_empty_object' => (object)array(),
      'this_is_int_0' => 0,
      'this_is_false' => false,
    );
  }

  /**
   * @param string $moduleId
   * @param array  $defaultModuleValues
   *
   * @return ModuleData
   */
  protected function createModuleData($moduleId, array $defaultModuleValues)
  {
    $module = new ModuleData();
    $module->setId($moduleId);
    $module->setFormvalues((object)$defaultModuleValues);
    return $module;
  }

  /**
   * @param $websiteId
   * @param $expectedModules
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Cms\Service\Modul
   */
  protected function getModuleServiceMock($websiteId, $expectedModules)
  {
    $moduleMock = $this->getMockBuilder('\Cms\Service\Modul')
      ->disableOriginalConstructor()->getMock();
    $moduleMock->expects($this->atLeastOnce())
      ->method('getById')
      ->will($this->returnCallback(function ($mId, $wId) use ($websiteId, $expectedModules) {
        if ($wId !== $websiteId) {
          throw new \Exception('Website "' . $wId . '" not exists');
        }
        foreach ($expectedModules as $module) {
          if ($mId === $module->getId()) {
            return $module;
          }
        }
        throw new \Exception('Module "' . $mId . '" not exists');
      }));

    return $moduleMock;
  }
}
 