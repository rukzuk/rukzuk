<?php
namespace Test\Rukzuk;

require_once(MODULE_PATH.'/rz_root/module/rz_root.php');

class ModuleTestCase extends \PHPUnit_Framework_TestCase
{
  // default module settings
  protected $moduleNS = '\Rukzuk\Modules';
  protected $moduleClass = '';
  protected $assetPath = null;
  protected $assetUrl = '';
  protected $manifest = array();
  protected $customData = array();

  /**
   * Creates an instance of the module class
   * @param array $conf The configuration values for the module instance
   *    The following properties/values are supported:
   *    * "ns" <string> The namespace of the module class (defaults to $this->moduleNS)
   *    * "class" <string> The module class name (defaults to $this->moduleClass)
   *    * "id" <string> The module ID (defaults to $this->moduleClass by convention)
   *    * "assetPath" <string> The module asset file path (defaults to result of $this->getAssetPath();)
   *    * "assetUrl" <string> The URL to the module assets (defaults to $this->assetUrl)
   *    * "manifest" <array> The module manifest (defaults to $this->manifest)
   */
  protected function createModule($conf = null)
  {
    $ns = $this->getValue('ns', $conf, $this->moduleNS);
    $class = $this->getValue('class', $conf, $this->moduleClass);
    $className = $ns . '\\' . $class;

    if (!class_exists($className)) {
      $this->fail('Module class "'.$className.'" is not defined');
      return;
    }

    return new $className();
  }

  protected function createModuleInfo($conf = null)
  {
    $id = $this->getValue('id', $conf, $this->moduleClass); // by convention is "module id" == "module class name"
    $assetPath = $this->getValue('assetPath', $conf, $this->getAssetPath());
    $assetUrl = $this->getValue('assetUrl', $conf, $this->assetUrl);
    $manifest = $this->getValue('manifest', $conf, $this->manifest);
    $customData = $this->getValue('custom', $conf, $this->customData);

    $moduleInfoStrorage = $this->getMockBuilder('Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage')
      ->disableOriginalConstructor()->getMock();
    $moduleInfoStrorage->expects($this->any())->method('getModuleAssetPath')->will($this->returnValue($assetPath));
    $moduleInfoStrorage->expects($this->any())->method('getModuleAssetUrl')->will($this->returnValue($assetUrl));
    $moduleInfoStrorage->expects($this->any())->method('getModuleManifest')->will($this->returnValue($manifest));
    $moduleInfoStrorage->expects($this->any())->method('getModuleCustomData')->will($this->returnValue($customData));

    $moduleInfo = new \Render\ModuleInfo($moduleInfoStrorage, $id);
    return $moduleInfo;
  }

  /**
   * Creates a render unit instance which is required by many API methods
   * @param array $conf The instance values; The following properties are supported:
   *    * "moduleId" <string> The ID of the unit's module (defaults to $this->moduleClass by convention)
   *    * "id" <string> The unit's ID (defaults to "unit_of_" + the value of the module id)
   *    * "name" <string> The unit's name (defaults to the value of the unit's id)
   *    * "formValues" <array> The actual unit's form values (defaults to an empty array)
   *    * "ghostContainer" <bool> TRUE iff the unit is a flex container (defaults to FALSE)
   *    * "templateUnitId" <string> The id of unit in the source template (defaults to NULL)
   * @return \Render\Unit
   */
  protected function createUnit($conf = null)
  {
    $moduleId = $this->getValue('moduleId', $conf, $this->moduleClass);
    $id = $this->getValue('id', $conf, 'unit_of_' . $moduleId);
    $name = $this->getValue('name', $conf, $id);
    $formValues = $this->getValue('formValues', $conf, array());
    $ghostContainer = $this->getValue('ghostContainer', $conf, false);
    $templateUnitId = $this->getValue('templateUnitId', $conf, null);

    return new \Render\Unit($id, $moduleId, $name, $formValues, $ghostContainer, $templateUnitId);
  }

  //
  //
  // asserts
  //
  //

