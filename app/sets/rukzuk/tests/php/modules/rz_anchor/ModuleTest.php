<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH.'/rz_anchor/module/rz_anchor.php');

class rz_anchor_ModuleTest_TestModule extends \Rukzuk\Modules\rz_anchor
{
  // override module class methods here...
}

class rz_anchor_ModuleTest_ApiMock extends RenderApiMock
{
  // override api mock methods here...
}

class rz_anchor_ModuleTest extends ModuleTestCase
{

  protected $moduleNS = '';
  protected $moduleClass = 'rz_anchor_ModuleTest_TestModule';

  public function testRender()
  {
    // prepare
    $unit = $this->createUnit(array(
      'id' => 'test-anchor',
      'formValues' => array('anchorName' => 'test-anchor-name')
    ));
    // execute
    $html = $this->render(null, null, $unit);
    // verify
    $this->assertTag(array(
      'tag' => 'div',
      'attributes' => array('id' => 'test-anchor'),
      'child' => array(
        'tag' => 'div',
        'attributes' => array('id' => base64_encode('test-anchor-name'))
      )
    ), $html);
  }

  public function testRender_editMode()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'mode' => 'edit'
    ));
    $unit = $this->createUnit(array(
      'id' => 'test-anchor',
      'formValues' => array(
        'anchorName'  => 'test anchor name',
        'anchorId'    => '#test-anchor-name',
      ),
    ));
    // execute
    $html = $this->render(null, $api, $unit);
    // verify
    $this->assertTag(array(
      'tag' => 'div',
      'attributes' => array('id' => 'test-anchor'),
      'child' => array(
        'tag' => 'div',
        'attributes' => array('id' => 'test-anchor-name'),
        'child' => array(
          'tag' => 'div',
          'content' => '#test-anchor-name'
        )
      )
    ), $html);
  }

  public function testRender_base64Fallback()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'mode' => 'edit'
    ));
    $unit = $this->createUnit(array(
      'id' => 'test-anchor',
      'formValues' => array(
        'anchorName'  => 'test anchor name',
        'anchorId'    => '',
      ),
    ));
    // execute
    $html = $this->render(null, $api, $unit);
    // verify
    $base64anchorId = base64_encode('test anchor name');
    $this->assertTag(array(
      'tag' => 'div',
      'attributes' => array('id' => 'test-anchor'),
      'child' => array(
        'tag' => 'div',
        'attributes' => array('id' => $base64anchorId),
        'child' => array(
          'tag' => 'div',
          'content' => '#'.$base64anchorId
        )
      )
    ), $html);
  }
}
