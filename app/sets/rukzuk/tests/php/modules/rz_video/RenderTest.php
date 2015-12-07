<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

use \Rukzuk\Modules\rz_video;

require_once(MODULE_PATH.'/rz_video/module/rz_video.php');

class rz_video_RenderTest_rz_video extends \Rukzuk\Modules\rz_video
{

}

class rz_video_RenderTest_ApiMock extends RenderApiMock
{
}

class rz_video_RenderTest extends ModuleTestCase
{
  protected $moduleNS = '';
  protected $moduleClass = 'rz_video_RenderTest_rz_video';

  public function testRender_normal()
  {
    // prepare
    $api = $this->createApiMock();
    $unit = $this->createUnit(array(
      'formValues' => array(
        'htmlCode' => '<iframe width="560" height="315" src="//www.youtube.com/embed/wEWCEqga8bc?rel=0" frameborder="0" allowfullscreen></iframe>'
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
      'tag' => 'div',
      'attributes' => array(
        'class' => 'videoWrapper'
      ),
      'child' => array(
        'tag' => 'iframe',
        'attributes' => array(
          'data-src' => '//www.youtube.com/embed/wEWCEqga8bc?rel=0'
        )
      )
    );

    $this->assertTag($matcher, $renderResult, 'embedded iframe tag not found in "'.$renderResult. '"');
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
