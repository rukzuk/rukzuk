<?php

/**
 * Test default implementation of SimpleModule->provideModuleData
 */

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;


class SimpleModuleTest_ProvoideModuleInfoTest extends \Rukzuk\Modules\SimpleModule
{
  protected function htmlHead($api, $moduleInfo) {
    return 'SimpleModule test head';
  }
}

class SimpleModuleTest_APIMock extends RenderApiMock
{
  private $editMode;

  public function __construct($editMode = FALSE)
  {
    $this->editMode = $editMode;
  }

  public function isEditMode()
  {
    return $this->editMode;
  }
}

class SimpleModuleTest extends ModuleTestCase
{
  protected $moduleClass = 'SimpleModule';

  protected $customData = array(
      'assets' => array(
        'js' => array(
          array(
            'file' => 'foo.js',
            'mode' => 'edit',
            'type' => 'module'
          ),
          array(
            'file' => 'bar.js',
            'type' => 'module'
          ),
          array(
            'mode' => 'edit',
            'file' => 'ping.js'
          ),
          array(
            'file' => 'pong.js'
          )
        )
      )
  );

  public function testProvideModuleData_isArray()
  {
    // prepare
    $module = $this->createModule();
    // execute
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(), $this->createModuleInfo());
    // verify
    $this->assertTrue(is_array($moduleData));
  }

  public function testProvideModuleData_hasHead() {
    // prepare
    $module = $this->createModule(array(
      'class' => 'SimpleModuleTest_ProvoideModuleInfoTest',
      'ns' => ''
    ));
    // execute
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(), $this->createModuleInfo());
    // verify
    $this->assertTrue(array_key_exists('htmlHead', $moduleData));
    $this->assertEquals($moduleData['htmlHead'], 'SimpleModule test head');
  }

  public function testProvideModuleData_hasJsModulesEditMode() {
    // prepare
    $module = $this->createModule();
    $moduleInfo = $this->createModuleInfo(array(
      'assetUrl' => 'testHasJsModules/assetUrl',
      'custom' => $this->customData
    ));
    // execute
	$module->disableCacheBuster();
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(TRUE), $moduleInfo);
    // verify
    $this->assertTrue(array_key_exists('jsModules', $moduleData));
    $jsModules = $moduleData['jsModules'];

    $this->assertContains('testHasJsModules/assetUrl/foo.js', $jsModules);
    $this->assertContains('testHasJsModules/assetUrl/bar.js', $jsModules);
    $this->assertContainsNot('testHasJsModules/assetUrl/ping.js', $jsModules);
    $this->assertContainsNot('testHasJsModules/assetUrl/pong.js', $jsModules);
  }

  public function testProvideModuleData_hasJsModulesLiveMode() {
    // prepare
    $module = $this->createModule();
    $moduleInfo = $this->createModuleInfo(array(
      'assetUrl' => 'testHasJsModules/assetUrl',
      'manifest' => $this->manifest
    ));
    // execute

	$module->disableCacheBuster();
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(FALSE), $moduleInfo);
    // verify
    $this->assertTrue(array_key_exists('jsModules', $moduleData));
    $jsModules = $moduleData['jsModules'];
    $this->assertContainsNot('testHasJsModules/assetUrl/foo.js', $jsModules);
    $this->assertContains('testHasJsModules/assetUrl/bar.js', $jsModules);
    $this->assertContainsNot('testHasJsModules/assetUrl/ping.js', $jsModules);
    $this->assertContainsNot('testHasJsModules/assetUrl/pong.js', $jsModules);
  }

  public function testProvideModuleData_hasJsScriptsEditMode() {
    // prepare
    $module = $this->createModule();
    $moduleInfo = $this->createModuleInfo(array(
      'assetUrl' => 'testHasJsModules/assetUrl',
      'manifest' => $this->manifest
    ));
    // execute
	$module->disableCacheBuster();
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(TRUE), $moduleInfo);
    // verify
    $this->assertTrue(array_key_exists('jsScripts', $moduleData));
    $jsScripts = $moduleData['jsScripts'];
    $this->assertContainsNot('testHasJsModules/assetUrl/foo.js', $jsScripts);
    $this->assertContainsNot('testHasJsModules/assetUrl/bar.js', $jsScripts);
    $this->assertContains('testHasJsModules/assetUrl/ping.js', $jsScripts);
    $this->assertContains('testHasJsModules/assetUrl/pong.js', $jsScripts);
  }

  public function testProvideModuleData_hasJsScriptsLiveMode() {
    // prepare
    $module = $this->createModule();
    $moduleInfo = $this->createModuleInfo(array(
      'assetUrl' => 'testHasJsModules/assetUrl',
      'manifest' => $this->manifest
    ));
    // execute
	$module->disableCacheBuster();
    $moduleData = $module->provideModuleData(new SimpleModuleTest_APIMock(FALSE), $moduleInfo);
    // verify
    $this->assertTrue(array_key_exists('jsModules', $moduleData));
    $jsScripts = $moduleData['jsScripts'];
    $this->assertContainsNot('testHasJsModules/assetUrl/foo.js', $jsScripts);
    $this->assertContainsNot('testHasJsModules/assetUrl/bar.js', $jsScripts);
    $this->assertContainsNot('testHasJsModules/assetUrl/ping.js', $jsScripts);
    $this->assertContains('testHasJsModules/assetUrl/pong.js', $jsScripts);
  }

}