  protected function assertContainsNot($needle, array $haystack, $message = '')
  {
    if ($message === '') {
      $message = 'Failed asserting that an array should not contain "' . $needle . '"';
    }
    $this->assertEmpty(array_intersect(array($needle), $haystack), $message);
  }

  //
  //
  // helper and convenience methods
  //
  //

  protected function createRenderApi($conf = null, $class = '\Test\Rukzuk\RenderApiMock')
  {
    return new $class($conf, $this);
  }

  protected function createCssApi($conf = null, $class = '\Test\Rukzuk\CssApiMock')
  {
    return new $class($conf, $this);
  }

  protected function getValue($key, $param, $defaultValue)
  {
    return HelperUtils::getValue($key, $param, $defaultValue);
  }

  protected function getAssetPath()
  {
    if (isset($this->assetPath)) {
      return $this->assetPath;
    } else {
      return MODULE_PATH.'/'.$this->moduleClass.'/assets';
    }
  }

  /**
   * Returns the render output for a given module and a set of parameters
   *
   * @param SimpleModule|array|null $module The module instance
   *    that implements the render method; or a configuration for createModule
   * @param RenderApiMock|array|null $api The api mock instance or a
   *    configuration for createRenderApi
   * @param Unit|array|null $unit The unit data instance a configuration for createUnit
   * @param ModuleInfo|array|null $module The module info instance or a configuration
   *    for createModuleInfo
   *
   * @return string The html output
   */
  protected function render($module = null, $api = null, $unit = null, $moduleInfo = null)
  {
    // create sane defaults
    if (is_null($module) || is_array($module)) {
      $module = $this->createModule($module);
    }
    if (is_null($api) || is_array($api)) {
      $api = $this->createRenderApi($api);
    }
    if (is_null($unit) || is_array($unit)) {
      $unit = $this->createUnit($unit);
    }
    if (is_null($moduleInfo) || is_array($moduleInfo)) {
      $moduleInfo = $this->createModuleInfo($moduleInfo);
    }

    // do rendering
    ob_start();
    $module->render($api, $unit, $moduleInfo);
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }

  /**
   * Returns the provideUnitData return value for a given module and a set of parameters
   *
   * @param SimpleModule|array|null $module The module instance
   *    that implements the render method; or a configuration for createModule
   * @param CssApiMock|array|null $api The api mock instance or a
   *    configuration for createCssApi
   * @param Unit|array|null $unit The unit data instance a configuration for createUnit
   * @param ModuleInfo|array|null $module The module info instance or a configuration
   *    for createModuleInfo
   *
   * @return array
   */
  protected function provideUnitData($module = null, $api = null, $unit = null, $moduleInfo = null)
  {
    // create sane defaults
    if (is_null($module) || is_array($module)) {
      $module = $this->createModule($module);
    }
    if (is_null($api) || is_array($api)) {
      $api = $this->createRenderApi($api);
    }
    if (is_null($unit) || is_array($unit)) {
      $unit = $this->createUnit($unit);
    }
    if (is_null($moduleInfo) || is_array($moduleInfo)) {
      $moduleInfo = $this->createModuleInfo($moduleInfo);
    }

    // do rendering
    ob_start();
    $providedUnitData = $module->provideUnitData($api, $unit, $moduleInfo);
    $output = ob_get_contents();
    ob_end_clean();

    // assert that there is no output
    $this->assertSame('', $output);

    return $providedUnitData;
  }

  /**
   * @param object $obj
   * @param string $methodName
   * @param array  $args
   *
   * @return mixed
   */
  protected function callMethod($obj, $methodName, array $args = array())
  {
    $class = new \ReflectionClass($obj);
    $method = $class->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($obj, $args);
  }

  /**
   * @param object $obj
   * @param string $propertyName
   * @param mixed  $value
   */
  protected function setObjectProperty($obj, $propertyName, $value)
  {
    $class = new \ReflectionClass($obj);
    $property = $class->getProperty($propertyName);
    $property->setAccessible(true);
    $property->setValue($obj, $value);
  }
}
