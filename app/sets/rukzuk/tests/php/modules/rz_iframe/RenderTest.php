<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_iframe;

require_once(MODULE_PATH.'/rz_iframe/module/rz_iframe.php');

class rz_iframe_RenderTest_rz_iframe extends \Rukzuk\Modules\rz_iframe
{

}

class rz_iframe_RenderTest_ApiMock extends RenderApiMock
{
}

class rz_iframe_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_iframe_RenderTest_rz_iframe';

  public function testRender_normal()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'iframeSrc' => 'http://test.URL'
      )
    ));
    $moduleInfo = $this->createModuleInfo();
    $module = $this->createModule();

    // execute
    ob_start();
    $module->render($api, $unit, $moduleInfo);
    $renderResult = ob_get_contents();
    ob_end_clean();

    // verify
    $matcher = array(
      'tag' => 'iframe',
      'attributes' => array(
        'data-src' => 'http://test.URL'
      ),
    );

    $this->assertTag($matcher, $renderResult, 'iframe tag not found in "'.$renderResult. '"');
  }

  private function createApiMock()
  {
    return new rz_iframe_RenderTest_ApiMock(array(
      'moduleInfo' => $this->createModuleInfo(array(
        'id' => 'root-unit-id',
        'assetUrl' => 'root/assets'
      ))
    ));
  }

}
